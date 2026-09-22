# ✅ Category-Based Priority Queue System

## What the system currently does

### 1. Queue categories
The app uses the real category values stored in the database:
- `pwd` for Persons with Disabilities
- `senior` for senior citizens
- `regular` for the general public

> Some legacy UI text still says “Priority,” but the live data model is category-based and the canonical values are `pwd`, `senior`, and `regular`.

### 2. Ticket limit
Authenticated students are limited to 2 active tickets at a time.
- This is enforced in the queue generation flow.
- The system does not allow unlimited tickets per student in the current codebase.

### 3. Call Next ordering
When staff click “Call Next,” the app sorts waiting tickets by:
1. PWD first
2. Senior second
3. Regular third
4. FIFO by `requested_at` within each category

---

## Queue priority rule

```text
PWD > Senior > Regular
Within each category: earliest requested_at first
```

### Example
```text
WAITING QUEUE:
#42 Regular - 14:00
#43 Senior  - 14:05
#44 PWD     - 14:10
#45 Regular - 14:15
#46 Senior  - 14:20

Call Next -> #44 (PWD) is served first
```

---

## Visual category flow

### Kiosk (Public)
- Category selection appears when a student selects a service.
- The queue uses category values and displays the selection in the flow.
- The UI may show a “Priority” label for the combined category group, but the stored values remain `pwd`, `senior`, and `regular`.

### Staff Dashboard
- Waiting queue includes category information.
- Staff can create walk-in tickets with a category selection.

### Monitor / notifications
- Ticket notifications and queue ordering reflect the category priority rule.

---

## Database model

### Column
```sql
ALTER TABLE queue_requests
ADD COLUMN category ENUM('pwd', 'senior', 'regular') DEFAULT 'regular';
```

### Index
```sql
CREATE INDEX idx_category_status_requested
ON queue_requests(category, status, requested_at);
```

---

## Relevant files

### Backend
- `app/Models/QueueRequest.php` — stores the category field
- `app/Http/Controllers/QueueController.php` — validates and assigns category values
- `app/Http/Controllers/DashboardController.php` — calculates priority ordering for queue position and “Call Next”
- `database/migrations/2026_08_18_000002_add_category_to_queue_requests.php` — migration for the category column

### Frontend
- `resources/js/Pages/Queue/Kiosk.vue` — category selection UI
- `resources/js/Pages/Dashboard/Staff.vue` — walk-in category form and queue display

---

## How this matches the real app

### Supported values
```php
'category' => 'required|in:priority,pwd,senior,regular'
```

The current app includes a legacy compatibility value of `priority`, but the database and queue logic are built around:
- `pwd`
- `senior`
- `regular`

### Active ticket rule
```php
if ($activeTicketCount >= 2) {
    return redirect()->back()->with('error', 'You can only have up to 2 active tickets at a time.');
}
```

This is the current behavior and should be reflected in documentation.

---

## Testing notes

1. Create several tickets in one service with different categories.
2. Staff clicks “Call Next.”
3. The app should call tickets in this order:
   - PWD
   - Senior
   - Regular
4. Within each group, earlier `requested_at` values are served first.

---

## Summary

This system is a category-based priority queue, not an unlimited ticket model. The real implementation is:
- categories are `pwd`, `senior`, `regular`
- active student tickets are capped at 2
- `Call Next` prioritizes PWD, then Senior, then Regular

That is the behavior represented in the current application codebase.
