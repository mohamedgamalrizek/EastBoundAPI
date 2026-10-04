<?php

namespace App\Repositories\User;

use App\Services\Auth\OtpService;
use App\Services\Contact\PhoneNumber;

use App\Services\Mail\MailConfig;

use App\Enums\ImageSize;
use App\Enums\Status;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\Hash;
use App\Repositories\User\UserInterface;
use App\Repositories\Upload\UploadInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserRepository implements UserInterface
{
    use ReturnFormatTrait;
    protected $model, $upload;

    public function __construct(User $model, UploadInterface $upload)
    {
        $this->model    = $model;
        $this->upload   = $upload;
    }

    public function all(int $paginate = null, bool $status = null, array $filters = [])
    {
        $query = $this->model::query();

        if ($status !== null) {
            $query->where('status', $status);
        }

        // Optional list filters coming from the user index search form.
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }
        if (!empty($filters['phone'])) {
            $query->where('phone', 'like', '%' . $filters['phone'] . '%');
        }

        $query->latest('updated_at');

        $query->with('image');

        if ($paginate !== null) {
            return $query->paginate($paginate)->withQueryString();
        }

        return $query->get();
    }

    public function get($id)
    {
        return $this->model->findOrFail($id);
    }

    public function store($request)
    {
        try {
            $nidUploadId             = $this->upload->uploadImage($request->file('nid'), 'users', [ImageSize::IMAGE_80x80, ImageSize::IMAGE_370x240]);
            $imageUploadId           = $this->upload->uploadImage($request->file('image'), 'users', [ImageSize::IMAGE_80x80, ImageSize::IMAGE_370x240]);

            $user                   = new User();
            $user->name             = $request->name;
            $user->email            = $request->email;
            $user->password         = Hash::make($request->password);
            $user->phone            = $request->phone;
            $user->nid_number       = $request->nid_number;
            $user->nid              = $nidUploadId;
            $user->image_id         = $imageUploadId;
            $user->address          = $request->address;

            $user->gender           =  $request->gender;
            $user->date_of_birth    =  Carbon::parse($request->date_of_birth)->format('Y-m-d');
            $user->about            =  $request->about;

            $user->role_id          = $request->role_id;
            $user->permissions      = Role::find($user->role_id)->permissions;
            $user->status           = $request->status;
            $user->save();

            return $this->responseWithSuccess(___('alert.successfully_added'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }

    public function update($request)
    {
        try {
            $user                   = $this->model::findOrFail($request->id);
            $user->name             = $request->name;
            $user->email            = $request->email;
            // Only change the password when a new one is supplied. Leaving the
            // field blank on edit must keep the existing password intact.
            if (filled($request->password)) {
                $user->password     = Hash::make($request->password);
            }
            $user->phone            = $request->phone;

            $user->date_of_birth              =  Carbon::parse($request->date_of_birth)->format('Y-m-d');
            $user->gender           =  $request->gender;

            $user->nid_number       = $request->nid_number;
            $user->nid              = $this->uploadUserFile($request->file('nid'), 'nid', $user->nid);
            $user->image_id         = $this->uploadUserFile($request->file('image'), 'image_id', $user->image_id);

            $user->address          = $request->address;
            $user->about            =  $request->about;

            $user->role_id          = $request->role_id;
            $user->permissions      = Role::find($user->role_id)->permissions;
            $user->status           = $request->status;
            $user->save();

            return $this->responseWithSuccess(___('alert.successfully_updated'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }


    public function delete($id)
    {
        try {
            $item  = $this->model::findOrFail($id);

            $this->deleteUserFile('image_id', $item->image_id);
            $this->deleteUserFile('nid', $item->nid);

            $item->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }

    public function permissionUpdate($request)
    {
        try {
            $user = $this->model::find($request->id);
            if ($request->permissions !== null) {
                $user->permissions = $request->permissions;
            } else {
                $user->permissions = [];
            }
            $user->save();

            return $this->responseWithSuccess(___('alert.successfully_updated'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }

    public function profileUpdate($request)
    {
        try {
            $user                   = $this->model::find(auth()->user()->id);
            $user->name             = $request->name;
            $user->date_of_birth              =  Carbon::parse($request->date_of_birth)->format('Y-m-d');
            $user->gender           = $request->gender;
            $user->image_id         = $this->uploadUserFile($request->file('image'), 'image_id', $user->image_id);
            $user->address          = $request->address;
            $user->about            = $request->about;
            $user->save();
            return $this->responseWithSuccess(___('alert.successfully_updated'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }

    private function uploadUserFile($file, string $column, $oldUploadId = null)
    {
        if (blank($file)) {
            return $oldUploadId;
        }

        $exclusiveUploadId = $oldUploadId && $this->uploadIdIsExclusiveToUser($column, $oldUploadId)
            ? $oldUploadId
            : null;

        return $this->upload->uploadImage(
            $file,
            'users',
            [ImageSize::IMAGE_80x80, ImageSize::IMAGE_370x240],
            $exclusiveUploadId
        );
    }

    private function uploadIdIsExclusiveToUser(string $column, $uploadId): bool
    {
        return $this->model::where($column, $uploadId)->count() === 1;
    }

    private function deleteUserFile(string $column, $uploadId): void
    {
        if ($uploadId && $this->uploadIdIsExclusiveToUser($column, $uploadId)) {
            $this->upload->deleteImage($uploadId, 'delete');
        }
    }

    public function passwordUpdate($request)
    {
        try {
            $user   = $this->model::find(auth()->user()->id);
            if (Hash::check($request->old_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
                $user->save();
                return $this->responseWithSuccess(___('alert.password_updated'), []);
            }
            return $this->responseWithError(___('alert.old_password_not_match'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }

    public function passwordReset($request)
    {
        try {
            $user   = $this->model::find($request->user_id);

            if ($user != null && session()->has('token') && session('token') == $request->token) {
                $user->password = Hash::make($request->new_password);
                $user->save();
                return $this->responseWithSuccess(___('alert.password_updated'), []);
            }

            return $this->responseWithError(___('alert.something_went_wrong'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }

    public function signup($request)
    {
        // One canonical shape before anything is stored or checked for
        // uniqueness — see App\Services\Contact\PhoneNumber.
        $request->merge([
            'phone' => PhoneNumber::e164($request->input('phone'), $request->input('dial_code')),
        ]);

        try {
            DB::beginTransaction();

            $user                   = $this->model;
            $user->name             = $request->name;
            $user->email            = $request->email;
            $user->password         = Hash::make($request->password);
            $user->phone            = $request->phone;
            $user->date_of_birth              =  Carbon::parse($request->date_of_birth)->format('Y-m-d');
            $user->gender           =  $request->gender;

            // Public self-registration is the customer-facing signup, so new
            // accounts get the Customer role only — never an admin role.
            $customerRole           = Role::where('slug', 'customer')->firstOrFail();
            $user->role_id          = $customerRole->id;
            $user->permissions      = $customerRole->permissions;

            $user->status           = Status::INACTIVE->value;

            // A website sign-up is a real customer: create (or reuse) the
            // matching `customers` record and link it, otherwise the account
            // never shows up on the ERP's Customers screen.
            $user->customer_id      = $this->customerRecordFor($request)->id;

            $user->save();

            session(['user_id' => $user->id, 'email' => $user->email, 'password' => $request->password,]);

            // One code, one email, one screen — see App\Services\Auth\OtpService.
            OtpService::issue($user);

            DB::commit();
            return $this->responseWithSuccess(___('alert.registration_successful'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }

    /**
     * Find the `customers` row for a website sign-up, creating it when the
     * person is new. Matching on email/phone avoids duplicating someone the
     * agency already has on file (e.g. booked over the phone, then registered).
     */
    private function customerRecordFor($request): Customer
    {
        $customer = Customer::where(function ($q) use ($request) {
            if ($request->email) {
                $q->orWhere('email', $request->email);
            }
            if ($request->phone) {
                $q->orWhere('phone', $request->phone);
            }
        })->first();

        if ($customer) {
            return $customer;
        }

        $customer = Customer::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'gender'        => $request->gender,
            'date_of_birth' => $request->date_of_birth
                ? Carbon::parse($request->date_of_birth)->format('Y-m-d')
                : null,
            'status'        => 'active',
            'notes'         => 'Created from website registration.',
            'referred_by'   => $request->ref ? Customer::where('referral_code', strtoupper($request->ref))->value('id') : null,
        ]);
        app(\App\Services\LoyaltyService::class)->awardReferral($customer);
        return $customer;
    }

    public function verifyToken($request)
    {
        try {
            $user = User::find($request->user_id);

            // Expiry is checked here, not in the query: a code that has run out
            // must fail as an expired code, not silently as "user not found".
            if ($user === null || ! OtpService::check($user, (string) $request->token)) {
                return $this->responseWithError(___('alert.invalid_or_expired_code'), []);
            }

            $user->email_verified_at = now();
            $user->status            = Status::ACTIVE;
            $user->save();

            OtpService::clear($user);

            return $this->responseWithSuccess(___('alert.verified'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }

    public function resendToken($request)
    {
        try {
            $user = $this->get($request->user_id);

            if (! OtpService::issue($user)) {
                return $this->responseWithError(___('alert.mail_not_send'), []);
            }

            return $this->responseWithSuccess(___('alert.otp_mail_send'), []);
        } catch (\Exception $e) {
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }

    public function passwordResetToken($request)
    {
        try {
            $user = $this->model::where('email', $request->email)->first();

            session(['user_id' => $user->id, 'email' => $user->email]);

            if (! OtpService::issue($user)) {
                return $this->responseWithError(___('alert.code_email_failed'), []);
            }

            return $this->responseWithSuccess(___('alert.otp_mail_send'), []);
        } catch (\Exception $e) {
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }
}
