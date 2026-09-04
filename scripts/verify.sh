#!/usr/bin/env bash
set -euo pipefail
composer validate --strict
php artisan optimize:clear
php artisan about
php artisan route:list --except-vendor
php artisan migrate:status
php artisan test
php artisan gradconn:check --database
