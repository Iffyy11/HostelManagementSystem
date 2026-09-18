# Deploy USIU Hostel Management System to Vercel

Vercel has no built-in PHP runtime, so this app runs on the community
[`vercel-php`](https://github.com/juicyfx/vercel-php) runtime (PHP 8.5).
Vercel hosts the app only — it needs an **external PostgreSQL database**
(e.g. the existing `usiu-hostel-db` on Render, or Neon / Supabase).

## How it works

| File | Role |
|------|------|
| `vercel.json` | Build command, PHP runtime, routing, function region |
| `api/index.php` | Serverless entry point; sends Laravel's writable paths to `/tmp` |
| `dist/` (generated) | Static assets copied from `public/` (without `index.php`) |

Requests for files in `dist/` (`/css`, `/js`, `/images`, ...) are served statically;
everything else goes to Laravel through `api/index.php`.

The deployed filesystem is read-only except `/tmp`, which is per-instance and
ephemeral. Sessions and cache use the database driver, so they are unaffected.

## Vercel project settings

Leave **Framework Preset** as detected and **Build / Output** settings empty —
`vercel.json` overrides them. Do not set an Output Directory manually.

### Environment variables

| Variable | Value |
|----------|-------|
| `APP_KEY` | `php artisan key:generate --show` (starts with `base64:`) |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://<your-project>.vercel.app` (exact URL, or login gives 419) |
| `LOG_CHANNEL` | `stderr` (shows in Vercel → Logs) |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Render: **usiu-hostel-db → External** credentials |
| `DB_SSLMODE` | `require` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `MPESA_CALLBACK_URL` | `https://<your-project>.vercel.app/mpesa/callback` |

Plus any `MPESA_*` / `AFRICASTALKING_*` values you use (see `.env.example`).

## Migrate the database (once, and after each schema change)

Nothing runs migrations on Vercel. Run them from your machine against the same database:

```bash
DB_CONNECTION=pgsql DB_HOST=<external-host> DB_PORT=5432 \
DB_DATABASE=<db> DB_USERNAME=<user> DB_PASSWORD=<password> DB_SSLMODE=require \
php artisan migrate --force

# First deploy only:
php artisan db:seed --force
```

**Login:** `admin@usiu.ac.ke` / `password` (change it after seeding).

## Known limitations

| Issue | Detail |
|-------|--------|
| Uploaded files | Student maintenance photos are saved to `/tmp` and are lost between requests/instances. Durable uploads need object storage (S3, Vercel Blob). |
| Function region | `vercel.json` pins `fra1` to sit next to a Frankfurt database. Change `regions` if the DB lives elsewhere. |
| Vite manifest | The bundled function does not include `public/build`. Views use `asset()` only; adding `@vite` would need the manifest shipped with the function. |
| Queues / scheduler | No workers or cron run on Vercel. |

## Troubleshooting

| Issue | Fix |
|-------|-----|
| `No Output Directory named "dist"` | `vercel.json` is missing/not committed — its `buildCommand` creates `dist/` |
| 500 on every page | Check Vercel → Logs; usually `APP_KEY` or DB variables |
| DB connection fails | Use the **External** DB host with `DB_SSLMODE=require` |
| 419 on login | `APP_URL` must match the deployed URL exactly |
