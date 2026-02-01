## 13. Order of Implementation (Suggested)

1. **Schema** — Add **all** schema deltas before any UI work. **New tables:** password_reset_tokens, invite_codes, reviews, store_warnings, support_tickets, support_ticket_messages, private_messages, deposit_withdraw_intents, audit_log. **New columns:** stores.withdraw_address (TEXT nullable), transactions.buyer_confirmed_at (TEXT nullable), disputes.transaction_uuid (FK transactions) if missing, dispute_claims.user_uuid (FK users) if missing. **Ensure item_categories exists** (id, name_en, parent_id)—required by staff categories; create in schema run if not already present so fresh environments do not break. Add indexes per §6, §7, §11. Run migration. Do not start Nav/User/Auth until this step is complete so agents do not hit missing-table or missing-column bugs mid-phase.
2. **Nav** — Extend web_header: Settings, Referrals, My orders, My store, Add item, Deposits (vendor-only), Staff. No Wallet. Quick win.
3. **User** — Profile, user settings, store settings, referrals, verification agreement (and plan page). Enables Settings link.
4. **Auth** — Recovery (username-only; token shown in UI; no email in v2.5), invite registration, admin users (list, ban, staff, seller). No impersonate.
5. **Vendor CMS** — Item edit/delete, deposits list/add/withdraw.
6. **Store** — Reviews and warnings (schema already added); store page tabs or subpages.
7. **Transactions** — Payment page: shipping, release, cancel, complete buttons and POST handlers.
8. **Disputes** — Start from payment; dispute detail; admin/staff list.
9. **Support** — Ticket list, new ticket, ticket thread.
10. **PMs** — Messages list, thread, send.
11. **Staff** — Dashboard and all sections (users, stores, tickets, disputes, warnings, deposits, stats, categories).

---

## Doc and Repo

- **Planning (v2):** All v2 planning docs in [../v2/](../v2/). Binding: **08-PLANNING-DECISIONS-QA.md**.
- **This plan:** [docs/planning/v2.5/](README.md) (this folder).
- **App:** Implement in `app/` (PHP web + API; Python cron unchanged unless new intents require it).
