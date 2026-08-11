#!/bin/sh
set -e

cd /var/www/html

echo "==> USIU Hostel — starting on port ${PORT:-8000}"

if [ -z "$APP_KEY" ]; then
  echo "ERROR: APP_KEY is missing. In Render → Environment, click Generate for APP_KEY."
  exit 1
fi

if [ -z "$DB_HOST" ]; then
  echo "ERROR: Database not linked. Add DB_* vars from usiu-hostel-db in Environment."
  exit 1
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations..."
php artisan migrate --force

php artisan storage:link --force 2>/dev/null || true

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  echo "==> Seeding database..."
  php artisan db:seed --force
fi

echo "==> Server ready"
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
