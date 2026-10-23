<?php

namespace App\Http\Requests\Dmc;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        $module = config('dmc-modules.'.$this->route('module'));
        if (! $module) {
            return false;
        }
        $permission = $module['write_permission'] ?? $module['permission'].($this->isMethod('POST') ? '_create' : '_update');

        return $this->user()?->can_access($permission) ?? false;
    }

    public function rules(): array
    {
        $module = config('dmc-modules.'.$this->route('module'));
        $rules = collect($module['fields'])->mapWithKeys(fn ($field, $name) => [
            $name => isset($field['permission']) && ! $this->user()->can_access($field['permission']) ? ['missing'] : $field['rules'],
        ])->all();
        if ($this->route('module') === 'quotations') {
            $rules += [
                'items' => ['required', 'array', 'min:1', 'max:200'],
                'items.*' => ['array:service_type,description,supplier_id,service_date,quantity,unit_cost,unit_price'],
                'items.*.service_type' => ['required', Rule::in(['hotel', 'transport', 'guide', 'activity', 'domestic_flight'])],
                'items.*.description' => ['required', 'string', 'max:1000'],
                'items.*.supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
                'items.*.service_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on', 'before_or_equal:ends_on'],
                'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
                'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:9999999'],
                'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            ];
        }
        if ($this->route('module') === 'websites') {
            foreach (['name', 'slug', 'domain'] as $field) {
                $rules[$field][] = Rule::unique('dmc_websites', $field)->ignore($this->route('record'));
            }
        }
        if ($this->route('module') === 'campaign-spends') {
            $rules['reference'][] = Rule::unique('dmc_campaign_spends', 'reference')
                ->where(fn ($query) => $query->where('campaign_id', $this->input('campaign_id'))->where('website_id', $this->input('website_id')))
                ->ignore($this->route('record'));
        }
        foreach (['vendor-payments' => 'paid_on', 'vendor-bills' => 'issued_on', 'trip-expenses' => 'spent_on', 'campaign-spends' => 'spent_on'] as $module => $field) {
            if ($this->route('module') === $module) {
                $rules[$field][] = 'before_or_equal:today';
            }
        }

        return $rules;
    }
}
