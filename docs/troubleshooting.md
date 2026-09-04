# Troubleshooting

`SQLSTATE[HY000] [2002] actively refused` means no database accepted the configured host/port. Start MySQL, correct `DB_HOST`/`DB_PORT` (XAMPP commonly uses `3306`), then run `php artisan optimize:clear`.

For `Unknown database`, create `DB_DATABASE` and grant access. For missing drivers, enable `pdo_mysql` in both CLI and web `php.ini` files and restart. For 419 errors, verify `@csrf`, cookies, `APP_URL`, and session storage. For redirect loops, verify the account is active, alumni status is approved, and its role is valid.

For uploads, check PHP size limits, storage permissions, MIME type, and `storage/logs/laravel.log`. Resumes must be PDF. For mail, start a worker, inspect `php artisan queue:failed`, verify `MAIL_*`, and retry only after fixing the cause. Use `php artisan optimize:clear` after `.env` changes and never enable `APP_DEBUG` in production.
