<?php

namespace App\Services\Dmc;

use App\Enums\Status;
use App\Models\Booking;
use App\Models\Dmc\ApprovalPolicy;
use App\Models\Dmc\Opportunity;
use App\Models\Dmc\PurchaseRequest;
use App\Models\Dmc\Quotation;
use App\Models\Dmc\SalesOrder;
use App\Models\Dmc\Trip;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesWorkflow
{
    public function saveQuotation(array $data, User $actor, ?Quotation $quotation = null): Quotation
    {
        return DB::transaction(function () use ($data, $actor, $quotation) {
            if ($quotation) {
                $quotation = Quotation::lockForUpdate()->findOrFail($quotation->id);
                $this->ensure($quotation->status === 'Draft', 'Only draft quotations can be edited. Create a revision for changes.');
                $this->ensure($quotation->opportunity_id == $data['opportunity_id'], 'A quotation cannot change its originating opportunity.');
            }
            $opportunity = Opportunity::lockForUpdate()->findOrFail($data['opportunity_id']);
            $this->ensure(! in_array($opportunity->stage, ['Won', 'Lost']), 'This opportunity is closed.');
            $items = $data['items'];
            unset($data['items']);
            $cost = $price = 0;
            foreach ($items as $item) {
                $cost += (int) round($item['unit_cost'] * 100) * (int) $item['quantity'];
                $price += (int) round($item['unit_price'] * 100) * (int) $item['quantity'];
            }
            $this->ensure($price > 0 && $price <= 99999999999 && $cost <= 99999999999, 'Quotation totals must be positive and within the supported monetary range.');
            $this->ensure(abs(($price - $cost) / $price * 100) < 999999 && ($cost === 0 || abs(($price - $cost) / $cost * 100) < 99999999), 'The margin or markup exceeds the supported range.');
            $values = Arr::only($data, ['opportunity_id', 'title', 'starts_on', 'ends_on', 'travelers', 'notes']) + [
                'total_cost' => $cost / 100, 'total_price' => $price / 100,
                'margin' => round(($price - $cost) / $price * 100, 2),
                'markup' => $cost > 0 ? round(($price - $cost) / $cost * 100, 2) : null,
            ];
            if ($quotation) {
                $quotation->update($values);
                $quotation->items()->delete();
            } else {
                $quotation = Quotation::create($values + ['series' => Str::uuid(), 'created_by' => $actor->id]);
            }
            $quotation->items()->createMany($items);

            return $quotation->fresh('items');
        }, 3);
    }

    public function approvalLevel(Quotation $quotation): string
    {
        $policy = ApprovalPolicy::orderBy('id')->first();
        if (! $policy || $policy->minimum_margin === null || $policy->maximum_markup === null || $policy->team_leader_limit === null) {
            return 'General Manager';
        }

        return $quotation->markup !== null && $quotation->margin >= $policy->minimum_margin
            && $quotation->markup <= $policy->maximum_markup && $quotation->total_price <= $policy->team_leader_limit
            ? 'Team Leader' : 'General Manager';
    }

    public function transition(Quotation $quotation, string $action, User $actor, array $data = []): Quotation|SalesOrder
    {
        return DB::transaction(function () use ($quotation, $action, $actor, $data) {
            $quotation = Quotation::lockForUpdate()->findOrFail($quotation->id);
            if ($action === 'accept') {
                return $this->confirm($quotation, $actor, $data);
            }
            if ($action === 'revise') {
                $this->ensure(in_array($quotation->status, ['Approved', 'Rejected']), 'Only approved or rejected quotations can be revised.');
                $quotation->update(['status' => 'Superseded']);
                $revision = $quotation->replicate(['approved_by', 'approved_at', 'approval_note', 'approval_level']);
                $revision->status = 'Draft';
                $revision->revision++;
                $revision->created_by = $actor->id;
                $revision->save();
                foreach ($quotation->items as $item) {
                    $copy = $item->replicate();
                    $copy->quotation_id = $revision->id;
                    $copy->save();
                }

                return $revision;
            }
            if ($action === 'submit') {
                $this->ensure($quotation->status === 'Draft' && $quotation->items()->exists(), 'A populated draft is required.');
                $quotation->update(['status' => 'Submitted', 'approval_level' => $this->approvalLevel($quotation)]);
                $quotation->opportunity->update(['stage' => 'Proposal']);
            } elseif (in_array($action, ['approve', 'reject'])) {
                $this->ensure($quotation->status === 'Submitted', 'This quotation is not awaiting approval.');
                $level = $this->approvalLevel($quotation);
                $allowed = $actor->can_access('dmc_sales_approve_gm') || ($level === 'Team Leader' && $actor->can_access('dmc_sales_approve_tl'));
                abort_unless($allowed, 403);
                $this->ensure($quotation->created_by !== $actor->id, 'A quotation requires approval by a different authorized employee.');
                $this->ensure(filled($data['note'] ?? null), 'An approval or rejection reason is required.');
                $quotation->update([
                    'status' => $action === 'approve' ? 'Approved' : 'Rejected', 'approval_level' => $level,
                    'approved_by' => $actor->id, 'approved_at' => now(), 'approval_note' => $data['note'],
                ]);
            } else {
                abort(422, 'Unsupported quotation action.');
            }

            return $quotation->fresh();
        }, 3);
    }

    private function confirm(Quotation $quotation, User $actor, array $data): SalesOrder
    {
        $this->ensure(filled($data['customer_confirmation'] ?? null), 'Record the customer acceptance reference.');
        $existing = $quotation->order;
        if ($existing) {
            $this->ensure($existing->customer_confirmation === $data['customer_confirmation'], 'This quotation already has a different confirmation reference.');

            return $existing;
        }
        $this->ensure($quotation->status === 'Approved', 'Only an approved quotation can be confirmed.');
        $this->ensure(! Quotation::where('series', $quotation->series)->where('revision', '>', $quotation->revision)->exists(), 'Accept the current revision only.');
        $opportunity = Opportunity::lockForUpdate()->findOrFail($quotation->opportunity_id);
        $this->ensure(! in_array($opportunity->stage, ['Won', 'Lost']), 'This opportunity is already closed.');
        $customer = $opportunity->customer;
        $manager = User::findOrFail($data['manager_id'] ?? $actor->id);
        $this->ensure($manager->can_access('dmc_operations_update') && $manager->isAdminUser() && $manager->status === Status::ACTIVE, 'Select an authorized operations manager before confirming the trip.');
        $booking = Booking::create([
            'customer_id' => $customer->id, 'lead_id' => $opportunity->lead_id,
            'customer_name' => $customer->name, 'customer_email' => $customer->email, 'customer_phone' => $customer->phone,
            'travel_date' => $quotation->starts_on, 'travelers' => $quotation->travelers,
            'amount' => $quotation->total_price, 'gross_amount' => $quotation->total_price, 'status' => 'confirmed',
            'notes' => 'DMC quotation #'.$quotation->id.' revision '.$quotation->revision,
        ]);
        $this->ensure($booking->invoice?->journalEntries()->exists() ?? false, 'The sales invoice could not be posted. Check accounting configuration.');
        $order = SalesOrder::create([
            'quotation_id' => $quotation->id, 'booking_id' => $booking->id,
            'confirmed_by' => $actor->id, 'customer_confirmation' => $data['customer_confirmation'],
        ]);
        $trip = Trip::create([
            'sales_order_id' => $order->id, 'customer_id' => $customer->id,
            'manager_id' => $manager->id, 'title' => $quotation->title,
            'starts_on' => $quotation->starts_on, 'ends_on' => $quotation->ends_on,
        ]);
        foreach ($quotation->items as $item) {
            $tripItem = $trip->items()->create([
                'quotation_item_id' => $item->id, 'supplier_id' => $item->supplier_id, 'service_date' => $item->service_date,
            ]);
            $cost = round((float) $item->unit_cost * $item->quantity, 2);
            if ($cost > 0) {
                PurchaseRequest::create([
                    'trip_id' => $trip->id, 'trip_item_id' => $tripItem->id, 'description' => $item->description,
                    'estimated_cost' => $cost, 'required_on' => $item->service_date,
                ]);
            }
        }
        $quotation->update(['status' => 'Accepted']);
        $opportunity->update(['stage' => 'Won']);
        $opportunity->lead?->update(['stage' => 'Won']);

        return $order;
    }

    private function ensure(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['workflow' => $message]);
        }
    }
}
