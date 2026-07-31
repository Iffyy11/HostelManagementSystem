# Deploy USIU Hostel Management System to Render

This guide covers deploying the Laravel 11 app to [Render](https://render.com) with **PostgreSQL** (Render’s native free-tier database). MySQL is **not** offered on Render; the app has been adjusted to work with PostgreSQL.

**Repository:** https://github.com/Iffyy11/HostelManagementSystem

---

## What was added/changed locally

| File | Purpose |
|------|---------|
| `render.yaml` | Render Blueprint — one-click web service + PostgreSQL |
| `scripts/render-start.sh` | Production start script (cache, migrate, serve) |
| `Dockerfile` | Production image with `pdo_pgsql`, Composer install |
| `.dockerignore` | Faster Docker builds |
| `app/Http/Controllers/Admin/DashboardController.php` | Cross-database date grouping (MySQL / PostgreSQL / SQLite) |
| `bootstrap/app.php` | Trust Render reverse proxy |
| `app/Providers/AppServiceProvider.php` | Force HTTPS in production |
| `.env.example` | PostgreSQL notes for Render |

**No npm build is required** — the UI uses Bootstrap and Chart.js from CDN, not Vite assets.

---

## Database choice

| Option | Render support | Recommendation |
|--------|----------------|----------------|
| **PostgreSQL** | Native free tier | **Use this** |
| MySQL | Not available on Render | Use external host (PlanetScale, Railway, etc.) if you must |
| SQLite | Ephemeral disk only | Not suitable for production on Render |

The only MySQL-specific code was `DATE_FORMAT()` in the admin revenue chart; it now uses `TO_CHAR` on PostgreSQL and `strftime` on SQLite.

---

## Option A — Blueprint (recommended)

1. Push these changes to GitHub (`main` branch).
2. Go to [Render Dashboard](https://dashboard.render.com) → **New** → **Blueprint**.
3. Connect **GitHub** → select `Iffyy11/HostelManagementSystem`.
4. Render reads `render.yaml` and creates:
   - **Web Service:** `usiu-hostel` (Docker)
   - **PostgreSQL:** `usiu-hostel-db` (free)
5. After the first deploy, open the web service → **Environment** and set:

   | Variable | Example value |
   |----------|---------------|
   | `APP_URL` | `https://usiu-hostel.onrender.com` |
   | `MPESA_CALLBACK_URL` | `https://usiu-hostel.onrender.com/mpesa/callback` |
   | `MPESA_CONSUMER_KEY` | From Safaricom Developer Portal |
   | `MPESA_CONSUMER_SECRET` | From Safaricom Developer Portal |
   | `MPESA_PASSKEY` | From Safaricom Developer Portal |

6. **Redeploy** after setting env vars (Manual Deploy → Deploy latest commit).

---

## Option B — Manual setup

### Step 1: Create PostgreSQL database

1. **New** → **PostgreSQL**
2. Name: `usiu-hostel-db`
3. Database: `hostel_management`
4. User: `hostel`
5. Plan: **Free**
6. Create → copy **Internal Database URL** (or individual host/port/user/password)

### Step 2: Create Web Service

1. **New** → **Web Service**
2. Connect repo: `Iffyy11/HostelManagementSystem`
3. Settings:

   | Setting | Value |
   |---------|-------|
   | **Name** | `usiu-hostel` |
   | **Region** | Frankfurt (or nearest) |
   | **Branch** | `main` |
   | **Runtime** | **Docker** |
   | **Dockerfile Path** | `./Dockerfile` |
   | **Plan** | Free |
   | **Health Check Path** | `/up` |

4. Render builds the Docker image and runs `scripts/render-start.sh` automatically.

> **Native PHP (no Docker):** Not recommended here. You would need a custom buildpack, `composer install`, PHP extensions (`pdo_pgsql`, `mbstring`, `zip`), and a start command. Docker is simpler and matches this repo.

---

## Environment variables (complete list)

Set these on the **Web Service** → **Environment** tab.

### Required

| Variable | Value / notes |
|----------|---------------|
| `APP_NAME` | `USIU Hostel` |
| `APP_ENV` | `production` |
| `APP_KEY` | Generate: `php artisan key:generate --show` locally, or use Render **Generate** |
| `APP_DEBUG` | `false` |
| `APP_URL` | Your Render URL, e.g. `https://usiu-hostel.onrender.com` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | From Render PostgreSQL dashboard |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `hostel_management` |
| `DB_USERNAME` | `hostel` |
| `DB_PASSWORD` | From Render PostgreSQL dashboard |

### Laravel / session / queue

| Variable | Value |
|----------|-------|
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `FILESYSTEM_DISK` | `local` |
| `LOG_CHANNEL` | `stderr` |
| `LOG_LEVEL` | `info` |

### Hostel settings

| Variable | Value |
|----------|-------|
| `HOSTEL_BOOKING_FEE` | `15000` |
| `INSTITUTIONAL_EMAIL_DOMAIN` | `students.usiu.ac.ke` |

### M-Pesa (required for payments)

| Variable | Value |
|----------|-------|
| `MPESA_CONSUMER_KEY` | Safaricom app key |
| `MPESA_CONSUMER_SECRET` | Safaricom app secret |
| `MPESA_PASSKEY` | Lipa na M-Pesa passkey |
| `MPESA_SHORTCODE` | `174379` (sandbox) or production shortcode |
| `MPESA_CALLBACK_URL` | `https://<your-render-url>/mpesa/callback` |
| `MPESA_ENVIRONMENT` | `sandbox` or `production` |
| `MPESA_DEMO_ANY_PHONE` | `false` (use `true` only for sandbox demos) |

### SMS (optional — stubbed in dev)

| Variable | Value |
|----------|-------|
| `AFRICASTALKING_USERNAME` | (optional) |
| `AFRICASTALKING_API_KEY` | (optional) |
| `AFRICASTALKING_FROM` | `USIUHOSTEL` |

---

## Build and start commands

When using **Docker** (this repo’s setup):

| Phase | Command |
|-------|---------|
| **Build** | Handled inside `Dockerfile`: `composer install --no-dev --optimize-autoloader` |
| **Start** | `./scripts/render-start.sh` |

The start script runs:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link --force
php artisan serve --host=0.0.0.0 --port=$PORT
```

---

## Post-deploy: seed demo data

Migrations run automatically on each deploy. **Seeding is one-time** — do not seed on every deploy in production.

1. Open the web service → **Shell** tab.
2. Run:

```bash
php artisan db:seed --force
```

Or set `RUN_SEEDERS=true` once in Environment, redeploy, then remove it.

### Demo accounts (password: `password`)

| Role | Email |
|------|-------|
| Admin | admin@usiu.ac.ke |
| Warden (Block A) | warden.a@usiu.ac.ke |
| Student | student1@students.usiu.ac.ke |

---

## M-Pesa callback URL (HTTPS)

Safaricom requires a **public HTTPS** callback URL.

1. After deploy, your app URL is like: `https://usiu-hostel.onrender.com`
2. Set in Render env:
   ```
   APP_URL=https://usiu-hostel.onrender.com
   MPESA_CALLBACK_URL=https://usiu-hostel.onrender.com/mpesa/callback
   ```
3. Register the same callback URL in the [Safaricom Developer Portal](https://developer.safaricom.co.ke/) for your app.
4. The route `POST /mpesa/callback` is **CSRF-exempt** (already configured).
5. Redeploy after changing env vars so Laravel picks up the new URL.

**Free tier note:** Render free web services spin down after ~15 minutes of inactivity. The first request after idle may take 30–60 seconds. M-Pesa callbacks during spin-up can time out — consider a paid plan or an uptime ping for production demos.

---

## Health check

Laravel 11 exposes `/up` (configured in `bootstrap/app.php`). Render uses this as the health check path in `render.yaml`.

---

## File uploads (maintenance photos)

Render’s filesystem is **ephemeral**. Uploaded files in `storage/app/public` are lost on redeploy unless you:

- Use **Render Disk** (paid), or
- Switch `FILESYSTEM_DISK` to **S3** / compatible object storage.

For a demo deployment, uploads work until the next redeploy.

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 500 on first visit | Check **Logs**; ensure `APP_KEY` is set |
| Database connection error | Verify `DB_*` vars match PostgreSQL dashboard; use **Internal** URL if DB and app are on Render |
| M-Pesa callback not received | Confirm `APP_URL` / `MPESA_CALLBACK_URL` use `https://`; wake the service before testing |
| Admin revenue chart empty | Fixed for PostgreSQL; redeploy latest code |
| Slow cold start | Normal on free tier; upgrade or use cron ping |

### Useful Shell commands

```bash
php artisan migrate:status
php artisan config:clear
php artisan route:list
php artisan tinker
```

---

## Push to GitHub

These files are prepared locally but **not committed**. When ready:

```bash
git add render.yaml Dockerfile .dockerignore scripts/render-start.sh docs/DEPLOY_RENDER.md \
  app/Http/Controllers/Admin/DashboardController.php bootstrap/app.php \
  app/Providers/AppServiceProvider.php .env.example
git commit -m "Add Render deployment config with PostgreSQL support"
git push origin main
```

Then follow **Option A** (Blueprint) or **Option B** (Manual) above.

---

## Summary

- **Database:** Render PostgreSQL (free) — set `DB_CONNECTION=pgsql`
- **Runtime:** Docker via `Dockerfile`
- **Build:** Composer install in Docker build
- **Start:** `scripts/render-start.sh` → migrate + `php artisan serve`
- **Post-deploy:** `php artisan db:seed --force` once via Shell
- **M-Pesa:** Set `MPESA_CALLBACK_URL` to your Render HTTPS URL
