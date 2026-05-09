# Study Track - AI Context File

This file is the single source of truth for future AI assistants working in this repository.

## 1. Project Summary
- Project name: Study Track
- App type: Laravel monolith (server-rendered web app)
- Architecture: MVC + Service Layer + Repository Pattern
- UI approach: Blade templates only (no SPA)
- Domain hierarchy: Subject -> Lesson -> Topic -> Checklist Item

## 2. Hard Constraints
- Use Laravel + Blade only
- Use MySQL as primary database
- Use Laravel Queue with database driver
- Use Laravel Notifications, Events, Listeners, Scheduler
- Do not use Redis
- Do not use Flutter, Vue, React, API-first architecture, microservices, or WebSockets

## 3. Current Implementation Status (May 2026)
Implemented:
- Authentication scaffolding with Blade (Breeze)
- Email verification, password reset, profile management
- Foundational study domain migrations
- Core models and relationships for hierarchy/planning/revision/gamification
- Subject CRUD controller + Blade pages
- Checklist toggle endpoint + service and progress recalculation
- Basic policies (ownership checks)
- Repository contracts and Eloquent implementations (starter set)
- Queue database config updated to after_commit = true
- Scheduler hook added in bootstrap

Not fully implemented yet:
- Full Lesson/Topic/Checklist CRUD flows
- Events/listeners/observers for full completion cascade side effects
- Daily planner full use cases
- Study session timer/manual complete workflow
- Revision inbox workflow
- Gamification award engine and notifications UI
- Analytics dashboard aggregation and charts

## 4. Key Files
- Routes:
  - routes/web.php
  - routes/auth.php
- Providers:
  - app/Providers/AppServiceProvider.php
  - app/Providers/AuthServiceProvider.php
  - bootstrap/providers.php
- Services:
  - app/Services/ChecklistService.php
  - app/Services/ProgressService.php
- Repositories:
  - app/Repositories/Contracts/ChecklistItemRepositoryInterface.php
  - app/Repositories/Contracts/TopicRepositoryInterface.php
  - app/Repositories/EloquentChecklistItemRepository.php
  - app/Repositories/EloquentTopicRepository.php
- Policies:
  - app/Policies/SubjectPolicy.php
  - app/Policies/ChecklistItemPolicy.php
- Migrations:
  - database/migrations/2026_05_08_000100_create_study_hierarchy_tables.php
  - database/migrations/2026_05_08_000200_create_planning_and_tracking_tables.php
  - database/migrations/2026_05_08_000300_create_revision_and_gamification_tables.php

## 5. Database Notes
- MySQL identifier limit matters (max 64 chars for index names)
- Explicit short index names were added in planning/tracking migration
- If migration fails mid-way, pending migration may have created some tables already

## 6. Migration Recovery Runbook
If you get "table already exists" while migration is still pending:
1. Clear config cache:
   - php artisan config:clear
2. Check status:
   - php artisan migrate:status
3. Drop partial tables only (do not reset everything unless intentional):
   - Use tinker and Schema::dropIfExists for affected tables
4. Re-run:
   - php artisan migrate

## 7. Environment Expectations
- .env should use MySQL credentials that actually work locally
- Important values:
  - DB_CONNECTION=mysql
  - DB_HOST=127.0.0.1
  - DB_PORT=3306
  - DB_DATABASE=study_track
  - DB_USERNAME=<local user>
  - DB_PASSWORD=<local password>
  - QUEUE_CONNECTION=database
  - SESSION_DRIVER=database
  - CACHE_STORE=database

## 8. Frontend Stack Notes
- Breeze uses Tailwind and Alpine by default
- Bootstrap was added and imported in resources/css/app.css
- app.js includes:
  - ./bootstrap
  - bootstrap package
  - Alpine startup
- resources/js/bootstrap.js sets axios defaults

## 9. Authorization Rules
- Data ownership is strict per user
- Policy checks are required for all model access and mutation
- Continue adding policies for Lesson, Topic, Plan, Session, Revision

## 10. Progress Formula
Use this formula for topic, lesson, subject, and daily rollups:
- completion_percentage = completed_checklist_items / total_checklist_items * 100
- time_percentage = min(actual_minutes / estimated_minutes * 100, 100)
- progress_score = (completion_percentage + time_percentage) / 2

## 11. Recommended Next Build Order
1. Lesson CRUD + Topic CRUD + Checklist item CRUD
2. Event-driven completion cascade side effects
3. Daily Study Plan workflow
4. Study session timer/manual flow
5. Revision scheduling and due list
6. Gamification ledger/badges/streak updates
7. Dashboard analytics queries + snapshot refresh jobs
8. Feature and unit tests for each module

## 12. Test and Build Commands
- php artisan test
- npm run build
- php artisan migrate
- php artisan queue:work

## 13. AI Working Agreement
- Keep controllers thin
- Put business logic in services
- Keep repositories focused and testable
- Use transactions for multi-table write operations
- Queue non-critical side effects
- Prefer additive, modular changes over large rewrites
- Preserve existing app constraints listed above
