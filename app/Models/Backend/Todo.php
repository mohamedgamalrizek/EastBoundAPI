<?php

namespace App\Models\Backend;

use App\Models\User;
use App\Models\Upload;

use App\Enums\TodoStatus;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Todo extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['title', 'description', 'user_id', 'date',];

    protected $casts = [
        'status' => TodoStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $logAttributes = ['title', 'description', 'user.name', 'date',];

        return LogOptions::defaults()->useLogName('ToDo')->logOnly($logAttributes)->setDescriptionForEvent(fn (string $eventName) => "{$eventName}");
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function upload()
    {
        return $this->belongsTo(Upload::class, 'file_id', 'id');
    }

    public function getTodoStatusAttribute()
    {
        $classes = [
            TodoStatus::PENDING->value      => 'warning',
            TodoStatus::PROCESSING->value   => 'info',
            TodoStatus::COMPLETED->value    => 'success',
        ];

        $class = $classes[$this->status?->value] ?? 'warning';

        return "<span class='bullet-badge  bullet-badge-{$class}'>" . ___("label.{$this->status?->name}") . "</span>";
    }
}
