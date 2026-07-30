# USIU Hostel Management System

A web-based hostel management platform for USIU-Africa — built as a final-year project. Students can browse rooms, submit booking requests, pay via M-Pesa STK Push, and report maintenance issues. Wardens, caretakers, and admins each have role-specific dashboards.

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Database:** MySQL (SQLite supported for quick local demo)
- **Frontend:** Blade + Bootstrap 5 + Chart.js
- **Auth:** Laravel Breeze-style auth + Spatie `laravel-permission`
- **Payments:** Safaricom M-Pesa Daraja API (Sandbox)

## Features

| Role | Capabilities |
|------|-------------|
| **Student** | Register, browse rooms, book, pay via M-Pesa, report maintenance |
| **Warden** | Approve/reject bookings, view block occupancy, assign maintenance |
| **Caretaker** | Manage work orders, update status, add resolution notes |
| **Admin** | User management, occupancy/revenue/maintenance reports, CSV/PDF export |

## Quick Start

### Prerequisites

- PHP 8.2+ with extensions: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `curl`
- Composer
- MySQL 8+ (or use SQLite for demo)

### Installation

```bash
cd hostel-management-system

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env (MySQL example):
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=hostel_management
# DB_USERNAME=root
# DB_PASSWORD=

# Or use SQLite for quick demo:
# DB_CONNECTION=sqlite
# Then: touch database/database.sqlite

# Run migrations and seed demo data
php artisan migrate --seed

# Link storage for maintenance photo uploads
php artisan storage:link

# Start the server
php artisan serve
```

Visit **http://127.0.0.1:8000**

### Demo Accounts (password: `password`)

| Role | Email |
|------|-------|
| Admin | admin@usiu.ac.ke |
| Warden (Block A) | warden.a@usiu.ac.ke |
| Warden (Block B) | warden.b@usiu.ac.ke |
| Caretaker | caretaker1@usiu.ac.ke |
| Student | student1@students.usiu.ac.ke |

## M-Pesa Sandbox Setup (Phase 3)

1. Register at [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
2. Create a sandbox app and get Consumer Key, Consumer Secret, Passkey
3. Add to `.env`:

```env
MPESA_CONSUMER_KEY=your_key
MPESA_CONSUMER_SECRET=your_secret
MPESA_PASSKEY=your_passkey
MPESA_SHORTCODE=174379
MPESA_CALLBACK_URL=https://your-ngrok-url.ngrok.io/mpesa/callback
MPESA_ENVIRONMENT=sandbox
```

For local testing, use [ngrok](https://ngrok.com/) to expose your callback URL:

```bash
ngrok http 8000
```

## Docker (Alternative)

If PHP is not installed locally:

```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

App runs at **http://localhost:8000**

## Project Structure

```
app/
├── Http/Controllers/     # Role-based controllers
├── Http/Middleware/      # EnsureUserHasRole
├── Http/Requests/        # Form validation
├── Models/               # Eloquent models + relationships
└── Services/             # BookingService, MpesaService, NotificationService
database/
├── migrations/           # Schema (matches Chapter 4 ERD)
└── seeders/              # Roles + demo data
resources/views/          # Blade templates (Bootstrap 5 UI)
public/css/hostel.css     # Custom design system
```

## Booking Workflow

1. Student browses available rooms (live from DB)
2. Student submits booking → status `pending`
3. Warden approves → status `approved_by_warden`
4. Student pays via M-Pesa STK Push → status `confirmed`, occupancy updated
5. On payment failure → status reverts to `awaiting_payment`

Race conditions on the last bed are handled with `lockForUpdate()` inside a DB transaction.

## License

MIT — academic project use.
