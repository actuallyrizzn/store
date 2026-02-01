## 6. Board & Messages (PMs Only)

### 6.1 Goal

Private messages only: list conversations (distinct peers), open thread with a user, send/reply.

### 6.2 Current State

No messages in app.

### 6.3 Schema

**New table: private_messages**

- `id` (PK), `from_user_uuid` (FK users), `to_user_uuid` (FK users), `body` (TEXT), `read_at` (TEXT nullable), `created_at` (TEXT).
- **Max body length (binding):** 10_000 characters. Reject longer on POST.
- **Indexes (binding):** `(from_user_uuid, created_at)`, `(to_user_uuid, created_at)` for thread and list queries. Optional: index for "last message per conversation" (e.g. `(LEAST(from_user_uuid, to_user_uuid), GREATEST(from_user_uuid, to_user_uuid), created_at)`) or compute distinct peers with subquery; ensure list query is O(messages per user) not O(N²).
- **Pagination (binding):** Thread view: 50 messages per page (default); `?page=2` etc. Conversation list: 50 conversations per page.
- **Retention / deletion:** v2.5: no user-facing delete. No soft-delete per user. If needed later, add `deleted_by_sender_at` / `deleted_by_recipient_at` and filter in SELECT.
- **Abuse / rate limiting (binding):** Rate limit PM send: 10 messages per minute per user (per user_uuid). Return 429 when exceeded. Optional: per-recipient limit (e.g. 5/min to same user) to curb spam.

### 6.4 URLs / Scripts

| Page | URL | Method |
|------|-----|--------|
| PM list | `/messages.php` | GET — list conversations (last message preview, unread count). |
| PM thread | `/messages.php?username=…` or `?peer=…` | GET — thread with that user; POST — send message. |

### 6.5 Implementation

- List: SELECT distinct peer, last message, unread count WHERE from_user_uuid = ? OR to_user_uuid = ?. Order by last message time. Paginate 50 per page. Use indexes above.
- Thread: GET messages WHERE (from,to) = (me, peer) OR (to,from) = (me, peer) ORDER BY created_at DESC LIMIT 50 OFFSET (page-1)*50. POST: validate body length ≤ 10_000; rate limit 10/min per user; insert private_messages; set read_at on recipient when viewing (optional).

### 6.6 Files to Create/Modify

| Action | File |
|--------|------|
| Modify | Schema — createPrivateMessages(). |
| Create | `app/public/messages.php` — list + thread by query param. |
