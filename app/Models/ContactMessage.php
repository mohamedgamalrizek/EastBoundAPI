<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'website',
        'language',
        'country',
        'campaign_source',
        'message',
        'status',
    ];

    public function statusBadge(): string
    {
        $map = ['new' => 'warning', 'read' => 'info', 'replied' => 'success'];
        $class = $map[$this->status] ?? 'warning';
        return "<span class='bullet-badge bullet-badge-{$class}'>" . ucfirst($this->status) . "</span>";
    }
}
