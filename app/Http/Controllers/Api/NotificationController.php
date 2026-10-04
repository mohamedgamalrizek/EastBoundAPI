<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;

/**
 * In-app notifications for any account (customer or agent/staff).
 * Returns notifications owned by the account (polymorphic) plus general
 * (NULL-owner) broadcasts.
 */
class NotificationController extends Controller
{
    use ApiReturnFormatTrait;

    public function index(Request $request)
    {
        $items = $this->ownedQuery($request)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (Notification $n) => $this->info($n));

        return $this->responseWithSuccess('Notifications fetched.', [
            'unread'        => $this->ownedQuery($request)->where('is_read', false)->count(),
            'notifications' => $items,
        ]);
    }

    public function markRead(Request $request, $id)
    {
        $n = $this->ownedQuery($request)->find($id);
        if (! $n) {
            return $this->responseWithError('Notification not found.', [], 404);
        }
        $n->update(['is_read' => true]);

        return $this->responseWithSuccess('Marked as read.');
    }

    public function markAllRead(Request $request)
    {
        $this->ownedQuery($request)->where('is_read', false)->update(['is_read' => true]);

        return $this->responseWithSuccess('All marked as read.');
    }

    /**
     * Notifications belonging to this account + general broadcasts.
     */
    private function ownedQuery(Request $request)
    {
        return Notification::query()->ownedBy($request->user());
    }

    private function info(Notification $n): array
    {
        return [
            'id'         => $n->id,
            'title'      => $n->title,
            'body'       => $n->body,
            'category'   => $n->category,
            'is_read'    => (bool) $n->is_read,
            'created_at' => optional($n->created_at)->toDateTimeString(),
        ];
    }
}
