#!/bin/bash
set -e

echo "Waiting for database..."
until php artisan migrate --force 2>/dev/null; do
  echo "DB not ready, retrying in 5s..."
  sleep 5
done

echo "Running migrations..."
php artisan migrate --force

echo "Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting PHP-FPM..."
exec php-fpm          # ← must be php-fpm, not php artisan serve
