<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Upload;
use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Account profile — works for both Customers (B2C) and Users/Agents (B2B).
 * View, update and change password.
 */
class ProfileController extends Controller
{
    use ApiReturnFormatTrait;

    public function show(Request $request)
    {
        return $this->responseWithSuccess('Profile fetched.', [
            'profile' => $this->profileInfo($request->user()),
        ]);
    }

    public function update(Request $request)
    {
        $account = $request->user();
        $table   = $account->getTable(); // customers | users

        $validator = Validator::make($request->all(), [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['nullable', 'email', 'max:255', Rule::unique($table, 'email')->ignore($account->id)],
            'phone'   => ['required', 'string', 'max:30', Rule::unique($table, 'phone')->ignore($account->id)],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        // Set directly (User fillable doesn't include phone/address) then save.
        // Only touch keys the client actually sent — a partial update must not
        // blank out the fields it left out.
        $account->name  = $request->name;
        $account->phone = $request->phone;

        if ($request->has('email')) {
            $account->email = $request->email;
        }

        // Guard on the column existing, not on $fillable: these are direct
        // property sets, so mass-assignment rules never apply. User::$fillable
        // omits address, which silently dropped every agent's address edit
        // while still answering 200 "Profile updated."
        if ($request->has('address') && Schema::hasColumn($table, 'address')) {
            $account->address = $request->address;
        }

        $account->save();

        return $this->responseWithSuccess('Profile updated.', [
            'profile' => $this->profileInfo($account->fresh()),
        ]);
    }

    /**
     * Upload / replace the account's profile photo (multipart: `avatar`).
     * Customers keep a relative path on the record; agents/staff reuse the
     * shared `uploads` row their `image_id` points at.
     */
    public function updateAvatar(Request $request)
    {
        $account = $request->user();

        $validator = Validator::make($request->all(), [
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $file = $request->file('avatar');
        $name = 'cus_' . $account->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/customers'), $name);
        $path = 'uploads/customers/' . $name;

        if ($account instanceof Customer) {
            $old = $account->avatar;
            $account->avatar = $path;
            $account->save();
        } else {
            $upload = $account->image;
            $old = $upload?->image_one;

            if ($upload) {
                $upload->update(['image_one' => $path, 'type' => 'image']);
            } else {
                $upload = Upload::create([
                    'original'  => $path,
                    'type'      => 'image',
                    'image_one' => $path,
                ]);
                $account->image_id = $upload->id;
                $account->save();
            }
        }

        // Drop the previous file so the uploads folder doesn't grow forever.
        if ($old && $old !== $path && file_exists(public_path($old))) {
            @unlink(public_path($old));
        }

        return $this->responseWithSuccess('Profile photo updated.', [
            'profile' => $this->profileInfo($account->fresh()),
        ]);
    }

    public function changePassword(Request $request)
    {
        $account = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'confirmed', Password::min(6)],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        if (! $account->password || ! Hash::check($request->current_password, $account->password)) {
            return $this->responseWithError('Current password is incorrect.', [], 422);
        }

        // Hash explicitly so it works regardless of model cast differences.
        $account->password = Hash::make($request->new_password);
        $account->save();

        return $this->responseWithSuccess('Password changed.');
    }

    private function profileInfo($account): array
    {
        $isCustomer = $account instanceof Customer;

        return [
            'id'             => $account->id,
            'name'           => $account->name,
            'email'          => $account->email,
            'phone'          => $account->phone,
            'address'        => $account->address ?? null,
            'avatar'         => $this->avatarUrl($account, $isCustomer),
            'account_type'   => $isCustomer ? 'customer' : (str_contains(strtolower((string) optional($account->role)->name), 'agent') ? 'agent' : 'staff'),
            'tier'           => $isCustomer ? $account->tier : null,
            'status'         => is_string($account->status ?? null) ? $account->status : null,
            'bookings_count' => $isCustomer ? $account->bookings()->count() : null,
        ];
    }

    /** Customers store a relative avatar path; Users point at an uploads row. */
    private function avatarUrl($account, bool $isCustomer): ?string
    {
        if ($isCustomer) {
            $path = $account->avatar;
        } else {
            $upload = $account->image;
            $path   = $upload ? ($upload->image_one ?: $upload->original) : null;
        }

        return filled($path) ? asset($path) : null;
    }
}
