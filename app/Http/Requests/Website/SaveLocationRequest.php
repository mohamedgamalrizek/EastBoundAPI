<?php
namespace App\Http\Requests\Website;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class SaveLocationRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can_access($this->isMethod('POST') ? 'cms_create' : 'cms_update') ?? false; }
    public function rules(): array
    {
        $city = $this->route('type') === 'cities';
        $unique = Rule::unique($city ? 'website_cities' : 'website_destinations', 'slug')->ignore($this->route('id'));
        if ($city) $unique->where('destination_id', $this->input('destination_id'));
        return ['name'=>['required','string','max:255'], 'slug'=>['required','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/','max:150',$unique],
            'destination_id'=>[$city ? 'required' : 'prohibited','integer','exists:website_destinations,id'],
            'headline'=>['nullable','string','max:255'], 'description'=>['nullable','string','max:5000'],
            'about'=>['nullable','string','max:30000'], 'image_file'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:8192'],
            'meta_title'=>['nullable','string','max:255'], 'meta_description'=>['nullable','string','max:1000'],
            'active'=>['nullable','boolean'], 'sort_order'=>['required','integer','min:0','max:100000']];
    }
}
