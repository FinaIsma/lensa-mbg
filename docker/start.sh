#!/usr/bin/env bash
set -e

PORT="${PORT:-80}"
echo "Configuring Nginx to listen on port ${PORT}..."
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/http.d/default.conf

echo "Discovering Laravel packages..."
php artisan package:discover --ansi

echo "Creating storage symlink..."
php artisan storage:link --force || true

echo "Optimizing Laravel for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running database migrations..."
php artisan migrate --force

echo "Starting Nginx and PHP-FPM..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
