# QUEUEVITA Project Notes

## Date
2026-08-17

## Important fixes completed

### 1) Administrator login fix
- Root cause: redirects were using `/admin/dashboard`, but the actual route is `/admin`.
- Fix applied in:
  - `app/Http/Controllers/Auth/LoginController.php`
  - `app/Http/Controllers/Auth/RegisterController.php`
  - `app/Providers/FortifyServiceProvider.php`
- Result: admin users now redirect to the valid admin dashboard.

### 2) Role normalization fix
- Root cause: role values were inconsistent (`Administrator`, `admin`, `Employee`, `staff`, etc.) while the database schema allowed a narrower set.
- Fix applied in:
  - `app/Models/User.php`
  - `database/migrations/0001_01_01_000000_create_users_table.php`
- The model now normalizes allowed values to canonical lowercase values such as `admin`, `staff`, `student`.

### 3) Staff dashboard fix
- Root cause: the staff console filtered offices by the current staff user only. If no office was assigned, the dashboard looked empty.
- Fix applied in:
  - `app/Http/Controllers/DashboardController.php`
- The dashboard now shows all active offices when the staff account has no direct office assignment, so the console stays usable.

## System behavior

### Authentication / login flow
- User logs in.
- Laravel checks the role.
- Redirects:
  - admin -> `/admin`
  - employee/staff -> `/dashboard/staff`
  - student -> `/dashboard`

### Student queue flow
- Student opens the dashboard.
- Only offices with an open queue session appear.
- Student chooses a service.
- The app validates the selected service.
- It checks whether the office is open.
- It prevents duplicate active requests for the same service.
- It creates a queue request with:
  - `user_id`
  - `service_id`
  - `queue_number`
  - `tracking_code`
  - `status = waiting`
  - `requested_at`

### Staff queue flow
- Staff user opens a queue session for an office.
- The queue is active.
- Staff can call the next client.
- Ticket status changes from `waiting` to `called` / `serving`.
- Ticket can later be `completed`, `skipped`, or `cancelled`.

## Main files involved
- `app/Http/Controllers/QueueController.php`
- `app/Http/Controllers/QueueSessionController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Models/User.php`
- `app/Providers/FortifyServiceProvider.php`
- `routes/web.php`

## Verification
The targeted auth regression suite passed:
- `php artisan test --filter=AuthenticationTest`
- Result: 5 tests passed

## Notes for next session
- If admin login fails again, check role values and route URLs first.
- If staff dashboard is empty, check whether the staff user has an assigned office or whether the fallback logic is active.
- If student ticket generation fails, check whether the office queue session is open.
