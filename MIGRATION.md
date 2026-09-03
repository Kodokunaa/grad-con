# GradConn Laravel migration

Migration complete. Target: Laravel 13 on PHP 8.3, preserving the existing screens and workflows.

The original application is preserved locally in `.migration-backup/source/` and in Git. This directory contains sensitive source and uploads: it is ignored by Git and denied by Apache. Do not publish it.

An isolated copy of the original MySQL data is in `.migration-runtime/data/`, served on 127.0.0.1:3307 for migration verification. The original XAMPP database remains untouched.

The Laravel application now occupies the repository root. Its production web root must be `public/`; for local development, run `php artisan serve` from this directory. Historic `.php` URLs are retained as Laravel routes.

Completed gates:
- 55 feature pages converted to controllers and Blade views; authentication and protected files use native Laravel controllers.
- Authentication, approval, CSRF, password hashing, and file authorization centralized.
- Configuration secrets removed from Laravel and redacted from retained executable source.
- Database schema captured in migrations with IDs and upload references retained.
- Workflows and access boundaries verified against isolated data.
- Installation and rollback instructions supplied in `README.md`.
