# Project Notes — Hostel Management System

Notes for Chapter 5 (Implementation) and Chapter 6 (Testing & Evaluation).

## What Was Built

Six phases per the system design specification:

1. **Scaffold & Database** — Migrations, models, relationships, seeders (3 blocks, 21 rooms, 15 students, 2 wardens, 2 caretakers, 1 admin)
2. **Authentication & RBAC** — Student self-registration, staff admin-created, four role dashboards
3. **Booking Workflow** — Availability check → warden approval → M-Pesa STK Push → confirmation with occupancy update
4. **Maintenance** — Student fault reporting with optional photo, caretaker workflow, status notifications
5. **Admin & Reports** — User CRUD, occupancy/revenue/maintenance reports, Chart.js dashboards, CSV/PDF export
6. **Non-Functional** — Mobile-responsive UI, security review (see SECURITY.md), error handling on external APIs

## Architecture

- **Pattern:** MVC with Service layer for complex business logic
- **BookingService:** Handles transactions, locking, notifications
- **MpesaService:** OAuth token caching, STK Push initiation
- **NotificationService:** Email via Laravel Mail + SMS stub with logging to `notifications_log`

## Database Schema

Matches Chapter 4 ERD:

- `users` ↔ Spatie roles (student, warden, caretaker, admin)
- `students`, `wardens` — role-specific profile tables
- `rooms` — block, capacity, occupancy, status
- `bookings` — status workflow enum
- `payments` — 1:1 with confirmed bookings
- `maintenance_requests` — category/status enums, optional caretaker assignment
- `notifications_log` — audit trail for email/SMS

## Testing Checklist

### Manual Test Scenarios

- [ ] Student registers with `@students.usiu.ac.ke` email
- [ ] Student browses rooms and submits booking
- [ ] Two students cannot book the last bed simultaneously
- [ ] Warden sees pending booking in their block only
- [ ] Warden approves → student sees "Pay Now"
- [ ] Warden rejects with reason → student notified
- [ ] M-Pesa STK Push initiates (sandbox)
- [ ] Callback confirms payment → occupancy increments
- [ ] Student submits maintenance request for confirmed room
- [ ] Caretaker updates status → student sees change (30s polling)
- [ ] Admin generates reports for date range
- [ ] Cross-role URL access redirects correctly

### Demo Without M-Pesa

Seed data includes a confirmed booking with receipt `QGH123ABC`. Payment flow can be demonstrated via code walkthrough; live STK Push requires sandbox credentials.

## Performance Notes

- Room listing queries are unpaginated (~21 rooms) — acceptable for demo scale
- Production would add: pagination on reports, Redis cache for room availability, queue workers for notifications
- Chart.js loaded via CDN on admin dashboard only

## Usability

- Tested at 375px viewport width (mobile)
- Sidebar collapses with hamburger toggle on tablet/mobile
- Bootstrap 5 responsive grid throughout
- Plus Jakarta Sans typography, teal brand palette

## Environment Variables

See `.env.example` for full list. Critical:

| Variable | Purpose |
|----------|---------|
| `HOSTEL_BOOKING_FEE` | Configurable booking amount (default 15000 KES) |
| `INSTITUTIONAL_EMAIL_DOMAIN` | Student registration email validation |
| `MPESA_*` | Daraja API credentials |
| `DB_*` | Database connection |

## Known Issues

- PHP/Composer must be installed locally (or use Docker)
- M-Pesa callback requires publicly accessible URL (ngrok for local dev)
- Email sends to log driver by default (`MAIL_MAILER=log`)

## Future Enhancements

- Waitlist for full rooms
- Semester-based booking periods
- SMS via Africa's Talking live integration
- Mobile app (API layer on existing services)
