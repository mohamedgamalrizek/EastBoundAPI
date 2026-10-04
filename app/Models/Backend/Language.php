<?php

namespace App\Models\Backend;

use App\Enums\Status;
use App\Traits\CommonHelperTrait;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Language extends Model
{
    use HasFactory, LogsActivity, CommonHelperTrait;

    protected $casts = [
        'status' => Status::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Language')
            ->logOnly(['name', 'code', 'icon_class', 'text_direction'])
            ->setDescriptionForEvent(fn (string $eventName) => "{$eventName}");
    }

    public function langConfig()
    {
        return $this->belongsTo(LanguageConfig::class, 'id', 'language_id');
    }
}
