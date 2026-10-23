<?php

namespace App\Services\Dmc;

use App\Models\Dmc\Activity;
use App\Models\Dmc\Trip;
use App\Models\Dmc\TripAlert;
use App\Models\Dmc\TripItem;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\Task;
use App\Models\TourGuide;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TripOperations
{
    public function assign(TripItem $item, array $data): TripItem
    {
        return DB::transaction(function () use ($item, $data) {
            $trip = Trip::lockForUpdate()->findOrFail($item->trip_id);
            $this->ensure(in_array($trip->status, ['Planning', 'Ready']), 'Resources cannot be edited after operations start.');
            $item = TripItem::lockForUpdate()->findOrFail($item->id);
            foreach (['hotel_id' => 'hotel', 'driver_id' => 'transport', 'guide_id' => 'guide', 'activity_id' => 'activity'] as $field => $type) {
                $this->ensure(empty($data[$field]) || $item->quotationItem->service_type === $type, 'Select only the resource appropriate to this service type.');
            }
            foreach (['guide_id' => TourGuide::class, 'driver_id' => Driver::class, 'activity_id' => Activity::class] as $field => $model) {
                if (empty($data[$field])) {
                    continue;
                }
                $resource = $model::lockForUpdate()->findOrFail($data[$field]);
                $this->ensure(! in_array(strtolower((string) ($resource->status ?? 'active')), ['inactive', 'disabled']), 'This resource is inactive.');
                $others = TripItem::where($field, $resource->id)->where('service_date', $item->service_date)
                    ->where('id', '!=', $item->id)->where('status', 'Confirmed')
                    ->whereHas('trip', fn ($query) => $query->where('status', '!=', 'Cancelled'));
                if ($field === 'activity_id') {
                    $used = $others->with('quotationItem')->get()->sum(fn ($row) => $row->quotationItem->quantity);
                    $this->ensure($resource->active && $used + $item->quotationItem->quantity <= $resource->capacity, 'Activity capacity is insufficient.');
                } else {
                    $this->ensure(! $others->exists(), 'This guide or driver is already assigned on this date.');
                }
            }
            $item->update($data + ['status' => 'Planned']);
            if ($trip->status === 'Ready') {
                $trip->update(['status' => 'Planning']);
            }

            return $item;
        }, 3);
    }

    public function confirmItem(TripItem $item): TripItem
    {
        return DB::transaction(function () use ($item) {
            Trip::lockForUpdate()->findOrFail($item->trip_id);
            $item = TripItem::lockForUpdate()->findOrFail($item->id);
            $item = $this->assign($item, $item->only(['hotel_id', 'driver_id', 'guide_id', 'activity_id', 'confirmation', 'notes']));
            $required = ['hotel' => 'hotel_id', 'transport' => 'driver_id', 'guide' => 'guide_id', 'activity' => 'activity_id'];
            $field = $required[$item->quotationItem->service_type] ?? null;
            $this->ensure(filled($item->confirmation), 'A supplier confirmation reference is required.');
            $this->ensure($field === null || filled($item->{$field}), 'Assign the required service resource first.');
            $item->update(['status' => 'Confirmed']);

            return $item;
        }, 3);
    }

    public function transition(Trip $trip, string $action): Trip
    {
        return DB::transaction(function () use ($trip, $action) {
            $trip = Trip::lockForUpdate()->findOrFail($trip->id);
            if ($action === 'ready') {
                $this->ensure($trip->status === 'Planning' && $trip->items()->exists()
                    && ! $trip->items()->where('status', '!=', 'Confirmed')->exists(), 'Confirm every service before marking a trip ready.');
                $this->ensure(! $trip->requests()->where('status', '!=', 'Ordered')->exists(), 'Every purchase request needs an awarded vendor order.');
                $trip->update(['status' => 'Ready']);
            } elseif ($action === 'start') {
                $this->ensure($trip->status === 'Ready' && $trip->starts_on->lte(today()), 'Only a ready trip whose start date has arrived can start.');
                $trip->update(['status' => 'In Progress']);
            } elseif ($action === 'complete') {
                $this->ensure($trip->status === 'In Progress' && $trip->ends_on->lte(today()), 'Only an in-progress trip whose end date has arrived can complete.');
                $trip->update(['status' => 'Completed']);
            } else {
                abort(422, 'Unsupported trip action.');
            }

            return $trip;
        }, 3);
    }

    public function generateAlerts(): int
    {
        $count = 0;
        Trip::whereIn('status', ['Planning', 'Ready'])->whereDate('starts_on', '<=', today()->addDays(7))
            ->whereDate('ends_on', '>=', today())->orderBy('id')->chunkById(100, function ($trips) use (&$count) {
                foreach ($trips as $trip) {
                    $days = (int) today()->diffInDays($trip->starts_on, false);
                    foreach (['seven-days' => 7, 'one-day' => 1, 'departure' => 0] as $kind => $limit) {
                        $relevant = $days <= 0 ? 'departure' : ($days <= 1 ? 'one-day' : 'seven-days');
                        if ($days > $limit || $kind !== $relevant) {
                            continue;
                        }
                        $created = DB::transaction(function () use ($trip, $kind, $limit) {
                            $trip = Trip::lockForUpdate()->findOrFail($trip->id);
                            $alert = TripAlert::firstOrCreate(['trip_id' => $trip->id, 'kind' => $kind]);
                            if (! $alert->wasRecentlyCreated) {
                                return false;
                            }
                            $task = Task::create([
                                'assigned_to' => $trip->manager_id, 'title' => 'Trip #'.$trip->id.' '.$kind.' readiness check',
                                'project' => 'DMC Trip #'.$trip->id, 'priority' => 'High', 'due_date' => max(today()->toDateString(), $trip->starts_on->copy()->subDays($limit)->toDateString()), 'status' => 'Todo',
                            ]);
                            $alert->update(['task_id' => $task->id]);
                            Notification::notify($trip->manager, $task->title, 'Review resources, supplier confirmations and outstanding purchases.', 'operations');

                            return true;
                        }, 3);
                        $count += $created ? 1 : 0;
                    }
                }
            });

        return $count;
    }

    private function ensure(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['workflow' => $message]);
        }
    }
}
