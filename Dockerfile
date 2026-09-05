FROM node:22-bookworm-slim AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM php:8.3-cli AS vendor
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libcurl4-openssl-dev libicu-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install bcmath curl dom intl mbstring pdo_mysql xml zip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-progress
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-interaction

FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev libicu-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install bcmath curl dom intl mbstring pdo_mysql xml zip opcache \
    && a2enmod rewrite headers expires deflate \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=frontend /app/public/build /var/www/html/public/build
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php-production.ini /usr/local/etc/php/conf.d/99-gradconn-production.ini
COPY docker/start.sh /usr/local/bin/gradconn-start
RUN chmod +x /usr/local/bin/gradconn-start \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000
ENTRYPOINT ["gradconn-start"]
