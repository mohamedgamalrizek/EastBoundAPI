<?php

namespace App\Repositories\Package;

use App\Models\Package;
use App\Models\PackageCategory;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use App\Repositories\Concerns\StoresPublicImage;
use App\Repositories\Package\PackageInterface;

class PackageRepository implements PackageInterface
{
    use ReturnFormatTrait;
    use StoresPublicImage;

    protected $model;

    public function __construct(Package $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model::orderByDesc('id')->get();
    }

    public function get($id)
    {
        return $this->model::with('itineraries')->find($id);
    }

    public function store($request)
    {
        try {
            DB::transaction(function () use ($request) {
                $package = $this->model->newQuery()->create($this->data($request));
                $this->syncItineraries($package, $request->input('itinerary', []));
            });

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function update($request, $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $package = $this->model::findOrFail($id);
                $package->update($this->data($request, $package->image));
                $this->syncItineraries($package, $request->input('itinerary', []));
            });

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

    /* ---- internal -------------------------------------------------------- */

    private function data($request, ?string $currentImage = null): array
    {
        return [
            'title'           => $request->title,
            'destination'     => $request->destination,
            'category_id'     => $request->category_id,
            // `category` keeps the name so list/report views and the public
            // site keep rendering without a join.
            'category'        => PackageCategory::find($request->category_id)?->name,
            'price'           => $request->price,
            'child_price'     => $request->filled('child_price') ? $request->child_price : null,
            'single_supplement' => $request->filled('single_supplement') ? $request->single_supplement : null,
            'duration_days'   => $request->duration_days,
            'duration_nights' => $request->duration_nights,
            'description'     => $request->description,
            'inclusions'      => $request->inclusions,
            'exclusions'      => $request->exclusions,
            'status'          => $request->status,
            'image'           => $this->resolveImage($request, 'image', 'image_url', 'packages', $currentImage),
        ];
    }

    /**
     * Replace the package's day-by-day plan with the submitted rows.
     * Rows without a title are blank template rows and are skipped.
     */
    private function syncItineraries(Package $package, $rows): void
    {
        $rows = collect(is_array($rows) ? $rows : [])
            ->filter(fn ($row) => filled($row['title'] ?? null))
            ->values();

        $package->itineraries()->delete();

        foreach ($rows as $i => $row) {
            $package->itineraries()->create([
                'day_number'  => (int) ($row['day_number'] ?? 0) ?: $i + 1,
                'title'       => $row['title'],
                'description' => $row['description'] ?? null,
            ]);
        }
    }
}
