# Queue Management System Data Flow Diagrams

This document uses the common capstone convention in which **Level 0** is the context diagram. The system is decomposed through **Level 2**, where each major process is detailed into its principal sub-processes, data stores, inputs, and outputs. Level 3 is not included because it would mostly describe controller-level validation and implementation logic rather than additional system functions.

## Level 0: Context Diagram

The system is represented as one process. External entities exchange data with it; internal data stores are intentionally omitted at this level.

```mermaid
flowchart LR
    Student[Student or walk-in client]
    Staff[Office staff]
    Admin[Administrator]
    Viewer[Public monitor viewer]
    System((0. College Queue Management System))

    Student -->|Ticket request or tracking details| System
    System -->|Ticket receipt, queue status, position, estimate| Student
    Staff -->|Walk-in ticket, queue action, session control| System
    System -->|Staff dashboard and operation results| Staff
    Admin -->|Report filters and export request| System
    System -->|Metrics, report data, CSV file| Admin
    Viewer -->|Live display request| System
    System -->|Called ticket, next ticket, office status| Viewer
```

## Level 1: Major Processes

Level 1 decomposes process 0 into the six major functions. The store identifiers are reused in every Level 2 diagram.

```mermaid
flowchart LR
    Student[Student or walk-in client]
    Staff[Office staff]
    Admin[Administrator]
    Viewer[Public monitor viewer]

    P1((1.0 Issue ticket))
    P2((2.0 Track ticket))
    P3((3.0 Operate queue))
    P4((4.0 Manage queue session))
    P5((5.0 Display live queue))
    P6((6.0 Report and export))

    D1[(D1 User records)]
    D2[(D2 Offices and services)]
    D3[(D3 Queue sessions)]
    D4[(D4 Queue requests)]
    D5[(D5 Daily queue sequences)]
    D6[(D6 Queue transactions)]
    D7[(D7 Notifications)]
    D8[(D8 Audit logs)]

    Student -->|Service, category, identity| P1
    P1 -->|Ticket, tracking code, receipt data| Student
    P1 -->|Office and service lookup| D2
    P1 -->|Session availability lookup| D3
    P1 -->|Active-ticket lookup and new ticket| D4
    P1 -->|Read and advance daily number| D5
    P1 -->|Issue notification| D7
    P1 -->|Ticket issuance event| D8

    Student -->|Tracking code or dashboard request| P2
    P2 -->|Ticket status, position, estimate| Student
    P2 -->|User and ticket lookup| D1
    P2 -->|Ticket and queue lookup| D4
    P2 -->|Office and service labels| D2
    P2 -->|Historical timing data| D6

    Staff -->|Call, recall, complete, skip, cancel| P3
    P3 -->|Updated queue result| Staff
    P3 -->|Staff and service access lookup| D1
    P3 -->|Office and service lookup| D2
    P3 -->|Read or update ticket state| D4
    P3 -->|Serving and completion timing| D6
    P3 -->|Call and turn notifications| D7
    P3 -->|Queue action event| D8

    Staff -->|Open, pause, resume, close, status request| P4
    P4 -->|Session status and operation result| Staff
    P4 -->|Staff authorization lookup| D1
    P4 -->|Office and service state| D2
    P4 -->|Read or update session state| D3
    P4 -->|Session action event| D8

    Viewer -->|Display request| P5
    P5 -->|Called and next tickets, office status| Viewer
    P5 -->|Ticket state and priority| D4
    P5 -->|Office and service labels| D2
    P5 -->|Office session state| D3

    Admin -->|Office, date, status filters; export request| P6
    P6 -->|Metrics, report rows, CSV file| Admin
    P6 -->|Queue report records| D4
    P6 -->|Office and service breakdowns| D2
    P6 -->|User labels| D1
    P6 -->|Completion and timing metrics| D6
    P6 -->|Recent activity records| D8
```

## Level 2: Process Decomposition

### 1.0 Issue Ticket

