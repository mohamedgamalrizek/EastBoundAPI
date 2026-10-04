<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'notifiable_type', 'notifiable_id',
        'title', 'body', 'category', 'is_read',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Notifications belonging to this account (Customer or User), plus:
     * legacy rows keyed only by `user_id` when the account is a User, and
     * general (no-owner) broadcasts visible to everyone. Single source of
     * truth for "what should this account's bell show" — used by the API
     * and the web session alike so the two surfaces can't drift apart.
     */
    public function scopeOwnedBy(Builder $query, Model $account): Builder
    {
        $type = get_class($account);
        $id   = $account->id;

        return $query->where(function (Builder $q) use ($account, $type, $id) {
            $q->where(function (Builder $q2) use ($type, $id) {
                $q2->where('notifiable_type', $type)->where('notifiable_id', $id);
            });

            if ($account instanceof User) {
                $q->orWhere(function (Builder $q2) use ($id) {
                    $q2->whereNull('notifiable_type')->where('user_id', $id);
                });
            }

            $q->orWhere(function (Builder $q2) {
                $q2->whereNull('notifiable_type')->whereNull('user_id');
            });
        });
    }

    /**
     * Create a notification for a given account (Customer or User).
     */
    public static function notify($account, string $title, ?string $body = null, string $category = 'general'): self
    {
        $notification = static::create([
            'notifiable_type' => $account ? get_class($account) : null,
            'notifiable_id'   => $account?->id,
            'user_id'         => $account instanceof \App\Models\User ? $account->id : null,
            'title'           => $title,
            'body'            => $body,
            'category'        => $category,
            'is_read'         => false,
        ]);

        // Mirror to the account's devices when FCM is configured. PushService
        // never throws, so a broken push setup cannot fail the caller.
        if ($account) {
            \App\Services\Messaging\PushService::sendTo($account, $title, $body, [
                'category'        => $category,
                'notification_id' => (string) $notification->id,
            ]);
        }

        return $notification;
    }
}
