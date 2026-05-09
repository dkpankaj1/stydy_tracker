<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    protected $fillable = [
        'user_id',
        'subject_id',
        'lesson_id',
        'name',
        'order_index',
        'estimated_minutes',
        'actual_minutes',
        'completion_percentage',
        'time_percentage',
        'progress_score',
        'completed_at',
        'revision_scheduled_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'revision_scheduled_at' => 'datetime',
            'completion_percentage' => 'decimal:2',
            'time_percentage' => 'decimal:2',
            'progress_score' => 'decimal:2',
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

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Revision::class);
    }
}
