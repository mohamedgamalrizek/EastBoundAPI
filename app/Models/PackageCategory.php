<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageCategory extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'status',
    ];

    public function packages() { return $this->hasMany(Package::class, 'category_id'); }


public function getMyStatusAttribute()
{
    if ($this->status == 'active') {
        return '<span class="badge badge-success">Active</span>';
    }

    return '<span class="badge badge-danger">Inactive</span>';
}




}

