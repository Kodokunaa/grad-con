# Troubleshooting

`SQLSTATE[HY000] [2002] actively refused` means no database accepted the configured host/port. Start MySQL, correct `DB_HOST`/`DB_PORT` (XAMPP commonly uses `3306`), then run `php artisan optimize:clear`.

For `Unknown database`, create `DB_DATABASE` and grant access. For missing drivers, enable `pdo_mysql` in both CLI and web `php.ini` files and restart. For 419 errors, verify `@csrf`, cookies, `APP_URL`, and session storage. For redirect loops, verify the account is active, alumni status is approved, and its role is valid.

For uploads, check PHP size limits, storage permissions, MIME type, and `storage/logs/laravel.log`. Resumes must be PDF. For mail, start a worker, inspect `php artisan queue:failed`, verify `MAIL_*`, and retry only after fixing the cause. Use `php artisan optimize:clear` after `.env` changes and never enable `APP_DEBUG` in production.
# Email is not received

For Resend, confirm `MAIL_MAILER=resend`, a valid `RESEND_API_KEY`, and a `MAIL_FROM_ADDRESS` at the verified domain. Resend's testing domain can only send to the Resend account owner's address. Run `php artisan gradconn:check --mail`, clear cached configuration, and use `php artisan gradconn:test-mail recipient@example.com` to expose the provider's exact response.

Run `php artisan gradconn:check --mail`. `MAIL_MAILER=log` only writes messages to `storage/logs/laravel.log`; it never contacts Gmail. For Gmail, set `MAIL_MAILER=smtp`, `MAIL_SCHEME=tls`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_USERNAME` to the sender Gmail address, `MAIL_PASSWORD` to a Google App Password, and `MAIL_FROM_ADDRESS` to the same sender. Then run `php artisan optimize:clear` and `php artisan gradconn:test-mail recipient@gmail.com`.

For simple local testing use `QUEUE_CONNECTION=sync`, which sends during the web request. Production should use `QUEUE_CONNECTION=database` with a continuously running `php artisan queue:work` process.
