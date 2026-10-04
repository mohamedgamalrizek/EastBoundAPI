<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LanguageConfig extends Model
{
    use HasFactory, LogsActivity;
    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('LanguageConfig')
            ->logOnly(['language.name', 'name', 'script', 'native', 'regional',])
            ->setDescriptionForEvent(fn (string $eventName) => "{$eventName}");
    }
}
