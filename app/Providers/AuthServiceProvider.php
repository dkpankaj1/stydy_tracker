<?php

namespace App\Providers;

use App\Models\ChecklistItem;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Topic;
use App\Policies\ChecklistItemPolicy;
use App\Policies\LessonPolicy;
use App\Policies\SubjectPolicy;
use App\Policies\TopicPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Subject::class => SubjectPolicy::class,
        ChecklistItem::class => ChecklistItemPolicy::class,
        Lesson::class => LessonPolicy::class,
        Topic::class => TopicPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
