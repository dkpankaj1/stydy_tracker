<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_study_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('plan_date');
            $table->unsignedInteger('target_minutes')->default(0);
            $table->unsignedInteger('completed_minutes')->default(0);
            $table->string('status', 20)->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'plan_date'], 'daily_plans_user_date_unique');
            $table->index(['user_id', 'status', 'plan_date'], 'daily_plans_user_status_date_idx');
        });

        Schema::create('daily_plan_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('daily_study_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedInteger('target_minutes')->default(0);
            $table->unsignedInteger('completed_minutes')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_completed'], 'daily_plan_items_user_completed_idx');
        });

        Schema::create('study_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('daily_plan_item_id')->nullable()->constrained()->nullOnDelete();
            $table->date('session_date');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('paused_seconds')->default(0);
            $table->unsignedInteger('actual_minutes')->default(0);
            $table->string('source', 20)->default('timer');
            $table->boolean('is_active')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'session_date'], 'study_sessions_user_date_idx');
            $table->index(['user_id', 'is_active'], 'study_sessions_user_active_idx');
        });

        Schema::create('progress_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->string('scope_type', 20);
            $table->unsignedBigInteger('scope_id')->default(0);
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->decimal('time_percentage', 5, 2)->default(0);
            $table->decimal('progress_score', 5, 2)->default(0);
            $table->unsignedInteger('estimated_minutes')->default(0);
            $table->unsignedInteger('actual_minutes')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'snapshot_date', 'scope_type', 'scope_id'], 'progress_snapshots_scope_unique');
            $table->index(['user_id', 'snapshot_date'], 'progress_snapshots_user_date_idx');
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 80);
            $table->json('payload')->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();

            $table->index(['user_id', 'occurred_at'], 'activity_logs_user_occurred_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('progress_snapshots');
        Schema::dropIfExists('study_sessions');
        Schema::dropIfExists('daily_plan_items');
        Schema::dropIfExists('daily_study_plans');
    }
};
