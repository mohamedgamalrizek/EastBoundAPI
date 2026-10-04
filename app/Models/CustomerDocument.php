<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDocument extends Model
{
    protected $fillable = [
        'customer_id', 'title', 'type', 'file_label', 'uploaded_on', 'status',
    ];

    protected $casts = [
        'uploaded_on' => 'date',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}
