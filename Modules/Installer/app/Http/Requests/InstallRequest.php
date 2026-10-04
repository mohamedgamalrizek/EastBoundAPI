<?php

namespace Modules\Installer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The product installs as a single-agency ERP; the mode is stamped here
     * rather than accepted from the form.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['mode' => 'single']);
    }

    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:100'],
            'app_url' => ['required', 'url', 'max:2048'],
            'timezone' => ['required', 'timezone'],
            'mode' => ['required', 'in:single'],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer', 'between:1,65535'],
            'db_database' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9$_-]+$/'],
            'db_username' => ['required', 'string', 'max:128'],
            'db_password' => ['nullable', 'string', 'max:1024'],
            'admin_name' => ['required', 'string', 'max:100'],
            'admin_email' => ['required', 'email:rfc', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'admin_phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
