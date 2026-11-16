<?php

namespace App\Models\Concerns;

use App\Support\CustomerAccess;
use Illuminate\Validation\ValidationException;

trait HasAssignedRecordAccess
{
    public static function bootHasAssignedRecordAccess(): void
    {
        static::addGlobalScope('assigned_record_access', function ($query) {
            if (method_exists(static::class, 'constrainRecordAccess')) {
                static::constrainRecordAccess($query);
                return;
            }
            if (! CustomerAccess::restricted()) return;
            foreach (static::accessParents() as $column => $model) {
                $qualified = $query->getModel()->qualifyColumn($column);
                $query->where(fn ($q) => $q->whereNull($qualified)
                    ->orWhereIn($qualified, $model::query()->select((new $model)->qualifyColumn('id'))));
            }
        });
        static::saving(function ($record) {
            if (! CustomerAccess::restricted()) return;
            // A hidden record cannot be relinked via an identifier submitted manually.
            foreach (static::accessParents() as $column => $model) {
                if ($record->{$column} !== null && ! $model::whereKey($record->{$column})->exists()) {
                    throw ValidationException::withMessages([$column => 'The selected record is not available.']);
                }
            }
        });
    }
}
