<?php

namespace App\Http\Controllers\Api;

use App\Models\Passport;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Passports the customer keeps on file, reused when the agency files visa
 * applications and Hajj/Umrah registrations. Mirrors the customer web portal.
 */
class PassportController extends Controller
{
    use ApiReturnFormatTrait, ResolvesCustomer;

    public const STATUSES = [
        Passport::STATUS_VALID,
        Passport::STATUS_EXPIRING,
        Passport::STATUS_EXPIRED,
    ];

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $items = Passport::where('customer_id', $customer->id)
            ->latest()
            ->get()
            ->map(fn (Passport $p) => $this->info($p));

        return $this->responseWithSuccess('Passports fetched.', [
            'passports' => $items,
            'statuses'  => self::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $data = $this->validatePayload($request);
        if ($data instanceof \Illuminate\Http\JsonResponse) {
            return $data;
        }

        $passport = Passport::create(array_merge($data, [
            'customer_id' => $customer->id,
        ]));

        return $this->responseWithSuccess('Passport added.', [
            'passport' => $this->info($passport),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $passport = Passport::where('customer_id', $customer->id)->find($id);
        if (! $passport) {
            return $this->responseWithError('Passport not found.', [], 404);
        }

        $data = $this->validatePayload($request, $passport->id);
        if ($data instanceof \Illuminate\Http\JsonResponse) {
            return $data;
        }

        $passport->update($data);

        return $this->responseWithSuccess('Passport updated.', [
            'passport' => $this->info($passport->fresh()),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $passport = Passport::where('customer_id', $customer->id)->find($id);
        if (! $passport) {
            return $this->responseWithError('Passport not found.', [], 404);
        }

        $passport->delete();

        return $this->responseWithSuccess('Passport removed.');
    }

    private function validatePayload(Request $request, ?int $ignoreId = null)
    {
        $validator = Validator::make($request->all(), [
            'holder_name' => ['required', 'string', 'max:255'],
            'passport_no' => [
                'required', 'string', 'max:60',
                Rule::unique('passports', 'passport_no')->ignore($ignoreId),
            ],
            'nationality' => ['nullable', 'string', 'max:100'],
            'issue_date'  => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        return $request->only([
            'holder_name', 'passport_no', 'nationality', 'issue_date', 'expiry_date',
        ]);
    }

    private function info(Passport $p): array
    {
        return [
            'id'          => $p->id,
            'holder_name' => $p->holder_name,
            'passport_no' => $p->passport_no,
            'nationality' => $p->nationality,
            'issue_date'  => optional($p->issue_date)->toDateString(),
            'expiry_date' => optional($p->expiry_date)->toDateString(),
            'status'      => $p->status,
        ];
    }
}
