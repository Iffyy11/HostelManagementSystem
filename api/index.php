<?php

/**
 * Vercel serverless entry point (vercel-php runtime).
 *
 * Every request is routed here by vercel.json. The deployed filesystem is
 * read-only except /tmp, so everything Laravel writes at runtime (compiled
 * views, framework caches, logs, uploads) is redirected under /tmp.
 * /tmp is per-instance and ephemeral: keep durable state in the database.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// PHP 8.5 flags a constant in Laravel's own vendor config while it loads, before
// Laravel's error handler exists, so the notice would be printed into the page.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// Always log to Vercel's runtime logs: a log file under /tmp is unreadable there,
// so an inherited LOG_CHANNEL=stack/single would hide every error.
$_ENV['LOG_CHANNEL'] = $_SERVER['LOG_CHANNEL'] = 'stderr';
putenv('LOG_CHANNEL=stderr');

$storage = '/tmp/storage';

foreach ([
    $storage.'/app/public',
    $storage.'/framework/cache/data',
    $storage.'/framework/sessions',
    $storage.'/framework/views',
    $storage.'/logs',
    '/tmp/bootstrap/cache',
] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Laravel reads these from $_ENV / $_SERVER before it touches the filesystem.
foreach ([
    'LARAVEL_STORAGE_PATH' => $storage,
    'APP_SERVICES_CACHE' => '/tmp/bootstrap/cache/services.php',
    'APP_PACKAGES_CACHE' => '/tmp/bootstrap/cache/packages.php',
    'VIEW_COMPILED_PATH' => $storage.'/framework/views',
] as $key => $value) {
    $_ENV[$key] = $_SERVER[$key] = $value;
    putenv("$key=$value");
}

// Vercel env vars are easy to leave blank (e.g. names copied from .env.example with no
// value). Laravel treats "" as a real value, which breaks the app in confusing ways:
// SESSION_DRIVER="" crashes every request ("createDriver(), 0 passed"), and
// SESSION_LIFETIME="" makes cookies expire instantly (login always fails with 419).
// Treat blank variables as unset so the config defaults apply.
$env = fn (string $key) => $_SERVER[$key] ?? $_ENV[$key] ?? (getenv($key) === false ? null : getenv($key));

foreach (array_keys(getenv()) as $key) {
    if ($env($key) === '') {
        unset($_ENV[$key], $_SERVER[$key]);
        putenv($key);
    }
}

// Without DB_CONNECTION Laravel defaults to SQLite and ignores DATABASE_URL, so
// infer the driver from a Postgres connection string (e.g. Neon's DATABASE_URL).
if ($env('DB_CONNECTION') === null && preg_match('#^postgres(ql)?://#', (string) ($env('DB_URL') ?? $env('DATABASE_URL')))) {
    $_ENV['DB_CONNECTION'] = $_SERVER['DB_CONNECTION'] = 'pgsql';
    putenv('DB_CONNECTION=pgsql');
}

require __DIR__.'/../bootstrap/mb_polyfill.php';

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
