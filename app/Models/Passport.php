<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class Passport extends Model
{
    public const STATUS_VALID = 'Valid';
    public const STATUS_EXPIRING = 'Expiring';
    public const STATUS_EXPIRED = 'Expired';
    public const EXPIRING_WITHIN_MONTHS = 6;

    protected $fillable = [
        'customer_id', 'holder_name', 'passport_no', 'nationality',
        'issue_date', 'expiry_date', 'status',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Passport $passport): void {
            $passport->status = static::deriveStatus($passport->expiry_date);
        });
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_VALID,
            self::STATUS_EXPIRING,
            self::STATUS_EXPIRED,
        ];
    }

    public static function deriveStatus($expiryDate): string
    {
        if (blank($expiryDate)) {
            return self::STATUS_VALID;
        }

        $expiry = $expiryDate instanceof Carbon
            ? $expiryDate->copy()->startOfDay()
            : Carbon::parse($expiryDate)->startOfDay();

        if ($expiry->lt(today())) {
            return self::STATUS_EXPIRED;
        }

        return $expiry->lte(today()->addMonths(self::EXPIRING_WITHIN_MONTHS))
            ? self::STATUS_EXPIRING
            : self::STATUS_VALID;
    }

    public static function refreshStoredStatuses(): int
    {
        $updated = 0;

        static::query()
            ->select(['id', 'expiry_date', 'status'])
            ->chunkById(200, function ($passports) use (&$updated): void {
                foreach ($passports as $passport) {
                    $status = static::deriveStatus($passport->expiry_date);

                    if ($passport->status === $status) {
                        continue;
                    }

                    $passport->forceFill(['status' => $status])->save();
                    $updated++;
                }
            });

        return $updated;
    }
}
