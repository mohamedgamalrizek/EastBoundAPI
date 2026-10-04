<?php

namespace App\Repositories\HajjPilgrim;

use App\Models\Customer;
use App\Models\HajjPackage;
use App\Models\HajjPilgrim;
use App\Repositories\BaseRepository;
use App\Repositories\HajjPilgrim\HajjPilgrimInterface;

class HajjPilgrimRepository extends BaseRepository implements HajjPilgrimInterface
{
    /** Allowed option sets — mirror the hajj_pilgrims migration. */
    public const PAYMENT_STATUSES  = ['Pending', 'Partial', 'Paid'];
    public const DOCUMENT_STATUSES = ['Pending', 'Submitted', 'Verified'];
    public const STATUSES          = ['Registered', 'Confirmed', 'Cancelled'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['hajjPackage', 'customer'];

    public function __construct(HajjPilgrim $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'hajj_package_id' => $request->hajj_package_id,
            'customer_id'     => $request->customer_id,
            // Left blank on the form, the office gets a sequential PIL-#### —
            // typing it by hand is how rows like "1" end up next to "PIL-1004".
            'pilgrim_no'      => $this->resolvePilgrimNo($request),
            'name'            => $request->name,
            'passport_no'     => $request->passport_no,
            // Denormalized copy kept in sync from the selected package; the
            // allocation/group/payment list pages read this string column.
            'package_title'   => HajjPackage::find($request->hajj_package_id)?->title ?? '',
            'group_name'      => $request->group_name,
            // payment_status is deliberately absent: it is derived from
            // amount_paid / amount_due by HajjPilgrimObserver, never typed.
            'document_status' => $request->document_status,
            'status'          => $request->status,
        ];
    }

    /** Keep what was typed; otherwise keep what the row already had, else mint one. */
    private function resolvePilgrimNo($request): string
    {
        if (filled($request->pilgrim_no)) {
            return $request->pilgrim_no;
        }

        if ($request->id && $existing = HajjPilgrim::find($request->id)?->pilgrim_no) {
            return $existing;
        }

        return $this->nextPilgrimNo();
    }

    /** Next number in the PIL-#### series, starting at PIL-1001. */
    private function nextPilgrimNo(): string
    {
        $last = HajjPilgrim::where('pilgrim_no', 'like', 'PIL-%')
            ->selectRaw('MAX(CAST(SUBSTRING(pilgrim_no, 5) AS UNSIGNED)) AS n')
            ->value('n');

        return 'PIL-' . max(1001, ((int) $last) + 1);
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('pilgrim_no', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('passport_no', 'like', "%{$search}%")
                    ->orWhere('package_title', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%");
            });
        }
        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        if (isset($filters['document_status'])) {
            $query->where('document_status', $filters['document_status']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'hajjPackages'    => HajjPackage::orderBy('title')->get(['id', 'title']),
            // Groups are chosen from the catalogue here too, so the pilgrim
            // form cannot invent a group the Groups screen has never heard of.
            'groupNames'      => \App\Models\HajjGroup::names(),
            'customers'       => Customer::orderBy('name')->get(['id', 'name']),
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'documentStatuses' => self::DOCUMENT_STATUSES,
            'statuses'        => self::STATUSES,
        ];
    }
}
