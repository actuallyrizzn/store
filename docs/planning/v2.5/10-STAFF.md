## 10. Staff

### 10.1 Goal

Full staff panel: dashboard, users, stores, tickets, disputes, warnings, deposits, stats, item categories. Middleware: require role staff or admin.

### 10.2 Current State

- Admin: `admin/index.php` (config, tokens). No staff panel.

### 10.3 Target State

- All under `/staff/`. Dashboard with links to each section. Sections: **stores** (list, detail, suspend, tiers), **tickets** (list, open thread, reply, status), **disputes** (list, link to detail), **warnings** (list, resolve/ack), **deposits** (list), **stats** (tables/charts), **categories** (CRUD item_categories). **No user management in staff panel**—user list, ban, grant staff/seller are admin-only at `/admin/users.php`; staff escalate to Admin.

### 10.4 URLs / Scripts

| Section | URL | Notes |
|---------|-----|--------|
| Dashboard | `/staff/index.php` | Links to sections (no users—user management is admin-only). |
| Stores | `/staff/stores.php`, `/staff/stores.php?uuid=…` | List; detail + suspend, tier. |
| Tickets | `/staff/tickets.php`, `/staff/tickets.php?id=…` | List; thread + reply, status. |
| Disputes | `/staff/disputes.php` | List; link to dispute.php. |
| Warnings | `/staff/warnings.php` | List store_warnings; add, resolve. |
| Deposits | `/staff/deposits.php` | List all or by store. |
| Stats | `/staff/stats.php` | Simple tables or charts (counts, volume). |
| Categories | `/staff/categories.php` | CRUD item_categories. |

### 10.5 Implementation

- Middleware: at top of each staff script, require $currentUser and role in (staff, admin). Redirect to login or 403 otherwise.
- Reuse or mirror v1 staff views; keep LEMP (one script per page or section). Stats: SELECT counts from transactions, users, stores; optional chart lib (e.g. simple HTML table first).

### 10.6 Files to Create/Modify

| Action | File |
|--------|------|
| Create | `app/public/staff/index.php` — dashboard. |
| Create | `app/public/staff/stores.php`, `tickets.php`, `disputes.php`, `warnings.php`, `deposits.php`, `stats.php`, `categories.php`. (No staff/users—user management is `/admin/users.php` only.) |
| Modify | Nav — add Staff link for staff/admin. |
