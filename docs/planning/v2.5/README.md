# V2.5 Build Plan (Full Specification)

Clawed Road v2 shipped with core marketplace (browse, buy, orders) and minimal vendor/admin. This plan closes the gap with the same level of detail as the v2 planning docs: **nav**, **auth**, **user**, **store**, **vendor CMS**, **PMs**, **support**, **transactions**, **disputes**, and **staff**.

**Reference:** v1 (Tochka) lives in repo root (Go); planning docs for v2 are in [../v2/](../v2/). Implement in **`app/`** (PHP web + API, Python cron unchanged unless noted). **Binding planning decisions:** [../v2/08-PLANNING-DECISIONS-QA.md](../v2/08-PLANNING-DECISIONS-QA.md).

---

## Executive Summary

### Scope (In Scope for v2.5)

| Area | Deliverables |
|------|--------------|
| **Nav** | Settings, Referrals, My store, Add item, Deposits (vendor-only); My orders (payments) for all; no "Wallet" (no in-app buyer wallets). |
| **Auth** | Password recovery (request → token → reset; token shown in UI; no email in v2.5); invite-code registration; auth admin (users list, ban, grant staff/seller). No impersonate in v2.5. |
| **User** | Public profile/about; user settings (password, preferences); store settings (storename, description, vendorship re-agree); referrals page; verification (agreement + plan). |
| **Store (vendor)** | Store settings (see User); reviews page; warnings page. |
| **Vendor CMS** | Deposits list/add/withdraw; item edit; item delete (soft). |
| **Board & Messages** | Private messages only: list conversations, thread, send/reply. |
| **Support** | Ticket list, new ticket, ticket thread (view + reply); staff can reply and set status. |
| **Transactions** | From payment page: actions shipping, release, cancel, complete (by role and status). |
| **Disputes** | Start dispute from tx page; dispute detail (view, claims, status); dispute admin list. |
| **Staff** | Full staff panel: dashboard, stores, tickets, disputes, warnings, deposits, stats, item categories (no user management—admin only). |

### Out of Scope for v2.5

- Shoutbox / public messageboard (PMs only).
- In-app buyer wallets / "fund from wallet" (per v2/08: buyer sends from external wallet only).
- Vendor referral payouts (roadmap per v2/08).
- Multisig / Gnosis Safe escrow (roadmap).
- 2FA (roadmap per v2/08).
- API scope expansion beyond what's needed for above (existing API patterns reused).
- **Impersonate:** In this plan, "impersonate" means an admin (or staff) "login as user"—the session becomes that user for support/debug. **Excluded from v2.5** unless it serves a clear use case; roadmapped for later if needed.
- **Verification plan page** (gold/silver/bronze tiers): out; roadmap. Only vendorship agreement (re-agree) in scope.
- **Config to shorten auto-release when buyer confirmed:** out; roadmap.

### Roadmap (post–v2.5)

Items explicitly out of scope for v2.5; implement later if needed:

- **Impersonate** ("login as user")
- **Verification plan page** (gold/silver/bronze tiers)
- **Config to shorten auto-release** when buyer has confirmed

### Current State (app/ as of v2)

- **Public:** `app/public/` — LEMP, one script per page; `includes/` (Schema, User, Session, StatusMachine, web_header, web_footer, bootstrap).
- **Nav:** Header (`web_header.php`) shows Marketplace, Vendors, My orders, Create store, Admin (if admin), Logout/Login/Register. No Settings, Referrals, My store, Add item, or Wallet.
- **Auth:** Login, register, logout only. No recovery, no invite codes, no auth admin (admin has config + tokens only).
- **User:** No public profile page; no settings pages; no referrals page; no verification pages.
- **Store:** `store.php` shows store by uuid; no reviews or warnings tabs/pages.
- **Vendor CMS:** No deposits web UI; no item edit/delete web UI (API has items CRUD).
- **Messages:** None.
- **Support:** None.
- **Transactions:** `payment.php` shows one order (status, escrow); no shipping/release/cancel/complete actions.
- **Disputes:** API exists (`api/disputes.php`); no web "start dispute" or dispute detail or admin list.
- **Staff:** No staff panel (admin has `admin/index.php` for config/tokens only).

### Target State (After v2.5)

