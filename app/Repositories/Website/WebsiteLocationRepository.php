<?php
namespace App\Repositories\Website;
use App\Models\WebsiteDestination;
use App\Models\WebsiteCity;
use App\Repositories\Concerns\StoresPublicImage;
class WebsiteLocationRepository
{
    use StoresPublicImage;
    public function query(string $type) { return ($type === 'destinations' ? WebsiteDestination::class : WebsiteCity::class)::query(); }
    public function save(string $type, $request, $record)
    {
        $data = $request->validated();
        unset($data['image_file']);
        $data['image'] = $request->hasFile('image_file') ? $this->storePublicImage($request->file('image_file'), 'website') : $record->image;
        $data['active'] = $request->boolean('active');
        $record->fill($data)->save();
        return $record;
    }
}