```mermaid
flowchart LR
    Student[Student or walk-in client]
    D2[(D2 Offices and services)]
    D3[(D3 Queue sessions)]
    D4[(D4 Queue requests)]
    D5[(D5 Daily queue sequences)]
    D7[(D7 Notifications)]
    D8[(D8 Audit logs)]

    P11((1.1 Validate request))
    P12((1.2 Allocate ticket number))
    P13((1.3 Save ticket))
    P14((1.4 Record issuance))
    P15((1.5 Build receipt and estimate))

    Student -->|Service, category, optional signed-in identity| P11
    P11 -->|Office and service details| D2
    P11 -->|Open session check| D3
    P11 -->|Active tickets for student limit check| D4
    P11 -->|Validated ticket request| P12
    P12 -->|Current date sequence read and update| D5
    P12 -->|Numbered ticket data| P13
    P13 -->|Waiting ticket and tracking code| D4
    P13 -->|Saved ticket data| P14
    P14 -->|Ticket issued notification| D7
    P14 -->|Ticket generation audit event| D8
    P14 -->|Ticket and service details| P15
    P15 -->|Queue position lookup| D4
    P15 -->|Service and office labels| D2
    P15 -->|Ticket number, tracking code, receipt, fixed 3-minute estimate per position| Student
```

### 2.0 Track Ticket

```mermaid
flowchart LR
    Student[Student or walk-in client]
    D1[(D1 User records)]
    D2[(D2 Offices and services)]
    D4[(D4 Queue requests)]
    D6[(D6 Queue transactions)]

    P21((2.1 Receive lookup request))
    P22((2.2 Find ticket))
    P23((2.3 Calculate queue position))
    P24((2.4 Prepare tracking result))

    Student -->|Tracking code or authenticated dashboard request| P21
    P21 -->|Identity for user's tickets| D1
    P21 -->|Lookup criteria| P22
    P22 -->|Ticket and status lookup| D4
    P22 -->|Matched ticket| P23
    P23 -->|Priority and earlier waiting tickets| D4
    P23 -->|Historical transaction timing| D6
    P23 -->|Position and estimate| P24
    P24 -->|Service and office labels| D2
    P24 -->|Ticket status, position, estimate, or not-found result| Student
```

### 3.0 Operate Queue

```mermaid
flowchart LR
    Staff[Office staff]
    D1[(D1 User records)]
    D2[(D2 Offices and services)]
    D4[(D4 Queue requests)]
    D6[(D6 Queue transactions)]
    D7[(D7 Notifications)]
    D8[(D8 Audit logs)]

    P31((3.1 Authorize staff action))
    P32((3.2 Call next ticket))
    P33((3.3 Recall last ticket))
    P34((3.4 Complete ticket))
    P35((3.5 Skip or cancel ticket))
    P36((3.6 Notify and audit action))

    Staff -->|Queue command and ticket identifier| P31
    P31 -->|Staff identity and role| D1
    P31 -->|Accessible office services| D2
    P31 -->|Authorized queue command| P32
    P31 -->|Authorized recall command| P33
    P31 -->|Authorized completion command| P34
    P31 -->|Authorized skip or cancellation command| P35

    P32 -->|Priority-ordered waiting ticket lookup and called state| D4
    P32 -->|New call timestamp and staff assignment| D6
    P32 -->|Called ticket event| P36
    P33 -->|Last called ticket state reset| D4
    P33 -->|Recalled ticket transaction removal| D6
    P33 -->|Recall event| P36
    P34 -->|Completed ticket state| D4
    P34 -->|Completion time and elapsed minutes| D6
    P34 -->|Completion event| P36
    P35 -->|Skipped or cancelled ticket state| D4
    P35 -->|Skip or cancellation event| P36
    P36 -->|Call notification when applicable| D7
    P36 -->|Queue action audit event| D8
    P36 -->|Updated queue result| Staff
```

### 4.0 Manage Queue Session

```mermaid
flowchart LR
    Staff[Office staff]
    D1[(D1 User records)]
    D2[(D2 Offices and services)]
    D3[(D3 Queue sessions)]
    D8[(D8 Audit logs)]

    P41((4.1 Authorize office action))
    P42((4.2 Read current session))
    P43((4.3 Apply session command))
    P44((4.4 Update office availability))
    P45((4.5 Record and return status))

    Staff -->|Office identifier and open, pause, resume, close, or status command| P41
    P41 -->|Staff role and assigned office| D1
    P41 -->|Office identity and services| D2
    P41 -->|Authorized command| P42
    P42 -->|Current session state| D3
    P42 -->|Session state and command| P43
    P43 -->|New session state and timestamps| D3
    P43 -->|Availability state for office and services| P44
    P44 -->|Office and service active flags| D2
    P44 -->|Updated session result| P45
    P45 -->|Session action audit event| D8
    P45 -->|Current session status and operation result| Staff
```

### 5.0 Display Live Queue

