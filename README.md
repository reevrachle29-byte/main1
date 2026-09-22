# QUEUE-MMS

QUEUE-MMS is a Laravel and Inertia/Vue queue management system for campus offices. Students can request and track queue tickets, while staff manage service queues and administrators manage users, offices, services, and reports.

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- A supported Laravel database connection

## Setup

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

For local development:

```bash
composer run dev
```

This starts the Laravel server, queue listener, log viewer, and Vite development server.

## Main workflows

- Guests use the shared Login and Register entry points.
- Public registration always creates a Student account.
- Administrators assign Staff and Administrator roles through User Management.
- Only Staff and Employee accounts can be assigned to an office.
- Only active services in an open office session can receive tickets.
- Queue numbers restart at 100 each day and are allocated through a locked daily sequence.
- Students may hold up to 2 active tickets at a time across the system.
- Ticket categories follow the queue rules: `pwd`, `senior`, and `regular` (with a legacy `priority` label still appearing in some UI text for compatibility).
- Staff can call, complete, skip, cancel, and recall tickets within their assigned office.
- The Call Next rule is PWD first, then Senior, then Regular, with FIFO ordering within each category.

## Testing

```bash
php artisan test
```

The test suite covers authentication, registration, queue generation, cancellation, role redirects, and core queue safety rules.

## Production checklist

1. Configure a production database, cache, queue, mailer, and `APP_URL` in `.env`.
2. Run `php artisan migrate --force`.
3. Build assets with `npm run build`.
4. Run a persistent queue worker with an appropriate retry and timeout policy.
5. Serve the application over HTTPS and keep `APP_DEBUG=false`.
6. Schedule backups and monitor failed jobs and application logs.
