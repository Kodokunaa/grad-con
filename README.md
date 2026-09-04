# GradConn

GradConn is a Laravel 13 application migrated from the original procedural PHP portal. It preserves the four roles, current routes and page design, database records, and uploaded files while centralizing authentication and security.

## Requirements

- PHP 8.3 with bcmath, ctype, curl, dom, fileinfo, mbstring, openssl, pdo_mysql, tokenizer, xml, and zip
- MySQL or MariaDB and Composer 2
- A production web root pointing to `public`

## Installation

1. Copy `.env.example` to `.env`. Set `APP_URL`, `APP_TIMEZONE`, and the actual `DB_*` values for the device. XAMPP normally uses port `3306`; use `3307` only when MySQL is configured to listen there.
2. Back up the database and uploads. Run `composer install --no-dev --optimize-autoloader`, `php artisan key:generate`, and `php artisan migrate --force`.
3. Run `php artisan gradconn:hash-passwords --dry-run`, then `php artisan gradconn:hash-passwords`. Values are never printed and existing hashes are skipped.
4. Copy old uploads to `storage/app/private/files/uploads`. Never put resumes in the public directory.
5. Run `php artisan config:cache` and `php artisan view:cache`.
6. Run `php artisan queue:work --tries=3` under a process manager when `QUEUE_CONNECTION=database`.
7. Run `php artisan gradconn:check --database` to verify PHP extensions, the application key, timezone, writable directories, and database access.

For local development, create the configured database first, start MySQL, run `composer install`, and use `php artisan serve`. The application timezone defaults to `Asia/Manila` and can be changed through `APP_TIMEZONE` without editing source files.

## Email

Mail settings live only in `.env`. Rotate the Gmail app password that existed in the original source before setting `MAIL_PASSWORD`. Development defaults to the log mailer, so tests do not contact recipients.

## Tests

Create an isolated MySQL database named `gradconn_test`. Copy `.env.testing.example` to `.env.testing`, generate its key with `php artisan key:generate --env=testing`, import a representative schema/data snapshot, and run `php artisan test`. Never point `.env.testing` at the live application database because the suite exercises write workflows inside transactions. `docs/route-inventory.json` maps every retained page to its controller and Blade view.

## Initial administrator

Set `ADMIN_SEED_NAME`, `ADMIN_SEED_USERNAME`, `ADMIN_SEED_EMAIL`, and a password of at least 12 characters in `.env`, then run `php artisan db:seed`. The idempotent seeder creates or updates that username as an active, approved administrator and hashes its password.

## Data and rollback

Migrations preserve IDs and records. The old source, database dump, and uploads are stored locally in `.migration-backup/`, denied by Apache and ignored by Git because it contains private data. Git retains source history.

To roll back production, stop writes, restore database and upload backups, and point the web server to the prior release. Destructive migration `down()` methods are disabled to avoid silently discarding data.

## Architecture

- `app/Http/Controllers/Pages`: retained feature controllers
- `resources/views/pages`: preserved Blade screens
- `app/Models` and `app/Policies`: Eloquent relationships and authorization
- `app/Support`: compatibility boundary for retained parameterized SQL
- `database/migrations`: repeatable schema
- `storage/app/private/files/uploads`: protected uploads

New work should use Form Requests, Eloquent models, policies, and services instead of adding procedural SQL to generated controllers.
