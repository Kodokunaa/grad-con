# Installation

Use PHP 8.3+, Composer 2, and MySQL 8 or compatible MariaDB. Enable `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, and `zip`. Make `storage` and `bootstrap/cache` writable.

## Windows/XAMPP

Copy `.env.example` to `.env`, configure `DB_*`, create the database, and run:

```powershell
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
powershell -ExecutionPolicy Bypass -File scripts/verify.ps1
php artisan serve
```

## Linux

```bash
cp .env.example .env
composer install --no-interaction
php artisan key:generate
php artisan migrate
php artisan db:seed
chmod -R ug+rw storage bootstrap/cache
bash scripts/verify.sh
```

Point Apache or Nginx to `public`, never the repository root. Configure `APP_URL`, timezone, database, mail, queue, and session values in `.env`. Set `ADMIN_SEED_NAME`, `ADMIN_SEED_USERNAME`, `ADMIN_SEED_EMAIL`, and a 12+ character `ADMIN_SEED_PASSWORD`, then run `php artisan db:seed --class=AdminSeeder` for the first administrator.

For outbound email without a domain, create and verify an individual sender in Brevo. Set `MAIL_MAILER=smtp`, `MAIL_HOST=smtp-relay.brevo.com`, `MAIL_PORT=587`, `MAIL_SCHEME=null`, `MAIL_USERNAME` to the Brevo SMTP login, `MAIL_PASSWORD` to a Brevo SMTP key, and `MAIL_FROM_ADDRESS` to the verified sender. Run `php artisan gradconn:check --mail` before testing account workflows.
