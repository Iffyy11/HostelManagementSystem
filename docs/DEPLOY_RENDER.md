# Deploy USIU Hostel Management System to Render

Deploy the Laravel 11 app to [Render](https://render.com) with **PostgreSQL** (free tier).

**Repository:** https://github.com/Iffyy11/HostelManagementSystem

---

## Already have `usiu-hostel-db`?

**Do not run Blueprint again** — free tier allows only one PostgreSQL database.

1. Push latest `main` to GitHub.
2. Open existing **usiu-hostel** web service → **Manual Deploy**.
3. Confirm **Environment** links to **usiu-hostel-db** (see variables below).
4. Set `APP_URL` and M-Pesa vars → redeploy.

---

## New setup — Manual (recommended if DB exists)

### Web service environment

| Variable | Value |
|----------|-------|
| `APP_KEY` | `php artisan key:generate --show` (must start with `base64:`) |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://usiu-hostel.onrender.com` (your URL) |
| `DB_CONNECTION` | `pgsql` |
| `DB_SSLMODE` | `require` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | From **usiu-hostel-db** → Internal |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `LOG_CHANNEL` | `stderr` |
| `MPESA_CALLBACK_URL` | `https://your-url.onrender.com/mpesa/callback` |
| `RUN_SEEDERS` | `true` (first deploy only, then remove) |

### Web service settings

| Setting | Value |
|---------|-------|
| Runtime | **Docker** |
| Dockerfile | `./Dockerfile` |
| Health Check | `/up` |
| Plan | Free |

---

## New setup — Blueprint (only if you have no database yet)

1. Push to GitHub → Render → **New** → **Blueprint** → connect repo.
2. Render creates `usiu-hostel` + `usiu-hostel-db` from `render.yaml`.
3. Set `APP_URL`, M-Pesa keys → redeploy.

---

## After deploy

```bash
# In Render Shell (if RUN_SEEDERS was not set):
php artisan db:seed --force
```

**Login:** `admin@usiu.ac.ke` / `password`

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| Exited status 1 | Check logs for `ERROR:` — usually `APP_KEY`, DB vars, or SSL |
| DB connection fail | Use **Internal** DB host; set `DB_SSLMODE=require` |
| 419 on login | `APP_URL` must match your Render URL exactly |
| Slow first load | Free tier cold start (~30–60s after idle) |

See also [Render docs](https://render.com/docs).