```mermaid
flowchart LR
    Viewer[Public monitor viewer]
    D2[(D2 Offices and services)]
    D3[(D3 Queue sessions)]
    D4[(D4 Queue requests)]

    P51((5.1 Select called tickets))
    P52((5.2 Select priority waiting tickets))
    P53((5.3 Determine office status))
    P54((5.4 Assemble display data))

    Viewer -->|Live display request| P51
    P51 -->|Called ticket records| D4
    P51 -->|Current called-ticket list| P54
    P52 -->|Waiting ticket records ordered by priority and time| D4
    P52 -->|Next ticket and waiting list| P54
    P53 -->|Open, paused, or closed session records| D3
    P53 -->|Office status| P54
    P54 -->|Office and service labels| D2
    P54 -->|Current tickets, next ticket, waiting list, office status| Viewer
```

### 6.0 Report and Export

```mermaid
flowchart LR
    Admin[Administrator]
    D1[(D1 User records)]
    D2[(D2 Offices and services)]
    D4[(D4 Queue requests)]
    D6[(D6 Queue transactions)]
    D8[(D8 Audit logs)]

    P61((6.1 Receive filters))
    P62((6.2 Retrieve filtered tickets))
    P63((6.3 Calculate report metrics))
    P64((6.4 Retrieve recent audit activity))
    P65((6.5 Format report or CSV))

    Admin -->|Office, date range, status, and export choice| P61
    P61 -->|Validated report criteria| P62
    P62 -->|Filtered ticket records| D4
    P62 -->|Office, service, and user labels| D2
    P62 -->|User names| D1
    P62 -->|Report rows| P65
    P63 -->|Queue totals and status counts| D4
    P63 -->|Completion and transaction timing| D6
    P63 -->|Office and service grouping| D2
    P63 -->|Metrics| P65
    P64 -->|Recent audit entries| D8
    P64 -->|Audit actor names| D1
    P64 -->|Audit activity| P65
    P65 -->|Dashboard metrics, report rows, audit activity, or downloadable CSV| Admin
```

## Data Store Definitions

| ID | Data store | Principal contents |
| --- | --- | --- |
| D1 | User records | Student, staff, and administrator identities and office assignments |
| D2 | Offices and services | Office names and status; service names, office links, and status |
| D3 | Queue sessions | Office session status, operator, and open/close timestamps |
| D4 | Queue requests | Ticket number, tracking code, owner, service, category, status, and request time |
| D5 | Daily queue sequences | Per-date last-issued ticket number |
| D6 | Queue transactions | Serving staff, call/completion timestamps, and elapsed minutes |
| D7 | Notifications | In-app, display, audio, and office notification records |
| D8 | Audit logs | Recorded ticket, queue, and session actions |

## Business Rules and Scope Notes

- A ticket request is accepted only for an active office and service with an open queue session. Signed-in students are limited to two active tickets; walk-in tickets may have no linked user.
- Queue priority is PWD and senior first, followed by regular tickets; tickets within a priority group are ordered by request time.
- The implemented daily sequence starts at 100 and uses the 100–600 range. **Current code wraps to 100 again if a same-day request advances past 600.** If the capstone requirement is to stop issuance at 600 for that day, that behavior must be changed in the application.
- `queue_requests` stores the ticket status. `queue_transactions` stores the call and completion timing. The new-ticket receipt estimate is three minutes per position; tracking lookup instead uses the average `wait_minutes` from completed transactions. The application computes `wait_minutes` from `called_at` to `completed_at` (elapsed service time), so it represents service duration rather than time spent waiting before service.
- Report row and office-breakdown filters use the selected office, date range, and status. The headline summary cards and recent audit activity are global/current data and are not all scoped to those filters.
- The public monitor aggregates tickets across offices. It refreshes display data in the client and reports Open if any session is open, otherwise Paused if any session is paused, otherwise Closed.
- Notifications are stored in the application database for in-app, display, and audio behavior; this diagram does not imply an external SMS gateway.

## Implementation References

- Routes and role-protected workflows: [routes/web.php](routes/web.php)
- Ticket issuance, tracking, and monitor data: [app/Http/Controllers/QueueController.php](app/Http/Controllers/QueueController.php)
- Staff queue operations: [app/Http/Controllers/DashboardController.php](app/Http/Controllers/DashboardController.php)
- Session controls: [app/Http/Controllers/QueueSessionController.php](app/Http/Controllers/QueueSessionController.php)
- Analytics and CSV export: [app/Http/Controllers/Admin/ReportsController.php](app/Http/Controllers/Admin/ReportsController.php)