# ERD Prompt for Lucidchart or Eraser

Copy the prompt below into Lucidchart AI or Eraser to generate a relational ERD for the queue system's core application schema for capstone documentation.

```text
Create a clean, professional, easy-to-understand relational Entity-Relationship Diagram (ERD) for the core database of a College Queue Management System. This is a database ERD, not a data-flow diagram or workflow chart. Make it suitable for capstone documentation and readable when printed.

Use Crow's Foot notation. Show each entity as a table with its real table name and principal attributes. Mark primary keys as PK, foreign keys as FK, unique fields as UNIQUE, and nullable fields as NULL. Show relationship optionality and cardinality at both ends. Do not invent entities, foreign keys, or relationships. Do not merge separate foreign keys just because they connect the same pair of tables.

Include these tables and columns:

users
- user_id PK
- office_id FK NULL -> offices.office_id
- name
- email UNIQUE
- password
- role (string, default student; current values include admin, administrator, staff, employee, student)
- contact NULL
- phone_number NULL
- email_verified_at NULL
- created_at
- updated_at

offices
- office_id PK
- user_id FK NULL -> users.user_id
- name
- is_active
- window_count (unsigned small integer; default 1)
- created_at
- updated_at

services
- service_id PK
- office_id FK NOT NULL -> offices.office_id
- user_id FK NULL -> users.user_id
- service_name
- is_active
- created_at
- updated_at

queue_requests
- request_id PK
- user_id FK NULL -> users.user_id
- service_id FK NOT NULL -> services.service_id
- queue_number
- tracking_code UNIQUE
- status (string; default waiting)
- category (pwd, senior, regular; default regular)
- requested_at NULL

queue_transactions
- transaction_id PK
- request_id FK NOT NULL, UNIQUE -> queue_requests.request_id
- served_by FK NULL -> users.user_id
- called_at NULL
- completed_at NULL
- wait_minutes NULL
- counter_number NULL (unsigned small integer)

notifications
- notif_id PK
- transaction_id FK NULL -> queue_transactions.transaction_id
- user_id FK NULL -> users.user_id
- type (string; default display)
- message
- sent_at

queue_sessions
- session_id PK
- office_id FK NOT NULL -> offices.office_id
- user_id FK NOT NULL -> users.user_id
- status (open, paused, closed; default open)
- opened_at NULL
- closed_at NULL
- created_at
- updated_at

audit_logs
- log_id PK
- user_id FK NULL -> users.user_id
- action
- description NULL
- metadata NULL (JSON)
- created_at
- updated_at

queue_sequences
- sequence_date PK
- last_number (unsigned integer; default 99)

Draw these relationships with Crow's Foot cardinalities:
1. One office has zero or many services; each service belongs to exactly one office.
2. A user may be assigned to zero or one office through users.office_id; an office may have zero or many assigned users.
3. An office may reference zero or one user through offices.user_id; a user may be referenced by zero or many offices. Keep this distinct from users.office_id.
4. A service may reference zero or one user through services.user_id; a user may be referenced by zero or many services.
5. A user may have zero or many queue requests; each queue request may belong to zero or one user. A null user_id represents a walk-in ticket.
6. One service has zero or many queue requests; each queue request belongs to exactly one service.
7. A queue request has zero or one queue transaction; every queue transaction belongs to exactly one queue request. The unique constraint on queue_transactions.request_id enforces at most one transaction per request.
8. A user may serve zero or many queue transactions; each transaction may reference zero or one serving user.
9. An office has zero or many queue sessions; each session belongs to exactly one office.
10. A user has zero or many queue sessions; each session belongs to exactly one user.
11. A user may receive zero or many notifications; each notification may reference zero or one user.
12. A queue transaction may have zero or many notifications; each notification may reference zero or one transaction.
13. A user may be associated with zero or many audit logs; each audit log may reference zero or one user.

Show queue_sequences as an independent table with no relationship lines because it stores one daily sequence per sequence_date and has no foreign keys.

Preserve the database delete behavior in relationship annotations where supported:
- Deleting an office cascades to its services and queue sessions.
- Deleting a service cascades to its queue requests.
- Deleting a queue request cascades to its queue transaction; deleting a transaction cascades to its notifications.
- Deleting a user sets the related nullable user foreign keys to NULL, except queue_sessions.user_id, which cascades with the session.
- Deleting an office sets users.office_id to NULL. Deleting a user referenced by offices.user_id or services.user_id sets those foreign keys to NULL.

Arrange the diagram with offices and users near the top, services and queue_sessions below them, queue_requests and queue_transactions in the center, notifications and audit_logs nearby, and the independent queue_sequences table apart from related tables. Build a clean, balanced layout with generous whitespace between tables. Use straight or right-angle (orthogonal/elbow) connectors. Do not let relationship lines cross one another, pass through tables, overlap labels, or obscure cardinality markers. If a connector would cross another, reposition the tables or reroute the line until the relationships are clear. Do not overlap tables, labels, or connectors. Keep relationship endpoints and Crow's Foot markers visible. Prioritize readability over compactness. Exclude framework support tables such as password reset tokens and browser sessions; include only the listed core application tables.
```