- Nav: logged-in users see Settings, Referrals, My orders (payments), and—if vendor—My store, Add item, Deposits; staff/admin see Staff link. No Wallet.
- Auth: recover password (request → token → reset; token shown in UI; no email in v2.5); register with optional invite code; admin can list users, ban, grant staff/seller. No impersonate in v2.5.
- User: public `/user.php?username=…`; settings/user, settings/store; referrals page; verification agreement + plan.
- Store: store page with reviews and warnings (tabs or subpages).
- Vendor CMS: deposits list/add/withdraw; item edit and item delete.
- PMs: list conversations, open thread, send/reply.
- Support: list tickets, new ticket, ticket thread; staff reply and set status.
- Transactions: payment page has buttons (shipping, release, cancel, complete) by role and status; POST handlers call StatusMachine / transaction intents.
- Disputes: "Open dispute" from payment page; dispute detail page; staff dispute list.
- Staff: staff dashboard and sections (stores, tickets, disputes, warnings, deposits, stats, categories). User management (list, ban, grant) is admin-only; staff escalate to Admin.

### Document Map (This Folder)

| § | Doc | Contents |
|---|-----|----------|
| 1 | [01-NAV.md](01-NAV.md) | Header links, vendor submenu (Deposits, not "Wallet"); files to change. |
| 2 | [02-AUTH.md](02-AUTH.md) | Password recovery, invite codes, auth admin; schema, flows, scripts. |
| 3 | [03-USER.md](03-USER.md) | Profile, user settings, store settings, referrals, verification; schema, URLs, flows. |
| 4 | [04-STORE.md](04-STORE.md) | Reviews, warnings; schema, URLs. |
| 5 | [05-VENDOR-CMS.md](05-VENDOR-CMS.md) | Deposits, item edit/delete; URLs, API reuse. |
| 6 | [06-MESSAGES.md](06-MESSAGES.md) | PMs only; schema, list/thread/send. |
| 7 | [07-SUPPORT.md](07-SUPPORT.md) | Tickets; schema, list/new/thread. |
| 8 | [08-TRANSACTIONS.md](08-TRANSACTIONS.md) | Shipping/release/cancel/complete on payment page; intents and StatusMachine. |
| 9 | [09-DISPUTES.md](09-DISPUTES.md) | Start, detail, admin list; schema alignment. |
| 10 | [10-STAFF.md](10-STAFF.md) | Full panel; middleware, sections, URLs. |
| 11 | [11-SCHEMA-SUMMARY.md](11-SCHEMA-SUMMARY.md) | All new tables and columns in one place. |
| 12 | [12-CROSS-CUTTING.md](12-CROSS-CUTTING.md) | API alignment, cron, security, Security Baselines (CSRF, rate limits), Audit logging. |
| 13 | [13-ORDER-OF-IMPLEMENTATION.md](13-ORDER-OF-IMPLEMENTATION.md) | Suggested sequence; doc and repo references. |

### Implementation principle (binding)

Where this plan leaves discretion: **do what makes sense**; avoid duplicate cruft; **KISS**. There are no live production systems yet—prefer simplicity. Do not create duplicate code paths for the same function as v2.

### Gating and Dependencies

- **Schema first:** Add any new tables (password_reset_tokens, invite_codes, reviews, store_warnings, support_tickets, support_ticket_messages, private_messages) before building pages that use them.
- **StatusMachine / transaction intents:** v2 already has `transaction_intents` and append-only status/shipping; use them for shipping/release/cancel/complete (PHP writes intents; Python cron performs chain actions per v2/05).
- **Access control (binding):** Staff panel requires role in (staff, admin). Admin-only routes require role = admin. Vendor = store_users membership only (no `vendor` in users.role). See **Role Matrix (Binding)**. Reuse `$currentUser` from Session; "is vendor?" = SELECT 1 FROM store_users WHERE user_uuid = ?.

### Decisions (binding)

