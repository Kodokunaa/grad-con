#!/bin/sh
set -eu

port="${PORT:-10000}"
sed -ri "s/^Listen .*/Listen ${port}/" /etc/apache2/ports.conf
sed -ri "s/__PORT__/${port}/g" /etc/apache2/sites-available/000-default.conf

mkdir -p storage/app/private storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache storage/certs
chown -R www-data:www-data storage bootstrap/cache

if [ -n "${AIVEN_CA_BASE64:-}" ]; then
    printf '%s' "$AIVEN_CA_BASE64" | base64 -d > storage/certs/aiven-ca.pem
    chmod 600 storage/certs/aiven-ca.pem
fi

php artisan config:clear
php artisan migrate --force
php artisan gradconn:check --database --mail
php artisan optimize

exec apache2-foreground
