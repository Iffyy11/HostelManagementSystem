#!/usr/bin/env bash
set -e

echo "==> USIU Hostel Management System — Setup"
echo ""

if ! command -v php &>/dev/null; then
    echo "ERROR: PHP is not installed."
    echo "Install PHP 8.2+ and Composer, then re-run this script."
    echo "  macOS: brew install php composer"
    echo "  Or use Docker: docker compose up -d"
    exit 1
fi

if ! command -v composer &>/dev/null; then
    echo "ERROR: Composer is not installed. Visit https://getcomposer.org"
    exit 1
fi

echo "==> Installing PHP dependencies..."
composer install --no-interaction

if [ ! -f .env ]; then
    echo "==> Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

if [ ! -f database/database.sqlite ] && grep -q "DB_CONNECTION=sqlite" .env 2>/dev/null; then
    echo "==> Creating SQLite database..."
    touch database/database.sqlite
fi

echo "==> Running migrations and seeders..."
php artisan migrate --seed --force

echo "==> Linking storage..."
php artisan storage:link --force 2>/dev/null || true

echo ""
echo "Setup complete! Start the server with:"
echo "  php artisan serve"
echo ""
echo "Then visit http://127.0.0.1:8000"
echo ""
echo "Demo login: admin@usiu.ac.ke / password"
