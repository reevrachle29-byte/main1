# System Architecture Prompt for Lucidchart or Eraser

Copy the prompt below into Lucidchart AI or Eraser to create a system architecture diagram for capstone documentation.

```text
Create a clean, professional, easy-to-understand logical system architecture diagram for QUEUE-MMS, a College Queue Management System. This is a software system architecture diagram, not an ERD, DFD, or generic flowchart. Make it suitable for capstone documentation and readable when printed.

Use clearly labeled architecture containers for external users, browser/client, application server, and data persistence. Show the system as a Laravel modular monolith; do not invent microservices or third-party integrations. Add a small legend. Use solid, labeled, right-angle connectors for requests, responses, and data access.

VERIFIED TECHNOLOGY STACK
- Backend application: Laravel 13 on PHP 8.3.
- Web interface: Vue 3 pages integrated through Inertia.js 2.
- Frontend asset tooling: Vite builds the browser assets; treat it as a development/build tool, not a production runtime service.
- Persistence: a configurable relational database. SQLite is the default configuration; MySQL, MariaDB, and PostgreSQL drivers are available. Represent one configured database endpoint, not all database engines at once.
- Authentication and access control: Laravel authentication with authenticated-route and role middleware. Public kiosk and monitor routes are distinct from protected staff and administrator routes.

EXTERNAL USERS AND CLIENTS
Show these people outside the application boundary:
- Student or walk-in client: requests a ticket, receives ticket/receipt details, and tracks a ticket.
- Office staff: operates the assigned queue and opens, pauses, or closes office queue sessions.
- Administrator: manages users, offices, services, and reports, including CSV export.
- Public monitor viewer: views the live queue display.

Show one web client/browser containing the system's Vue/Inertia views, with labeled views for public kiosk/tracking/monitor, student dashboard, staff dashboard, and administrator pages. These are views of the same web application, not separate applications.

APPLICATION SERVER BOUNDARY
Inside one Laravel application server/container, show these related responsibilities as components or labeled modules:
- HTTP routes and middleware: receives web requests, applies authentication, role checks, and request throttling where configured, then routes requests to controllers.
- Authentication and authorization: protects student, staff, and administrator capabilities according to route access.
- Queue and ticket handling: ticket generation, tracking, cancellation, staff call/recall/complete/skip actions, and queue-session operations.
- Administration and reporting: user, office, and service management, report queries, and CSV export.
- Eloquent models / ORM and application logic: reads and writes application records through Laravel's configured database connection.
- Inertia response handling: returns page data to Vue/Inertia views; show CSV downloads as responses to administrator export requests.

DATA PERSISTENCE
Show one relational database connected to the Laravel application. Group or list the main domain data in the database boundary:
- Users, roles, and office assignments.
- Offices and services.
- Queue sessions.
- Queue requests and daily queue-number sequences.
- Queue transactions.
- Stored notifications.
- Audit logs.
Do not draw each database table as a separate architecture service; detailed table attributes belong in the ERD.

SHOW THESE MAIN CONNECTIONS
1. Student/walk-in browser requests go through public routes and middleware to queue/ticket handling; ticket and tracking results return to the browser.
2. Staff browser actions pass through authentication and role middleware to queue/session handling; the application reads and updates queue, transaction, session, and audit data in the database.
3. Administrator browser requests pass through authentication and administrator role checks to administration/reporting; those components read or update the database and return pages or CSV downloads.
4. Public monitor browser requests reach the public monitor route; the application reads queue, office/service, and session data and returns display data.
5. Laravel application logic accesses persistence through Eloquent and the configured database connection. Do not draw direct browser-to-database connections.

ACCURACY AND SCOPE
- Notifications are stored in the application database. Do not show an SMS, email, push-notification provider, message broker, or realtime broadcasting service; none is verified as an active integration.
- Do not show deployment-specific hosting, cloud services, cache servers, object storage, or a separate job worker unless explicitly identified as optional and unconfigured. Do not confuse the ticket queue feature with Laravel's background job queue.
- Do not show Vite as a production server. If included, label it clearly as build/development tooling outside the runtime request path.
- Keep public access and authenticated staff/administrator access visually distinct. Keep all sensitive database access behind the Laravel application boundary.

LAYOUT AND READABILITY
Place users at the far left, the shared browser/client next, the Laravel application boundary in the center, and the relational database on the right. Put related Laravel responsibilities inside the application boundary and group them by responsibility. Use generous whitespace and a clear left-to-right hierarchy. Use straight or right-angle connectors. Do not allow connectors to cross, pass through component boxes, overlap labels, or obscure arrowheads; reposition components and reroute connectors whenever needed. Do not overlap text or shapes. Keep labels short and readable, use consistent fonts and spacing, and prioritize clarity over compactness.
```
