# Study Track

Study Track is a Laravel monolith for planning and tracking study progress.

The learning hierarchy is:

Subject -> Lesson -> Topic -> Checklist Item

## Tech Stack

- Laravel 13 (Blade server-rendered UI)
- MySQL
- Laravel Queue (database driver)
- Laravel Notifications, Events, Listeners, Scheduler (in-progress usage)
- Bootstrap + Tailwind (Breeze base)

## Current Features

- Authentication via Breeze
- Email verification, password reset, profile management
- Subject CRUD
- Lesson, Topic, and Checklist item CRUD flows
- Checklist completion toggle with progress recalculation
- Subject structure CSV import
- Subject report CSV export
- Subject detail page with Bootstrap accordion for lessons
	- Per-lesson expand/collapse
	- Open All / Collapse All controls
- Ownership-based authorization policies
- Repository contracts and Eloquent implementations (starter set)

## Progress Formula

Used for topic, lesson, subject, and daily rollups:

- completion_percentage = completed_checklist_items / total_checklist_items * 100
- time_percentage = min(actual_minutes / estimated_minutes * 100, 100)
- progress_score = (completion_percentage + time_percentage) / 2

## Project Structure

- Routes: `routes/web.php`, `routes/auth.php`
- Controllers: `app/Http/Controllers`
- Form Requests: `app/Http/Requests`
- Services: `app/Services`
- Repositories: `app/Repositories`
- Policies: `app/Policies`
- Models: `app/Models`
- Views: `resources/views`

## Setup

1. Install dependencies

```bash
composer install
npm install
```

2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

3. Update `.env` for MySQL (example)

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=study_track
DB_USERNAME=your_user
DB_PASSWORD=your_password

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
```

4. Run database setup

```bash
php artisan migrate
```

5. Start development servers

```bash
php artisan serve
npm run dev
```

## Useful Commands

- Run tests: `php artisan test`
- Build frontend assets: `npm run build`
- Run queue worker: `php artisan queue:work`
- Check migration status: `php artisan migrate:status`

## Migration Recovery Note

If migration fails with "table already exists" while a migration is still pending:

1. `php artisan config:clear`
2. `php artisan migrate:status`
3. Drop only partial tables that were created
4. Re-run: `php artisan migrate`

## Development Constraints

- Keep Laravel + Blade architecture (no SPA/API-first rewrite)
- Use MySQL as primary DB
- Keep controllers thin
- Put business logic in services
- Use transactions for multi-table writes
- Queue non-critical side effects

## License

This project is open-sourced under the MIT license.
