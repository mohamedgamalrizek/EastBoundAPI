<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;

/**
 * Documents the agency holds for the customer (passport scans, photos, NID…).
 * Read-only in the app: files are collected and verified by the agency, the
 * customer tracks which ones are still pending.
 */
class DocumentController extends Controller
{
    use ApiReturnFormatTrait, ResolvesCustomer;

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $query = CustomerDocument::where('customer_id', $customer->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->latest('uploaded_on')
            ->get()
            ->map(fn (CustomerDocument $d) => $this->info($d));

        return $this->responseWithSuccess('Documents fetched.', [
            'documents' => $documents,
            'summary'   => [
                'total'    => $documents->count(),
                'verified' => $documents->where('status', 'Verified')->count(),
                'pending'  => $documents->where('status', 'Pending')->count(),
                'rejected' => $documents->where('status', 'Rejected')->count(),
            ],
        ]);
    }

    private function info(CustomerDocument $d): array
    {
        return [
            'id'          => $d->id,
            'title'       => $d->title,
            'type'        => $d->type,
            'file_label'  => $d->file_label,
            'uploaded_on' => optional($d->uploaded_on)->toDateString(),
            'status'      => $d->status,
        ];
    }
}
