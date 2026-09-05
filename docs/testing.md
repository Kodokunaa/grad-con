# Testing and verification

Use an isolated MySQL database configured in `.env.testing`; never use production.

```bash
composer validate --strict
php artisan optimize:clear
php artisan migrate:fresh --env=testing
php artisan test
php artisan gradconn:check --database
```

`composer verify`, `scripts/verify.ps1`, and `scripts/verify.sh` run repeatable configuration, route, migration-status, and automated-test gates.

Browser acceptance covers all four roles; password change/re-login; POST-only logout modal; cross-role denial; job, application, offer, interview, event, archive, feed, profile, employment, report, and private-upload workflows. For queues, trigger mail with `QUEUE_CONNECTION=database`, run `php artisan queue:work --once --tries=1`, and inspect `queue_jobs` and `failed_jobs`. For SMTP use a designated test mailbox and verify delivery without contacting real alumni. For clean-device testing, clone without `vendor`, `.env`, or caches and repeat installation on Windows and Linux.
