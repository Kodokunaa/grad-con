# GradConn

GradConn is a Laravel 13 alumni, employer, and campus-officer portal. Authentication, authorization, validation, private uploads, queued mail, jobs, applications, offers, interviews, events, social reactions, comments, and reports use Laravel controllers, models, policies, Form Requests, services, and Blade views.

## Start locally

```powershell
Copy-Item .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Set the real `DB_*` values before migrating. Standard XAMPP MySQL uses port `3306`; use `3307` only when MySQL listens there. Run `php artisan queue:work --tries=3` in another terminal when `QUEUE_CONNECTION=database`.

- [Installation](docs/installation.md)
- [Existing-database upgrade](docs/upgrade.md)
- [Deployment](docs/deployment.md)
- [Render deployment](docs/render-deployment.md)
- [Backup and restore](docs/backup-restore.md)
- [Testing](docs/testing.md)
- [Troubleshooting](docs/troubleshooting.md)

Run `php artisan gradconn:check --database` and `composer verify` before deployment. Point the production web root to `public/`. Private files remain under `storage/app/private/files/uploads` and are served only through authorized routes.
