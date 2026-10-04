<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An FCM registration token for one signed-in device. Push targets are
 * resolved per account: every device the customer (or staff user) is signed
 * in on gets the notification.
 */
class DeviceToken extends Model
{
    protected $fillable = [
        'notifiable_type', 'notifiable_id', 'token', 'platform',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    /** Register (or move) a device token for an account. */
    public static function register($account, string $token, ?string $platform = null): self
    {
        return static::updateOrCreate(
            ['token' => $token],
            [
                'notifiable_type' => get_class($account),
                'notifiable_id'   => $account->id,
                'platform'        => $platform,
            ]
        );
    }

    /** All tokens registered to an account. */
    public static function tokensFor($account): array
    {
        if (! $account) {
            return [];
        }

        return static::where('notifiable_type', get_class($account))
            ->where('notifiable_id', $account->id)
            ->pluck('token')
            ->all();
    }
}
