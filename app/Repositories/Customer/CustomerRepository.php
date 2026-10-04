<?php

namespace App\Repositories\Customer;

use App\Models\Customer;
use App\Traits\ReturnFormatTrait;
use App\Repositories\Customer\CustomerInterface;

class CustomerRepository implements CustomerInterface
{
    use ReturnFormatTrait;

    protected $model;

    public function __construct(Customer $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model::orderByDesc('id')->get();
    }

    public function get($id)
    {
        // The profile lists the side services sold to this customer; loading
        // them here keeps the view from firing a query per module.
        return $this->model::with([
            'insurances', 'studentServices', 'medicalTours', 'corporateTravels',
        ])->find($id);
    }

    public function store($request)
    {
        try {
            $customer = new $this->model;
            $customer->fill($this->data($request));
            $customer->avatar = $this->uploadAvatar($request);
            $customer->save();

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function update($request, $id)
    {
        try {
            $customer = $this->model::findOrFail($id);
            $customer->fill($this->data($request));
            $customer->avatar = $this->uploadAvatar($request, $customer->avatar);
            $customer->save();

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function delete($id)
    {
        try {
            $this->model::findOrFail($id)->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    private function data($request): array
    {
        return [
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
            'tier'    => $request->tier,
            'status'  => $request->status,
            'notes'   => $request->notes,
        ];
    }

    /* store the uploaded avatar to public/uploads/customers and return its relative path */
    private function uploadAvatar($request, $old = null)
    {
        if (! $request->hasFile('avatar')) {
            return $old;
        }

        $file = $request->file('avatar');
        $name = 'cus_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/customers'), $name);

        return 'uploads/customers/' . $name;
    }
}
