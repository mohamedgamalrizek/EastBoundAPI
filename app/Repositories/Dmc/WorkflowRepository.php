<?php

namespace App\Repositories\Dmc;

use App\Enums\Status;
use App\Models\Dmc\Opportunity;
use App\Models\Dmc\PurchaseRequest;
use App\Models\Lead;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Dmc\ProcurementWorkflow;
use App\Services\Dmc\SalesWorkflow;
use App\Services\Dmc\TripExpenses;
use App\Services\Dmc\TripOperations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkflowRepository
{
    public function display(Model $record, string $field, array $options = []): string
    {
        $value = $record->{$field};
        if ($value instanceof \DateTimeInterface) {
            return $value->format(str_ends_with($field, '_at') ? 'Y-m-d H:i' : 'Y-m-d');
        }
        if (is_bool($value)) {
            return $options[(int) $value] ?? ($value ? 'Yes' : 'No');
        }
        if (array_key_exists((string) $value, $options)) {
            return (string) $options[(string) $value];
        }
        $relations = ['assigned_to' => 'assignedTo', 'created_by' => 'creator', 'approved_by' => 'approver', 'sales_order_id' => 'order'];
        $relation = $relations[$field] ?? Str::camel(preg_replace('/_id$/', '', $field));
        if ($value !== null && (isset($relations[$field]) || str_ends_with($field, '_id')) && method_exists($record, $relation)) {
            $related = $record->{$relation};
            if ($related instanceof Model) {
                return '#'.$related->id.' '.($related->name ?? $related->title ?? $related->description ?? $related->reference ?? $related->customer_name ?? '');
            }
        }

        return $value === null ? '—' : (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value);
    }

    public function canEdit(string $module, Model $record): bool
    {
        $definition = $this->definition($module);
        if (! ($definition['editable'] || ($definition['update_only'] ?? false))) {
            return false;
        }

        return match ($module) {
            'quotations' => $record->status === 'Draft',
            'rfqs' => in_array($record->status, ['Draft', 'Sent']),
            'opportunities' => ! in_array($record->stage, ['Won', 'Lost']),
            'trips', 'trip-items' => in_array($module === 'trips' ? $record->status : $record->trip->status, ['Planning', 'Ready']),
            default => true,
        };
    }

    public function definition(string $module): array
    {
        $definition = config('dmc-modules.'.$module);
        abort_unless(is_array($definition), 404);

        return $definition;
    }

    public function query(string $module, array $filters = [])
    {
        $model = $this->definition($module)['model'];
        $query = $model::query();
        if ($module === 'opportunities' && ($filters['view'] ?? '') === 'active') {
            $query->whereNotIn('stage', ['Won', 'Lost']);
        }
        if ($module === 'quotations' && ($filters['view'] ?? '') === 'pending') {
            $query->where('status', 'Submitted');
        }
        if ($module === 'trips') {
            if (($filters['view'] ?? '') === 'upcoming') {
                $query->whereDate('starts_on', '>=', today())->whereIn('status', ['Planning', 'Ready']);
            }
            if (($filters['view'] ?? '') === 'today') {
                $query->whereDate('starts_on', '<=', today())->whereDate('ends_on', '>=', today())->where('status', '!=', 'Cancelled');
            }
        }
        if ($module === 'trip-items' && ($filters['view'] ?? '') === 'today') {
            $query->whereDate('service_date', today());
        }
        if (in_array($module, ['trip-items', 'purchase-requests', 'vendor-orders']) && ! empty($filters['trip'])) {
            $query->where('trip_id', (int) $filters['trip']);
        }

        return $query;
    }

    public function save(string $module, array $data, ?Model $record = null): Model
    {
        return DB::transaction(function () use ($module, $data, $record) {
            $definition = $this->definition($module);
            if ($record) {
                $record = $definition['model']::lockForUpdate()->findOrFail($record->id);
            }
            if ($module === 'quotations') {
                return app(SalesWorkflow::class)->saveQuotation($data, auth()->user(), $record);
            }
            if ($module === 'vendor-bills') {
                return app(ProcurementWorkflow::class)->bill($data);
            }
            if ($module === 'vendor-payments') {
                return app(ProcurementWorkflow::class)->pay($data);
            }
            if ($module === 'trip-expenses') {
                return app(TripExpenses::class)->record($data);
            }
            if ($module === 'trip-items') {
                return app(TripOperations::class)->assign($record, $data);
            }
            if ($record instanceof Opportunity) {
                $this->ensure(! in_array($record->stage, ['Won', 'Lost']), 'Closed opportunities cannot be edited.');
                if ($record->quotations()->whereIn('status', ['Submitted', 'Approved', 'Accepted'])->exists()) {
                    foreach (['lead_id', 'customer_id', 'assigned_to'] as $field) {
                        $this->ensure(($record->{$field} ?? null) == ($data[$field] ?? null), 'Submitted quotations lock the opportunity customer, lead and owner.');
                    }
                }
            }
            if ($module === 'trips') {
                $this->ensure(in_array($record->status, ['Planning', 'Ready']), 'Only planned trips can change operations manager.');
                $manager = User::findOrFail($data['manager_id']);
                $this->ensure($manager->can_access('dmc_operations_update') && $manager->isAdminUser() && $manager->status === Status::ACTIVE, 'Choose an authorized internal operations manager.');
            }
            if ($module === 'opportunities') {
                $owner = User::findOrFail($data['assigned_to']);
                $this->ensure($owner->isAdminUser() && $owner->can_access('dmc_sales_read') && $owner->status === Status::ACTIVE, 'Choose an active internal sales owner.');
            }
            if ($module === 'websites' && $record && $record->slug !== $data['slug']) {
                $this->ensure(! Lead::where('dmc_website_id', $record->id)->exists(), 'A source identifier cannot change after receiving leads.');
            }
            if ($module === 'rfqs') {
                $this->ensure(Supplier::findOrFail($data['supplier_id'])->status === 'active', 'Choose an active vendor.');
                $request = PurchaseRequest::lockForUpdate()->findOrFail($data['purchase_request_id']);
                $this->ensure($request->status !== 'Ordered', 'The purchase request already has a vendor order.');
                $this->ensure(! $record || in_array($record->status, ['Draft', 'Sent']), 'This RFQ is no longer editable.');
                if ($record && $record->status !== 'Draft') {
                    $this->ensure($record->supplier_id == $data['supplier_id'] && $record->purchase_request_id == $data['purchase_request_id'], 'A sent RFQ cannot change vendor or purchase request.');
                }
            }
            if ($record) {
                $record->update($data);
                if ($module === 'vendors') {
                    activity('DMC')->performedOn($record)->withProperties(['fields' => array_keys(array_diff_key($data, ['bank_details' => true])), 'bank_details_submitted' => array_key_exists('bank_details', $data)])->log('Vendor profile updated');
                }

                return $record;
            }
            $this->ensure($definition['editable'] || ($definition['create_only'] ?? false), 'This record is generated by its workflow.');

            $record = $definition['model']::create($data);
            if ($module === 'vendors') {
                activity('DMC')->performedOn($record)->withProperties(['fields' => array_keys(array_diff_key($data, ['bank_details' => true]))])->log('Vendor profile created');
            }

            return $record;
        }, 3);
    }

    private function ensure(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['workflow' => $message]);
        }
    }
}
