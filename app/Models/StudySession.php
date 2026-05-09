<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudySession extends Model
{
    protected $fillable = [
        'user_id',
        'subject_id',
        'lesson_id',
        'topic_id',
        'daily_plan_item_id',
        'session_date',
        'started_at',
        'ended_at',
        'paused_seconds',
        'actual_minutes',
        'source',
        'is_active',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function dailyPlanItem(): BelongsTo
    {
        return $this->belongsTo(DailyPlanItem::class);
    }
}
