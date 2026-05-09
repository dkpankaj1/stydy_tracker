<?php

namespace App\Providers;

use App\Repositories\Contracts\ChecklistItemRepositoryInterface;
use App\Repositories\Contracts\TopicRepositoryInterface;
use App\Repositories\EloquentChecklistItemRepository;
use App\Repositories\EloquentTopicRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ChecklistItemRepositoryInterface::class, EloquentChecklistItemRepository::class);
        $this->app->bind(TopicRepositoryInterface::class, EloquentTopicRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
