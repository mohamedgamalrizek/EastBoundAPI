<?php

namespace App\Support;

class LeadAccess
{
    public static function constrain($query, string $column = 'leads.assigned_to')
    {
        $user = auth()->user();
        if ($user && ! $user->can_access('lead_view_all')) {
            $query->where($column, $user->id);
        }
        return $query;
    }
}
