# syntax=docker/dockerfile:1

## ============================
## Stage 1: Build frontend (Vite/React/Tailwind)
## ============================
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

## ============================
## Stage 2: PHP dependencies (Composer)
## ============================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev

## ============================
## Stage 3: Runtime (PHP-FPM + Nginx)
## ============================
FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    sqlite-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    icu-dev \
    bash \
    curl \
    gettext \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_sqlite \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        bcmath \
        intl \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy vendor (from composer stage) and built frontend assets (from node stage)
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Ensure sqlite database file exists (created only if missing; Railway volume can override)
RUN mkdir -p database && touch database/database.sqlite \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache database

# Nginx config template (port is substituted at runtime from $PORT)
COPY docker/nginx.conf.template /etc/nginx/templates/default.conf.template
# Supervisor config (runs php-fpm + nginx together)
COPY docker/supervisord.conf /etc/supervisord.conf
# Entrypoint (runs migrations/cache on boot)
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_FPM_LISTEN=9000

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
