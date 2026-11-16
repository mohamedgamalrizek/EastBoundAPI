<?php

namespace App\Support;

use App\Models\User;

class CustomerAccess
{
    public static function restricted(): bool
    {
        $user = auth()->user();
        // Public intake, background jobs and customer/agent portals retain their own access rules.
        return $user instanceof User
            && ($user->isAdminUser() || (! $user->can_access('customer_portal_read') && ! $user->can_access('agent_portal_read')))
            && (! $user->can_access('customer_view_all') || ! $user->can_access('lead_view_all') || ! $user->can_access('opportunity_view_all'));
    }

    public static function constrain($query)
    {
        if (static::restricted() && ! auth()->user()->can_access('customer_view_all')) {
            $query->where('customers.assigned_to', auth()->id());
        }
        return $query;
    }
}