- **Impersonate:** Out of scope for v2.5 (roadmap). Do not implement "login as user."
- **Staff and user management:** Staff does **not** do user management. User list, ban, grant staff/seller are **admin only**. Staff escalate to admin for user actions.
- **Dispute list:** One route only: **`/staff/disputes.php`**. Both staff and admin use it. Do not add `/admin/disputes.php`.
- **Referral and invite:** Same flow; consolidate. Referral link uses the same mechanism as invite codes (e.g. invite_codes row or user-scoped code). One consistent mechanism; KISS.
- **Verification plan page:** Out of scope; roadmap. Only `/verification/agreement.php` (vendorship re-agree) in v2.5.
- **Support ticket message rate limit:** **Per ticket** (20 messages per hour **per ticket**). Binding. Pick this rule only; do not use "per user across all tickets."
- **Multiple stores (binding):** If we allow multiple stores per user, a **store switcher is required**. Nav or "My stores" must let the user choose which store for My store / Add item / Deposits. No multi-store without a switcher.
- **Deposit add (binding):** PHP must **send** chain_id (and currency/crypto per existing deposits schema) to Python. Python **defines** allowed chain/network options dynamically; if nothing is defined, default is **ETH mainnet** (system default). Define the contract (what PHP sends) and keep it consistent; do **not** duplicate v2 logic for the same function. Schema: no new columns that duplicate v2.
- **Buyer-confirmed shortening:** Out of scope; roadmap. No config in v2.5 to shorten auto-release when buyer confirmed.
- **Review score:** **1–5** (binding). Do not change.
- **dispute_claims:** Add **user_uuid** (author) only; keep existing columns (e.g. claim, status). No duplicate body column; KISS.
- **stores.withdraw_address:** **Validate format** (EVM address) before save; reject invalid. Binding.

---

## Role Matrix (Binding)

**Roles (canonical, binding):** `admin`, `staff`, `customer` only. Stored in `users.role`. A user has exactly one of these three. **There is no `vendor` value in `users.role`.**

**Vendor (binding):** "Vendor" is **not** a user role; it is **purely** store membership. A user **is a vendor** (for nav, item CMS, deposits, tx actions) if and only if they have at least one row in `store_users` (any store). So: `user.role = customer` but they have a `store_users` row → they see My store, Add item, Deposits (no contradiction). `user.role = customer` with zero stores → customer only, no vendor UI. Implement "is vendor?" as "SELECT 1 FROM store_users WHERE user_uuid = ? LIMIT 1"; do **not** check `users.role = 'vendor'`.

**store_users.role:** Within a store, `store_users.role` is `owner` or `collaborator`. **Owner** can: edit store settings (storename, description, vendorship agreement), manage store-level data, and (per deposit rules below) request withdraw. **Owner and collaborator** can: add/edit/delete items for that store, list/add deposits, mark shipped / release for that store's transactions. "My store" / "Add item" / "Deposits" in nav show for any user who has any `store_users` row.

**Staff vs admin:** Staff is **separate** from admin. Staff can: list/reply tickets, list/resolve disputes, list/resolve/ack warnings, list deposits, view stats, CRUD item categories. Admin can do everything staff can **plus**: list/ban/grant users (grant staff, grant seller), config and API tokens (`/admin/`). So: staff panel (`/staff/`) is available to both staff and admin; admin-only routes require `role = admin`. Staff do **not** do user management; escalate to admin. No impersonate in v2.5.

**Grant seller (binding):** "Grant seller" = create a store (if needed) and add the user to `store_users` as **owner**. Do **not** set `users.role = 'vendor'` (that value does not exist). The user remains `admin`, `staff`, or `customer`; they gain vendor capabilities by virtue of `store_users` membership.

**Allowed actions by route group (binding):**

| Route group | customer | vendor (for own store) | staff | admin |
|-------------|----------|------------------------|------|-------|
| Public (marketplace, store view, user profile) | ✓ | ✓ | ✓ | ✓ |
| My orders / payments (buyer) | ✓ (own) | ✓ (own as buyer) | ✓ | ✓ |
| Payment page actions (shipping, release, cancel, complete) | See State & Permission Matrix | See State & Permission Matrix | See State & Permission Matrix | See State & Permission Matrix |
| Settings (user, store), Referrals, Verification | ✓ (own) | ✓ (own; store settings if owner) | ✓ | ✓ |
| Deposits (list/add/withdraw) | — | ✓ (stores they belong to) | list only (staff) | list only (admin) |
| Item CMS (new/edit/delete) | — | ✓ (stores they belong to) | — | ✓ |
| Reviews (view; create after RELEASED) | ✓ (create if buyer) | ✓ (view store) | ✓ | ✓ |
| Warnings (view store; ack) | — | ✓ (view; ack if owner) | create, resolve, list | create, resolve, list |
| PMs (list, thread, send) | ✓ (own) | ✓ (own) | ✓ (own) | ✓ (own) |
| Support (list, new, thread, reply) | ✓ (own tickets) | ✓ (own tickets) | ✓ (all tickets; reply, set status) | ✓ (all tickets; reply, set status) |
| Disputes (start, view, add claim) | ✓ (buyer on tx) | ✓ (vendor on tx) | view all, resolve, partial refund | view all, resolve, partial refund |
| Staff panel (dashboard, stores, tickets, disputes, warnings, deposits, stats, categories) | — | — | ✓ | ✓ |
| Admin only (user list, ban, grant staff/seller, config, tokens) | — | — | — | ✓ |

