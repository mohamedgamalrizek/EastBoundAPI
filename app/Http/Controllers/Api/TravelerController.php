<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Traveler;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Saved travelers (companions) for the customer — reused across bookings.
 */
class TravelerController extends Controller
{
    use ApiReturnFormatTrait, ResolvesCustomer;

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $items = Traveler::where('customer_id', $customer->id)
            ->latest()
            ->get()
            ->map(fn (Traveler $t) => $this->travelerInfo($t));

        return $this->responseWithSuccess('Travelers fetched.', ['travelers' => $items]);
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

        $traveler = Traveler::create(array_merge($data, [
            'customer_id' => $customer->id,
            'status'      => 'active',
        ]));

        return $this->responseWithSuccess('Traveler added.', [
            'traveler' => $this->travelerInfo($traveler),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $traveler = Traveler::where('customer_id', $customer->id)->find($id);
        if (! $traveler) {
            return $this->responseWithError('Traveler not found.', [], 404);
        }

        $data = $this->validatePayload($request);
        if ($data instanceof \Illuminate\Http\JsonResponse) {
            return $data;
        }

        $traveler->update($data);

        return $this->responseWithSuccess('Traveler updated.', [
            'traveler' => $this->travelerInfo($traveler->fresh()),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $traveler = Traveler::where('customer_id', $customer->id)->find($id);
        if (! $traveler) {
            return $this->responseWithError('Traveler not found.', [], 404);
        }

        $traveler->delete();

        return $this->responseWithSuccess('Traveler removed.');
    }

    private function validatePayload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'max:255'],
            'relation'    => ['nullable', 'string', 'max:100'],
            'passport_no' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'dob'         => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        return $request->only(['name', 'relation', 'passport_no', 'nationality', 'dob']);
    }

    private function travelerInfo(Traveler $t): array
    {
        return [
            'id'          => $t->id,
            'name'        => $t->name,
            'relation'    => $t->relation,
            'passport_no' => $t->passport_no,
            'nationality' => $t->nationality,
            'dob'         => optional($t->dob)->toDateString(),
            'status'      => $t->status,
        ];
    }
}
