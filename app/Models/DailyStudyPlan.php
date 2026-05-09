<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyStudyPlan extends Model
{
    protected $fillable = [
        'user_id',
        'plan_date',
        'target_minutes',
        'completed_minutes',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'plan_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyPlanItem::class);
    }
}
