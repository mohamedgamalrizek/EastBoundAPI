<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A travelling group for Hajj / Umrah — the unit that shares a bus, a hotel
 * block and a leader.
 *
 * The pilgrim still carries the group's name rather than its id (every hajj
 * screen, the API and the search filter read that column), so this table is
 * the catalogue: it decides what the office can pick, and it can hold a group
 * that nobody has been placed in yet.
 */
class HajjGroup extends Model
{
    protected $fillable = ['name', 'leader', 'notes'];

    /** Pilgrims placed in this group, matched on the name they carry. */
    public function pilgrims(): HasMany
    {
        return $this->hasMany(HajjPilgrim::class, 'group_name', 'name');
    }

    /**
     * Make sure a group exists for a name that arrived from somewhere without
     * a dropdown — the mobile API, an import, an older form. Keeps the
     * catalogue a superset of the names actually in use, so the Groups screen
     * can never hide a group that has pilgrims in it.
     */
    public static function register(?string $name): ?self
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        return static::firstOrCreate(['name' => $name]);
    }

    /** Names for a picker, in the order the office reads them. */
    public static function names()
    {
        return static::orderBy('name')->pluck('name');
    }
}
