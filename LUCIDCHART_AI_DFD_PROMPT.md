# DFD Prompt for Lucidchart or Eraser

Copy the prompt below into Lucidchart AI or Eraser. It asks for a conventional, multi-level DFD based on this system's actual workflows.

```text
Create a professional, clean, easy-to-understand Data Flow Diagram (DFD) for a College Queue Management System. Create eight clearly titled pages: one Level 0 context diagram, one Level 1 diagram, and one Level 2 decomposition page for each of the six Level 1 processes. Make every page suitable for capstone documentation and readable when printed.

Use standard DFD notation consistently:
- External entities are rectangles.
- Processes are numbered circles or rounded process bubbles.
- Data stores use the standard open-ended data-store symbol and keep their D1-D8 identifiers.
- Every arrow is a labeled data flow. Use names of data, not control-flow instructions.
- Do not use decision diamonds, start/end symbols, swimlanes, sequence-diagram lifelines, or generic flowchart styling.
- Keep external entities outside the application processes. Keep data stores separate from processes.
- Balance the diagrams: Level 0 boundary flows must be represented by Level 1, and each Level 1 process's inputs and outputs must be represented in its Level 2 decomposition.
- Use a clear left-to-right layout with generous whitespace. Keep external entities at the outer edges, processes aligned in the center, and data stores close to the processes that use them.
- Route data-flow arrows with straight or right-angle (orthogonal/elbow) connectors. Do not let arrows cross one another, pass through symbols, overlap labels, or obscure arrowheads. If a route would cross another arrow, reposition the entities, processes, or stores and reroute it. Use connector bridges only if crossings are truly unavoidable and the diagram tool supports them.
- Do not overlap symbols, labels, arrows, or page titles. Use consistent spacing, alignment, font sizing, process numbering, store identifiers, and flow-label placement on every page. Prioritize readability over compactness; reposition or redistribute elements rather than crowding them.

SYSTEM AND EXTERNAL ENTITIES
E1 Student or walk-in client
E2 Office staff
E3 Administrator
E4 Public monitor viewer

DATA STORES (use these identifiers and names consistently)
D1 User records: student, staff, and administrator identities and office assignments
D2 Offices and services: office and service names, office links, and active status
D3 Queue sessions: office, operator, status, and session timestamps
D4 Queue requests: ticket number, tracking code, optional user, service, category, status, and request time
D5 Daily queue sequences: last-issued number for each date
D6 Queue transactions: serving staff, call/completion times, and elapsed minutes
D7 Notifications: stored in-app, display, audio, and office notifications
D8 Audit logs: recorded ticket, queue, and session actions

LEVEL 0 - CONTEXT DIAGRAM
Show exactly one process: P0 College Queue Management System. Do not show internal data stores on this diagram.
E1 sends ticket requests and tracking details to P0. P0 returns ticket number, tracking code, receipt details, queue status, position, and estimate to E1.
E2 sends walk-in ticket requests, queue actions, and session controls to P0. P0 returns staff dashboard results and queue/session status to E2.
E3 sends report filters and export requests to P0. P0 returns metrics, report data, and CSV output to E3.
E4 requests the live display from P0. P0 returns called tickets, the next ticket, waiting tickets, and office status to E4.

LEVEL 1 - SYSTEM PROCESSES AND DATA STORES
Decompose P0 into these processes:
P1 1.0 Issue ticket
P2 2.0 Track ticket
P3 3.0 Operate queue
P4 4.0 Manage queue session
P5 5.0 Display live queue
P6 6.0 Report and export

Show these principal flows and connect them to the stores:
E1 -> P1: service selection, category, optional signed-in identity. P1 reads D2, D3, and D4; reads and updates D5; creates a ticket in D4; writes an issuance notification to D7 and an issuance event to D8; returns ticket and receipt data to E1.
E1 -> P2: tracking code or authenticated dashboard request. P2 reads D1, D4, D2, and D6; returns ticket status, position, and estimate to E1.
E2 -> P3: call, recall, complete, skip, or cancel command. P3 reads D1 and D2; reads or updates D4 and D6; writes call notifications to D7 when applicable and queue action events to D8; returns operation results to E2.
E2 -> P4: open, pause, resume, close, or status request. P4 reads D1 and D2; reads or updates D3 and office/service availability in D2; writes session events to D8; returns session status and operation result to E2.
E4 -> P5: live display request. P5 reads D4, D2, and D3; returns called and waiting tickets, next ticket, and office status to E4.
E3 -> P6: office, date-range, and status filters plus export request. P6 reads D4, D2, D1, D6, and D8; returns report metrics, report rows, audit activity, or CSV output to E3.

LEVEL 2 - DECOMPOSE EACH LEVEL 1 PROCESS

P1 ISSUE TICKET
P1.1 Validate request: receives service, category, and optional student identity; reads office/service data from D2, open-session data from D3, and active-ticket data from D4.
P1.2 Allocate ticket number: reads and updates the date's sequence in D5.
P1.3 Save ticket: writes a waiting ticket and tracking code to D4.
P1.4 Record issuance: writes an office notification to D7 and an issuance audit event to D8.
P1.5 Build receipt and estimate: reads queue position from D4 and office/service labels from D2; returns ticket number, tracking code, receipt data, and estimate to E1.

P2 TRACK TICKET
P2.1 Receive lookup request: accepts a tracking code or authenticated dashboard request and uses D1 identity data when the request is for a signed-in student's tickets.
P2.2 Find ticket: reads the ticket and status from D4.
P2.3 Calculate queue position: reads priority and earlier waiting tickets from D4 and historical transaction timing from D6.
P2.4 Prepare tracking result: reads service/office labels from D2 and returns ticket status, position, estimate, or not-found result to E1.

P3 OPERATE QUEUE
P3.1 Authorize staff action: reads staff identity/role from D1 and accessible services from D2.
P3.2 Call next ticket: selects the next priority-ordered waiting ticket in D4, changes it to called, and creates its call/staff record in D6.
P3.3 Recall last ticket: returns the called ticket to waiting in D4 and removes its transaction from D6.
P3.4 Complete ticket: changes ticket status in D4 and writes completion time and elapsed minutes to D6.
P3.5 Skip or cancel ticket: writes the skipped or cancelled status to D4.
P3.6 Notify and audit action: writes call notifications to D7 when applicable and writes queue action events to D8; returns the operation result to E2.

P4 MANAGE QUEUE SESSION
P4.1 Authorize office action: reads staff role/office assignment from D1 and office/services from D2.
P4.2 Read current session: reads session state from D3.
P4.3 Apply session command: writes open, paused, or closed status and timestamps to D3.
P4.4 Update office availability: writes office and service active status to D2.
P4.5 Record and return status: writes a session audit event to D8 and returns session status/result to E2.

P5 DISPLAY LIVE QUEUE
P5.1 Select called tickets: reads called ticket records from D4.
P5.2 Select priority waiting tickets: reads waiting tickets ordered by priority and request time from D4; identifies the next ticket.
P5.3 Determine office status: reads session records from D3.
P5.4 Assemble display data: reads office/service labels from D2 and returns current called tickets, next ticket, waiting list, and office status to E4.

P6 REPORT AND EXPORT
P6.1 Receive filters: accepts office, date range, and status criteria from E3.
P6.2 Retrieve filtered tickets: reads report rows from D4, office/service labels from D2, and user names from D1.
P6.3 Calculate report metrics: reads queue counts from D4, completion/timing data from D6, and office/service grouping from D2.
P6.4 Retrieve recent audit activity: reads audit entries from D8 and actor names from D1.
P6.5 Format report or CSV: returns dashboard metrics, report rows, recent activity, or a downloadable CSV to E3.

BUSINESS RULES TO SHOW AS CALLOUTS, NOT AS FLOWCHART DECISIONS
- Ticket issuance requires an active office and service and an open queue session.
- Signed-in students can have at most two active tickets. Walk-in tickets can have no linked user.
- Queue priority is PWD and senior first, then regular; tickets within a priority group are ordered by request time.
- Ticket numbering starts at 100 and uses the 100-600 range. The current implementation wraps to 100 again if a same-day request advances past 600.
- A newly issued ticket uses a fixed estimate of three minutes per queue position. Tracking lookup uses the average completed transaction wait_minutes value; the current application calculates that value from called_at to completed_at, so it is elapsed service time.
- The public monitor aggregates tickets across offices. Its status is Open if any session is open, otherwise Paused if any session is paused, otherwise Closed.
- Report row and office-breakdown filters use the selected criteria, but headline summary cards and recent audit activity are not all scoped to those filters.
- Notifications are stored in the application; do not draw an external SMS provider.

Maintain a consistent visual layout across all eight pages. Keep external entities at the edges, processes in the center, and relevant data stores nearby. Use consistent numbering and labels across all levels. Ensure every page has clearly visible inputs and outputs without tangled or crossing arrows.
```