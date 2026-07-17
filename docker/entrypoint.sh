#!/bin/bash
set -e

# Railway injects PORT; default to 8080 for local docker run
export PORT="${PORT:-8080}"

# Render nginx config with the correct port
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

cd /var/www/html

# Generate APP_KEY on first boot if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force || true
fi

# Make sure sqlite db file exists (skip if using a mounted volume that already has it)
mkdir -p database
touch database/database.sqlite

# Cache config/routes/views for production, run migrations
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true
php artisan migrate --force

# Fix permissions (in case a mounted volume reset them)
chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache database

exec "$@"
