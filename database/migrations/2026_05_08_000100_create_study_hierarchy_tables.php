<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('estimated_minutes')->default(0);
            $table->unsignedInteger('actual_minutes')->default(0);
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->decimal('time_percentage', 5, 2)->default(0);
            $table->decimal('progress_score', 5, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
            $table->index(['user_id', 'progress_score']);
        });

        Schema::create('lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('order_index')->default(0);
            $table->unsignedInteger('estimated_minutes')->default(0);
            $table->unsignedInteger('actual_minutes')->default(0);
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->decimal('time_percentage', 5, 2)->default(0);
            $table->decimal('progress_score', 5, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['subject_id', 'name']);
            $table->index(['user_id', 'subject_id']);
        });

        Schema::create('topics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('order_index')->default(0);
            $table->unsignedInteger('estimated_minutes')->default(0);
            $table->unsignedInteger('actual_minutes')->default(0);
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->decimal('time_percentage', 5, 2)->default(0);
            $table->decimal('progress_score', 5, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('revision_scheduled_at')->nullable();
            $table->timestamps();

            $table->unique(['lesson_id', 'name']);
            $table->index(['user_id', 'lesson_id']);
            $table->index(['user_id', 'completed_at']);
        });

        Schema::create('checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('order_index')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'topic_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('subjects');
    }
};
