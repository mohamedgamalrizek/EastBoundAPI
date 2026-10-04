<?php

namespace App\Repositories\Tour;

use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\TourGuide;
use App\Models\TourGuideAssignment;
use App\Models\TourSchedule;
use App\Models\User;
use App\Repositories\Concerns\StoresPublicImage;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Str;
use Throwable;

class TourRepository implements TourInterface
{
    use ReturnFormatTrait;
    use StoresPublicImage;

    public function category()
    {
        return [
            'categories' => PackageCategory::orderBy('id', 'desc')->paginate(10),
        ];
    }

    public function categoryCreate()
    {
        return [
            'categories' => PackageCategory::orderBy('id', 'desc')->get(),
        ];
    }

    public function categoryStore($request)
    {
        try {

            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            $slug = Str::slug($request->name);

            $exists = PackageCategory::where('slug', $slug)->exists();
            if ($exists) {
                $slug .= '-'.time();
            }

            $category = new PackageCategory;
            $category->name = $request->name;
            $category->slug = $slug;
            $category->description = $request->description;
            $category->status = $request->status;
            $category->save();

            return $this->responseWithSuccess(___('alert.successfully_added'));

        } catch (Throwable $th) {

            \Log::error('Category Store Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function categoryEdit($id)
    {
        return [
            'category' => PackageCategory::findOrFail($id),
            'categories' => PackageCategory::orderBy('name')->get(),
        ];
    }

    public function categoryUpdate($request, $id)
    {
        try {

            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            $category = PackageCategory::findOrFail($id);

            $slug = Str::slug($request->name);

            $exists = PackageCategory::where('slug', $slug)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                $slug .= '-'.time();
            }

            $category->name = $request->name;
            $category->slug = $slug;
            $category->description = $request->description;
            $category->status = $request->status;
            $category->save();

            return $this->responseWithSuccess(___('alert.successfully_updated'));

        } catch (Throwable $th) {

            \Log::error('Category Update Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function categoryDelete($id)
    {
        try {

            $category = PackageCategory::findOrFail($id);
            $category->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));

        } catch (Throwable $th) {

            \Log::error('Category Delete Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function schedule()
    {
        return [
            'tour_schedules' => TourSchedule::orderBy('start_date')->paginate(10),
        ];
    }

    public function scheduleCreate()
    {
        return [
            'packages' => Package::orderBy('title')->get(),
        ];
    }

    public function scheduleStore($request)
    {

        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'seats' => 'required|integer|min:1',
            'booked' => 'nullable|integer|min:0|lte:seats',
            'status' => 'required|in:open,full,closed',
        ], [
            'end_date.after_or_equal' => 'End date must be greater than or equal to start date.',
            'booked.lte' => 'Booked seats cannot exceed total seats.',
        ]);

        try {

            $package = Package::findOrFail($request->package_id);

            $schedule = new TourSchedule;

            $schedule->package_id = $package->id;
            $schedule->package_title = $package->title;

            $schedule->start_date = $request->start_date;
            $schedule->end_date = $request->end_date;
            $schedule->seats = $request->seats;
            $schedule->booked = $request->booked;
            $schedule->status = $request->status;

            $schedule->save();

            return $this->responseWithSuccess(___('alert.successfully_added'));

        } catch (Throwable $th) {

            \Log::error('Schedule Store Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function scheduleEdit($id)
    {
        return [
            'tour_schedule' => TourSchedule::findOrFail($id),
            'packages' => Package::orderBy('title')->get(),
        ];
    }

    public function scheduleUpdate($request, $id)
    {

        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'seats' => 'required|integer|min:1',
            'booked' => 'nullable|integer|min:0|lte:seats',
            'status' => 'required|in:open,full,closed',
        ], [
            'end_date.after_or_equal' => 'End date must be greater than or equal to start date.',
            'booked.lte' => 'Booked seats cannot exceed total seats.',
        ]);

        try {

            $package = Package::findOrFail($request->package_id);

            $schedule = TourSchedule::findOrFail($id);

            $schedule->package_id = $package->id;
            $schedule->package_title = $package->title;

            $schedule->start_date = $request->start_date;
            $schedule->end_date = $request->end_date;
            $schedule->seats = $request->seats;
            $schedule->booked = $request->booked;
            $schedule->status = $request->status;

            $schedule->save();

            return $this->responseWithSuccess(___('alert.successfully_updated'));

        } catch (Throwable $th) {

            \Log::error('Schedule Update Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function scheduleDelete($id)
    {
        try {

            $schedule = TourSchedule::findOrFail($id);
            $schedule->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));

        } catch (Throwable $th) {

            \Log::error('Schedule Delete Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function guides()
    {
        return [
            // assignments + ratings feed effective_status and avg_rating.
            'tour_guides' => TourGuide::with(['assignments', 'ratings'])->orderBy('id', 'desc')->paginate(10),
        ];
    }

    public function guidesCreate()
    {
        return [
            'tour_guides' => TourGuide::orderBy('id', 'desc')->get(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ];
    }

    public function guidesStore($request)
    {
        try {

            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|in:active,inactive,on_tour',
                'photo' => 'nullable|image|max:2048',
                'photo_url' => 'nullable|string|max:500',
                'bio' => 'nullable|string|max:2000',
                'user_id' => 'nullable|integer|exists:users,id',
            ]);

            $guide = new TourGuide;
            $guide->name = $request->name;
            $guide->photo = $this->resolveImage($request, 'photo', 'photo_url', 'guides');
            $guide->phone = $request->phone;
            $guide->languages = $request->languages;
            $guide->experience_years = $request->experience_years;
            $guide->bio = $request->bio;
            $guide->user_id = $request->user_id;
            $guide->status = $request->status;
            $guide->save();

            return $this->responseWithSuccess(___('alert.successfully_added'));

        } catch (Throwable $th) {

            \Log::error('Guide Store Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function guidesEdit($id)
    {
        return [
            'tour_guide' => TourGuide::findOrFail($id),
            'tour_guides' => TourGuide::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ];
    }

    public function guidesUpdate($request, $id)
    {
        try {

            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|in:active,inactive,on_tour',
                'photo' => 'nullable|image|max:2048',
                'photo_url' => 'nullable|string|max:500',
                'bio' => 'nullable|string|max:2000',
                'user_id' => 'nullable|integer|exists:users,id',
            ]);

            $guide = TourGuide::findOrFail($id);

            $guide->name = $request->name;
            $guide->photo = $this->resolveImage($request, 'photo', 'photo_url', 'guides', $guide->photo);
            $guide->phone = $request->phone;
            $guide->languages = $request->languages;
            $guide->experience_years = $request->experience_years;
            $guide->bio = $request->bio;
            $guide->user_id = $request->user_id;
            $guide->status = $request->status;
            $guide->save();

            return $this->responseWithSuccess(___('alert.successfully_updated'));

        } catch (Throwable $th) {

            \Log::error('Guide Update Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function guidesDelete($id)
    {
        try {

            $guide = TourGuide::findOrFail($id);
            $guide->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));

        } catch (Throwable $th) {

            \Log::error('Guide Delete Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function guideAssignments()
    {
        return [
            'assignments' => TourGuideAssignment::with(['guide', 'package'])
                ->orderBy('start_date', 'desc')->paginate(15),
            'tour_guides' => TourGuide::where('status', '!=', 'inactive')->orderBy('name')->get(),
            'packages' => Package::where('status', 'active')->orderBy('title')->get(['id', 'title', 'destination']),
        ];
    }

    public function guideAssignmentsStore($request)
    {
        try {

            $request->validate([
                'tour_guide_id' => 'required|integer|exists:tour_guides,id',
                'package_id' => 'required|integer|exists:packages,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'notes' => 'nullable|string|max:2000',
            ]);

            $guide = TourGuide::findOrFail($request->tour_guide_id);

            if ($guide->hasOverlap($request->start_date, $request->end_date)) {
                return $this->responseWithError($guide->name.' '.___('label.guide_already_assigned_in_range'));
            }

            TourGuideAssignment::create([
                'tour_guide_id' => $request->tour_guide_id,
                'package_id' => $request->package_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'notes' => $request->notes,
                'status' => 'scheduled',
            ]);

            return $this->responseWithSuccess(___('alert.successfully_added'));

        } catch (Throwable $th) {

            \Log::error('Guide Assignment Store Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function guideAssignmentsStatus($request, $id)
    {
        try {

            $request->validate([
                'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            ]);

            $assignment = TourGuideAssignment::findOrFail($id);
            $assignment->status = $request->status;
            $assignment->save();

            return $this->responseWithSuccess(___('alert.successfully_updated'));

        } catch (Throwable $th) {

            \Log::error('Guide Assignment Status Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function guideAssignmentsDelete($id)
    {
        try {

            $assignment = TourGuideAssignment::findOrFail($id);
            $assignment->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));

        } catch (Throwable $th) {

            \Log::error('Guide Assignment Delete Error: '.$th->getMessage());

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function reports()
    {
        $totalSeats = (int) TourSchedule::sum('seats');
        $totalBooked = (int) TourSchedule::sum('booked');

        return [
            'totalPackages' => Package::count(),
            'totalCategories' => PackageCategory::count(),
            'totalSchedules' => TourSchedule::count(),
            'totalGuides' => TourGuide::count(),
            'totalSeats' => $totalSeats,
            'totalBooked' => $totalBooked,
            'occupancy' => $totalSeats > 0 ? round($totalBooked / $totalSeats * 100) : 0,

            'byCategory' => Package::selectRaw('category, count(*) as total')
                ->groupBy('category')
                ->pluck('total', 'category'),

            'scheduleByStatus' => TourSchedule::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),

            'topSchedules' => TourSchedule::orderByDesc('booked')
                ->take(5)
                ->get(),
        ];
    }
}
