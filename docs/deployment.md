# Deployment

Use a separate release directory and stable storage. Point the document root to `public/`. Set `APP_ENV=production`, `APP_DEBUG=false`, the HTTPS `APP_URL`, secure cookies, and production database, mail, queue, and session credentials.

```bash
composer install --no-dev --classmap-authoritative
php artisan gradconn:check --database
php artisan migrate --force
php artisan optimize
composer verify
```

Run `php artisan queue:work --sleep=3 --tries=3 --max-time=3600` under Supervisor, systemd, or a Windows service. Run `php artisan queue:restart` after deployment. Verify all four roles, logout, one mutation, a private PDF download, queued mail, and `storage/logs/laravel.log`. Retain the previous release and matching backup until acceptance completes.
# Resend email delivery

Verify a sending domain in Resend and create a sending API key. Configure `MAIL_MAILER=resend`, `RESEND_API_KEY`, `MAIL_FROM_ADDRESS=notifications@your-verified-domain`, and `MAIL_FROM_NAME=GradConn`. Never commit the API key. Validate configuration with `php artisan gradconn:check --mail`, then perform a live test with `php artisan gradconn:test-mail recipient@example.com`.

# Brevo email delivery

Brevo is the default for installations without a domain. Verify an individual sender in Brevo, generate a standard SMTP key, and copy the separate SMTP login from Settings > SMTP & API. Configure `MAIL_MAILER=smtp`, `MAIL_SCHEME=null`, `MAIL_HOST=smtp-relay.brevo.com`, `MAIL_PORT=587`, `MAIL_USERNAME`, `MAIL_PASSWORD`, and the verified `MAIL_FROM_ADDRESS`. Use the SMTP key as the password; an API key, Brevo account password, or sender Gmail password will not work.
