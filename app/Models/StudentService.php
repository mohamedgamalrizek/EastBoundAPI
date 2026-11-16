<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentService extends Model
{
    use \App\Models\Concerns\HasAssignedRecordAccess;

    public static function accessParents(): array
    {
        return ['customer_id' => \App\Models\Customer::class];
    }

    protected $fillable = [
        'customer_id', 'student_name', 'university', 'country', 'service_type', 'status',
        'service_fee',
    ];

    protected $casts = [
        'service_fee' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}
