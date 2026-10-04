<?php

namespace App\Http\Requests\Todo;

use App\Enums\Status;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreTodoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'title'          => ['required'],
            'user'           => ['required', $this->assignableUserRule()],
            'date'           => ['required']
    ];
    }

    private function assignableUserRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $isAssignable = User::whereKey($value)
                ->where('status', Status::ACTIVE->value)
                ->whereHas('role', fn ($role) => $role->where('slug', '!=', 'customer'))
                ->exists();

            if (! $isAssignable) {
                $fail('The selected user is invalid.');
            }
        };
    }
}
