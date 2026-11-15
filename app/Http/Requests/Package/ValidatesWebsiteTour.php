<?php
namespace App\Http\Requests\Package;
use Illuminate\Validation\Rule;
trait ValidatesWebsiteTour
{
    private function websiteRules(): array
    {
        return [
            'website_fields'=>['nullable','boolean'], 'website_published'=>['nullable','boolean'], 'website_featured'=>['nullable','boolean'],
            'website_destination_id'=>['nullable','required_if:website_published,1','integer','exists:website_destinations,id'],
            'website_slug'=>['nullable','required_if:website_published,1','string','max:170','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',Rule::unique('packages','website_slug')->ignore($this->route('id'))],
            'website_type'=>['nullable',Rule::in(['package','day-tour'])],
            'website_city_ids'=>['nullable','array','max:100'],
            'website_city_ids.*'=>['integer','distinct',Rule::exists('website_cities','id')->where('destination_id',$this->input('website_destination_id'))],
            'website_full_title'=>['nullable','string','max:255'], 'website_tag'=>['nullable','string','max:100'],
            'website_start_point'=>['nullable','string','max:255'], 'website_duration_hours'=>['nullable','string','max:100'],
            'website_why_choose'=>['nullable','string','max:10000'], 'website_travel_tips'=>['nullable','string','max:10000'], 'website_optional_excursions'=>['nullable','string','max:10000'],
        ];
    }
}
