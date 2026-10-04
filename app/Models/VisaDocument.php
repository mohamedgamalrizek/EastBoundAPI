<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisaDocument extends Model
{
    protected $fillable = [
        'visa_application_id',
        'document_type',
        'file_name',
        'file_path',
        'mime_type',
        'status',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function visaApplication()
    {
        return $this->belongsTo(VisaApplication::class);
    }
}
