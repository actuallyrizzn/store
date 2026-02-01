## 11. Schema Changes Summary

All new or changed schema for v2.5 in one place. Add to `app/public/includes/Schema.php` and run (e.g. via `schema.php` or migration).

| Table | Purpose |
|-------|---------|
| **password_reset_tokens** | id, user_uuid, token (unique), expires_at, created_at |
| **invite_codes** | id, code (unique), created_by_user_uuid, used_by_user_uuid, used_at, created_at |
| **reviews** | id, transaction_uuid (UNIQUE), store_uuid, rater_user_uuid, score, comment, created_at |
| **store_warnings** | id, store_uuid, author_user_uuid, message, status (open/acked/resolved), created_at, updated_at, resolved_at, acked_at |
| **support_tickets** | id, user_uuid, subject, status, created_at, updated_at |
| **support_ticket_messages** | id, ticket_id, user_uuid, body, created_at |
| **private_messages** | id, from_user_uuid, to_user_uuid, body, read_at, created_at |
| **deposit_withdraw_intents** | id, deposit_uuid, to_address (set from stores.withdraw_address only; no user input), requested_at, requested_by_user_uuid, status (pending/completed/failed), created_at |
| **audit_log** | id, actor_user_uuid, action_type, target_type, target_id, metadata (JSON), created_at |

**Existing tables (changes for v2.5):** **stores** — add `withdraw_address` (TEXT nullable) for deposit withdraw binding. **transactions** — add `buyer_confirmed_at` (TEXT nullable) for "Confirm received". **disputes** — add `transaction_uuid` (FK to transactions) if missing. **dispute_claims** — add `user_uuid` (FK users, author of claim) if missing. Keep existing columns (e.g. claim, status); no duplicate body column. KISS. **Existing table (required by staff/categories; ensure created in schema run):** **item_categories** — id, name_en, parent_id. Staff panel CRUD uses it. If your v2 schema creation (e.g. Schema.php createItemCategories) does not already create this table, add it to the schema run so a fresh environment has it; otherwise staff categories will break. Other tables (users, store_users, items, packages, transaction_statuses, shipping_statuses, transaction_intents, deposits, deposit_history, referral_payments, api_keys, config): no structural change unless noted.