---

## State & Permission Matrix (Binding)

This table is the **single source of truth** for which actions are allowed on the payment/transaction page, by **role**, **payment status**, **shipping status**, and **dispute status**. All UI buttons and POST handlers must derive from this matrix. No ad hoc permission checks.

**Axes:**

- **Payment status** (from latest `transaction_statuses`): `PENDING`, `COMPLETED`, `RELEASED`, `CANCELLED`, `FAILED`, `FROZEN`.
- **Shipping status** (from latest `shipping_statuses`): `DISPATCH PENDING`, `DISPATCHED`.
- **Dispute status:** `NONE` (no dispute linked), `OPEN` (dispute exists and not resolved), `RESOLVED`.
- **Role in context:** buyer (current user = `transactions.buyer_uuid`), vendor (current user in `store_users` for `transactions.store_uuid`), staff, admin.

**Allowed actions and effect:**

| Payment status | Shipping | Dispute | Buyer | Vendor | Staff/Admin | Action | Backend effect |
|----------------|----------|---------|-------|--------|------------|--------|----------------|
| PENDING | any | NONE | Cancel | — | Cancel (admin override) | Cancel | Insert intent CANCEL; Python refunds, sets CANCELLED. |
| PENDING | any | any | — | — | — | (no other actions) | |
| COMPLETED | DISPATCH PENDING | NONE | Confirm received; Open dispute | Mark shipped; Release; Open dispute | Mark shipped; Release; Open dispute | Mark shipped | Append shipping_status DISPATCHED. |
| COMPLETED | DISPATCH PENDING | NONE | same | same | same | Release | Insert intent RELEASE; Python pays out, sets RELEASED. |
| COMPLETED | DISPATCH PENDING | NONE | same | same | same | Confirm received | Set buyer_confirmed_at (or equivalent); annotation only (no shorten auto-release in v2.5); **does not** immediately release (release = vendor or auto-release). |
| COMPLETED | DISPATCHED | NONE | Confirm received; Open dispute | Release; Open dispute | Release; Open dispute | Release | Insert intent RELEASE. |
| COMPLETED | any | NONE | Open dispute | Open dispute | — | Open dispute | Create dispute, link to tx; **immediately** append transaction_status FROZEN (binding: dispute create → FROZEN). |
| FROZEN | any | OPEN | Add claim; (no release/cancel) | Add claim; (no release/cancel) | Resolve; Partial refund | Resolve / Partial refund | Staff only: set dispute status, insert PARTIAL_REFUND intent or close dispute; Python performs partial refund per v2/01. **PARTIAL_REFUND intent → on success set transaction status to CANCELLED (with receipt).** |
| RELEASED, CANCELLED, FAILED | any | any | — | — | — | (none) | Terminal; no actions. |

**Cancel semantics (binding):**

- **Buyer** may request **Cancel** only when payment status is **PENDING** (buyer has not yet paid, or wants to abort). Once COMPLETED, buyer cannot cancel; only "Confirm received" or "Open dispute."
- **Vendor** does **not** get a "Cancel" button in v2.5; cron handles "COMPLETED + not dispatched in 72h → freeze" (v2/01). Vendor can "Mark shipped" or "Release."
- **Staff/Admin** may request Cancel only when PENDING (e.g. support override); for COMPLETED/FROZEN, use dispute flow (resolve or partial refund).
- **If COMPLETED is not dispatched and enters FROZEN** (e.g. cron freeze after 72h), resolution is **via dispute flow only** (staff resolves or partial refund); there is no direct cancel action in COMPLETED. Do not reintroduce cancel-completed as a support override.

**Complete / "Confirm received" semantics (binding):**

- **Complete** = buyer confirms they received the goods. Stored as a timestamp (e.g. `buyer_confirmed_at` on transaction or a status row). It does **not** by itself trigger immediate release. Release is either: (1) vendor clicks **Release**, or (2) auto-release after `completed_duration` (cron). Optionally, config can shorten auto-release when buyer has confirmed; if so, document in config. Default v2.5: **Complete** is annotation only; release still requires vendor or cron.
