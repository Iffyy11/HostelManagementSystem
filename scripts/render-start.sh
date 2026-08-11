#!/bin/sh
set -e

cd /var/www/html

PORT="${PORT:-8000}"
echo "==> USIU Hostel — starting on port ${PORT}"

if [ -z "$APP_KEY" ]; then
  echo "ERROR: APP_KEY is missing. In Render → Environment, set APP_KEY from:"
  echo "  php artisan key:generate --show"
  exit 1
fi

case "$APP_KEY" in
  base64:*) ;;
  *)
    echo "==> Normalizing APP_KEY (adding base64: prefix for Laravel)"
    APP_KEY="base64:${APP_KEY}"
    export APP_KEY
    ;;
esac

if [ -n "$DATABASE_URL" ] && [ -z "$DB_URL" ]; then
  export DB_URL="$DATABASE_URL"
fi

export DB_CONNECTION="${DB_CONNECTION:-pgsql}"

if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi

if [ -n "$APP_URL" ] && [ -z "$MPESA_CALLBACK_URL" ]; then
  export MPESA_CALLBACK_URL="${APP_URL}/mpesa/callback"
fi

if [ -z "$DB_HOST" ] && [ -z "$DATABASE_URL" ] && [ -z "$DB_URL" ]; then
  echo "ERROR: DB_HOST is missing. Link usiu-hostel-db or copy Internal Database credentials."
  exit 1
fi

if [ "${DB_CONNECTION:-pgsql}" != "pgsql" ]; then
  echo "ERROR: DB_CONNECTION must be pgsql on Render (current: ${DB_CONNECTION:-unset})."
  exit 1
fi

echo "==> Checking required PHP extensions..."
for ext in dom xml pdo_pgsql mbstring zip gd bcmath intl; do
  if ! php -m 2>/dev/null | grep -qi "^${ext}$"; then
    echo "ERROR: PHP extension '${ext}' is missing from the Docker image."
    exit 1
  fi
done

echo "==> Waiting for PostgreSQL..."
attempt=0
max_attempts=15
while [ "$attempt" -lt "$max_attempts" ]; do
  attempt=$((attempt + 1))
  if php -r '
$url = getenv("DB_URL") ?: getenv("DATABASE_URL");
if ($url) {
    try {
        new PDO($url, null, null, [PDO::ATTR_TIMEOUT => 5]);
        exit(0);
    } catch (Throwable $e) {
        exit(1);
    }
}
$host = getenv("DB_HOST");
$port = getenv("DB_PORT") ?: "5432";
$db = getenv("DB_DATABASE");
$user = getenv("DB_USERNAME");
$pass = getenv("DB_PASSWORD");
if (! $host || ! $db) {
    exit(1);
}
$sslmode = getenv("DB_SSLMODE") ?: "require";
$dsn = "pgsql:host={$host};port={$port};dbname={$db};sslmode={$sslmode}";
try {
    new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
    exit(0);
} catch (Throwable $e) {
    exit(1);
}
'; then
    echo "==> Database connection OK"
    break
  fi
  if [ "$attempt" -eq "$max_attempts" ]; then
    echo "ERROR: Could not connect to PostgreSQL after ${max_attempts} attempts."
    echo "  DB_HOST=${DB_HOST:-from DATABASE_URL} DB_SSLMODE=${DB_SSLMODE:-require}"
    echo "  Use Internal Database credentials from usiu-hostel-db (same Render region)."
    exit 1
  fi
  echo "  attempt ${attempt}/${max_attempts} — retrying in 3s..."
  sleep 3
done

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

echo "==> Server ready at http://0.0.0.0:${PORT}"
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
