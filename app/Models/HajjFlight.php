<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A flight a hajj batch travels on, with its seat map.
 *
 * The pilgrim still carries `flight_no` and `seat_no` as text (every hajj
 * screen and the API read them); this is the catalogue that decides what can
 * be picked and which seats exist.
 */
class HajjFlight extends Model
{
    protected $fillable = [
        'flight_no', 'airline', 'departure_date', 'return_date',
        'seat_rows', 'seat_letters', 'notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date'    => 'date',
        'seat_rows'      => 'integer',
    ];

    /** Pilgrims booked onto this flight, matched on the number they carry. */
    public function pilgrims(): HasMany
    {
        return $this->hasMany(HajjPilgrim::class, 'flight_no', 'flight_no');
    }

    /**
     * Every seat on the aircraft, in boarding order: 1A, 1B … 30F.
     *
     * Generated from rows × letters rather than stored, so changing a 30-row
     * aircraft to a 40-row one is one field, not 60 new records.
     *
     * @return array<int, string>
     */
    public function seats(): array
    {
        $letters = preg_split('//', strtoupper((string) $this->seat_letters), -1, PREG_SPLIT_NO_EMPTY) ?: ['A'];
        $rows    = max(1, (int) $this->seat_rows);

        $seats = [];

        for ($row = 1; $row <= $rows; $row++) {
            foreach ($letters as $letter) {
                $seats[] = $row . $letter;
            }
        }

        return $seats;
    }

    public function seatCount(): int
    {
        return max(1, (int) $this->seat_rows) * max(1, strlen((string) $this->seat_letters));
    }

    /**
     * Seats already given out on this flight, as seat => pilgrim name.
     *
     * @return array<string, string>
     */
    public function takenSeats(): array
    {
        return $this->pilgrims()
            ->whereNotNull('seat_no')
            ->where('seat_no', '!=', '')
            ->pluck('name', 'seat_no')
            ->toArray();
    }

    /**
     * Make sure a flight exists for a number that arrived without a dropdown
     * (import, API, an older form), so the catalogue is never missing one that
     * pilgrims are already on.
     */
    public static function register(?string $flightNo): ?self
    {
        $flightNo = trim((string) $flightNo);

        if ($flightNo === '') {
            return null;
        }

        return static::firstOrCreate(['flight_no' => $flightNo]);
    }
}
