<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TourGuide;
use App\Models\TourGuideAssignment;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ApiTransformTrait;
use Illuminate\Http\Request;

/**
 * Guide role in the mobile app: my assignments + start/complete.
 *
 * A guide is a users-table login linked from tour_guides.user_id; the admin
 * makes the link on the guide form. Start/complete here is what keeps the
 * backend's assignment status (and so the guide's on_tour badge) truthful
 * without anyone in the office touching it.
 */
class GuideController extends Controller
{
    use ApiReturnFormatTrait, ApiTransformTrait;

    public function assignments(Request $request)
    {
        $guide = $this->guide($request);
        if (! $guide) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $assignments = TourGuideAssignment::with('package')
            ->where('tour_guide_id', $guide->id)
            ->orderByRaw("field(status, 'in_progress', 'scheduled', 'completed', 'cancelled')")
            ->orderBy('start_date')
            ->get();

        return $this->responseWithSuccess('Assignments fetched.', [
            'assignments' => $assignments->map(fn ($a) => $this->assignmentInfo($a))->values(),
        ]);
    }

    public function start(Request $request, $id)
    {
        return $this->transition($request, $id, from: 'scheduled', to: 'in_progress', label: 'started');
    }

    public function complete(Request $request, $id)
    {
        return $this->transition($request, $id, from: 'in_progress', to: 'completed', label: 'completed');
    }

    private function transition(Request $request, $id, string $from, string $to, string $label)
    {
        $guide = $this->guide($request);
        if (! $guide) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $assignment = TourGuideAssignment::with('package')
            ->where('tour_guide_id', $guide->id)
            ->find($id);

        if (! $assignment) {
            return $this->responseWithError('Assignment not found.', [], 404);
        }

        if ($assignment->status !== $from) {
            return $this->responseWithError("Only a {$from} tour can be {$label}.", [], 422);
        }

        $assignment->status = $to;
        $assignment->save();

        return $this->responseWithSuccess("Tour {$label}.", [
            'assignment' => $this->assignmentInfo($assignment),
        ]);
    }

    private function guide(Request $request): ?TourGuide
    {
        $user = $request->user();

        if (! $user instanceof \App\Models\User) {
            return null;
        }

        return TourGuide::where('user_id', $user->id)->first();
    }

    private function assignmentInfo(TourGuideAssignment $a): array
    {
        // Headcount the guide should expect: confirmed/paid bookings of this
        // package departing inside the assignment window.
        $travelers = (int) Booking::where('package_id', $a->package_id)
            ->whereIn('status', ['confirmed', 'paid'])
            ->whereBetween('travel_date', [$a->start_date, $a->end_date])
            ->sum('travelers');

        return [
            'id'         => $a->id,
            'status'     => $a->status,
            'start_date' => optional($a->start_date)->toDateString(),
            'end_date'   => optional($a->end_date)->toDateString(),
            'notes'      => $a->notes,
            'travelers'  => $travelers,
            'package'    => $a->package ? [
                'id'          => $a->package->id,
                'title'       => $a->package->title,
                'destination' => $a->package->destination,
                'image_url'   => $this->imageUrl($a->package->image, 'pkg' . $a->package->id),
            ] : null,
        ];
    }
}
