# Existing-database upgrade

1. Stop writes and queue workers.
2. Back up the database, private uploads, and `.env` with one timestamp.
3. Deploy beside the old release and copy `.env`; do not copy caches.
4. Copy legacy uploads to `storage/app/private/files/uploads` without renaming files.
5. Run `composer install --no-dev --optimize-autoloader` and `php artisan gradconn:check --database`.
6. Review `php artisan migrate:status`, then run `php artisan migrate --force`.
7. Run `php artisan gradconn:hash-passwords --dry-run`, review the count, then run it normally.
8. Run `composer verify`, start the worker, and complete the role checks in `docs/testing.md`.
9. Switch the web root only after acceptance passes.

The preserved baseline is additive for existing databases. Production rollback restores the matching pre-upgrade database and uploads rather than destructively reversing the baseline.
