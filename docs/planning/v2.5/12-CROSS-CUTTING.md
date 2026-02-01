## 12. Cross-Cutting

### 12.1 API Alignment

- Existing API: auth-user, deposits, disputes, items, stores, transactions, keys, etc. Add or extend only where needed for above features (e.g. support tickets, messages) if agents need them. Prefer web-first; API can follow same logic.

### 12.2 Cron

- **Transaction intents:** Already handled by Python (release, cancel). No change unless new action types.
- **Deposits (v2.5):** Python cron must: (1) fill `deposits.address` for rows with address NULL (HD-derived); (2) update deposit balances from chain; (3) process `deposit_withdraw_intents` (pending → send from deposit address to to_address, write history, set intent completed/failed). Add these to cron if not already present.
- **PHP cron (optional):** Expire password_reset_tokens (delete where expires_at < now); optional cleanup of old audit_log (retention policy later).

### 12.3 Security

- All new pages: require auth where appropriate; validate ownership (store, transaction, ticket). Staff routes: require staff or admin role. Reset tokens: single-use, expiry. Invite codes: server-side validate. No PGP/dark-web references.

### 12.4 Security Baselines (binding)

**CSRF:** Every web POST (forms that change state) must be CSRF-protected. Use a per-session or per-request token in the form; validate on POST. No exception for "internal" forms. Apply to: login, register, recover, settings, store settings, item edit/delete, deposits add/withdraw, payment actions (shipping, release, cancel, complete), dispute start/add claim, support new ticket/ticket reply, PM send, staff actions (ban, grant, ticket status, dispute resolve, warning create/resolve, etc.). No impersonate in v2.5.

**Rate limiting (binding):**

| Flow | Limit | Key | Response when exceeded |
|------|--------|-----|--------------------------|
| Password reset request | 5 per hour | per IP | 429 or generic "If that user exists, a link was sent" (no enumeration) |
| Login attempts | 10 per 5 minutes | per IP | 429 or delay + generic "Invalid credentials" |
| PM send | 10 per minute | per user_uuid | 429 |
| Support ticket create | 5 per hour | per user_uuid | 429 |
| Support ticket message | 20 per hour | per ticket | 429 |

Throttle recovery by IP to avoid enumeration and abuse; throttle PM/ticket by user to avoid spam. No user enumeration on recovery (same message for valid/invalid username).

### 12.5 Audit logging (binding)

Write to **audit_log** (actor_user_uuid, action_type, target_type, target_id, metadata JSON, created_at) for all staff/admin actions that change authority or money-related state. No fancy UI required in v2.5; just persist rows for debugging and compliance.

**Log at least:**

- user_ban (target_type=user, target_id=user_uuid)
- grant_staff, grant_seller (target_type=user, target_id=user_uuid)
- impersonate (target_type=user, target_id=impersonated user_uuid; metadata: who impersonated)
- dispute_resolve, dispute_partial_refund (target_type=dispute, target_id=dispute_uuid)
- warning_create, warning_resolve (target_type=store_warning, target_id=warning id)
- ticket_status_change (target_type=support_ticket, target_id=ticket id; metadata: old_status, new_status)

Optional: log release/cancel intents (target_type=transaction, action_type=release_request/cancel_request) for audit trail. Implement a small helper (e.g. `AuditLog::write($pdo, $actorUuid, $actionType, $targetType, $targetId, $metadata)`) and call it from the same code paths that perform the action.

### 12.6 Localization

- v2 has i18n data under `data/i18n/`. Add strings for new UI (Settings, Referrals, My store, Add item, Deposits, Recovery, Invite, Staff, etc.) as needed; can start with EN only.
