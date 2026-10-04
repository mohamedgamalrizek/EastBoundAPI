<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ResolvesCustomer;
use Illuminate\Http\Request;

/**
 * The bell icon, shared by every web panel (admin, customer, agent, staff,
 * SaaS). Which rows an account sees is entirely down to `accountFor()`: a
 * customer-portal session resolves to its Customer record (matching how the
 * mobile app writes notifications), everyone else uses their User record —
 * same identity `Notification::notify()` was given when the row was created.
 */
class NotificationController extends Controller
{
    use ResolvesCustomer;

    public function index(Request $request)
    {
        $account = $this->accountFor($request);

        return view('backend.notifications.index', [
            'notifications' => Notification::query()->ownedBy($account)->latest()->limit(100)->get(),
        ]);
    }

    public function dropdown(Request $request)
    {
        $account = $this->accountFor($request);

        return view('backend.partials.notification-dropdown', [
            'notifications' => Notification::query()->ownedBy($account)->latest()->limit(8)->get(),
        ]);
    }

    public function unreadCount(Request $request)
    {
        $unread = Notification::query()->ownedBy($this->accountFor($request))->where('is_read', false)->count();

        return response()->json(['unread' => $unread]);
    }

    public function markRead(Request $request, $id)
    {
        $notification = Notification::query()->ownedBy($this->accountFor($request))->find($id);

        if (! $notification) {
            return response()->json(['status' => false, 'message' => ___('alert.not_found')], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['status' => true]);
    }

    public function markAllRead(Request $request)
    {
        Notification::query()->ownedBy($this->accountFor($request))->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['status' => true]);
    }

    /** The identity `Notification::notify()` was given: Customer for a customer-portal session, User otherwise. */
    private function accountFor(Request $request)
    {
        return $this->customer($request) ?? $request->user();
    }
}
