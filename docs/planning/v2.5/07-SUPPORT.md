## 7. Support

### 7.1 Goal

Ticket list, new ticket, ticket thread (view + reply). Staff can reply and set status.

### 7.2 Schema

**New tables:**

- **support_tickets**
  - `id` (PK), `user_uuid` (FK users), `subject` (TEXT), `status` (TEXT: open, closed, etc.), `created_at`, `updated_at`.
- **support_ticket_messages**
  - `id` (PK), `ticket_id` (FK support_tickets), `user_uuid` (FK users), `body` (TEXT), `created_at`.

**Pagination, limits, indexes (binding):**

- **Max body length:** 10_000 characters for ticket message body; reject longer on POST.
- **Indexes:** `support_ticket_messages(ticket_id, created_at)` for thread listing.
- **Pagination:** Ticket list: 50 per page. Ticket thread: 50 messages per page.
- **Retention:** v2.5: no deletion. No soft-delete. Add later if needed.
- **Abuse / rate limiting:** Rate limit ticket creation: 5 new tickets per hour per user. Rate limit message send: **20 messages per hour per ticket** (binding). Return 429 when exceeded.

### 7.3 URLs / Scripts

| Page | URL | Method |
|------|-----|--------|
| List | `/support.php` | GET — my tickets. |
| New | `/support/new.php` | GET form, POST — create ticket + first message. |
| Thread | `/support/ticket.php?id=…` | GET — messages; POST — add message; staff can set status. |

### 7.4 Files to Create/Modify

| Action | File |
|--------|------|
| Modify | Schema — createSupportTickets(), createSupportTicketMessages(). |
| Create | `app/public/support.php`, `app/public/support/new.php`, `app/public/support/ticket.php`. |
