## 1. Nav

### 1.1 Goal

Header shows **Settings**, **Referrals**, **My orders** (payments) for all logged-in users; **vendors** additionally see **My store**, **Add item**, **Deposits**. No Wallet. Optional: user dropdown (avatar + menu) instead of flat links. Staff/admin see **Staff** link to staff panel.

### 1.2 Current State

- **File:** `app/public/includes/web_header.php`.
- **Logged-in links today:** Marketplace, Vendors, My orders, Create store, Admin (if role === admin), Logout (username).
- **Missing:** Settings, Referrals, My store, Add item, Deposits (vendor-only). No "vendor check" (store_users) to show My store / Add item / Deposits only for vendors.

### 1.3 Target State

- **All logged-in users:** Settings, Referrals, My orders (payments). No "Wallet."
- **Vendors only:** My store, Add item, Deposits (link to `/deposits.php`). Vendor = has at least one row in `store_users` for current user.
- **Staff/admin:** Staff link (e.g. `/staff/` or `/staff/index.php`). Admin additionally sees Admin (config/tokens).
- **Anonymous:** Unchanged (Marketplace, Vendors, Login, Register).

### 1.4 Schema

No new tables. **Queries:** (1) Resolve "my store" for current user: `SELECT store_uuid FROM store_users WHERE user_uuid = ? LIMIT 1` (or primary store by role; v2 has store_users with role). If user has multiple stores, **store switcher required**: nav or "My stores" must let user choose which store for My store / Add item / Deposits. Use first store only when single store.

### 1.5 URLs / Scripts

| Link | Target URL | Notes |
|------|------------|--------|
| Settings | `/settings/user` or `/settings.php` (user tab) | May be single settings page with tabs: user, store, API. |
| Referrals | `/referrals.php` | |
| My store | `/store.php?uuid={store_uuid}` | store_uuid from store_users. |
| Add item | `/item/new.php?store_uuid={store_uuid}` or `/store-admin/{store_uuid}/item/new` | Vendor only; store_uuid from store_users. |
| Deposits | `/deposits.php` | Vendor only; list/add/withdraw vendor deposits (no "Wallet"—no in-app buyer wallet in v2.5). |
| Staff | `/staff/` or `/staff/index.php` | Visible if role in (staff, admin). |

### 1.6 Implementation

1. **Extend `web_header.php`:**
   - For all logged-in: Settings, Referrals, My orders (existing payments link).
   - Query: `SELECT store_uuid FROM store_users WHERE user_uuid = ? LIMIT 1`. If row exists: add My store, Add item, **Deposits** (href `/deposits.php`). Do not add "Wallet."
   - If role is staff or admin: add Staff link.
   - Optional: wrap user links in a dropdown (e.g. "Account" or username).
2. **No "Wallet" in v2.5:** Vendor funds = Deposits only. Buyer sees My orders (payments). No in-app buyer wallet; no Wallet link or balance in nav.

### 1.7 Files to Create/Modify

| Action | File |
|--------|------|
| Modify | `app/public/includes/web_header.php` — add links, vendor query, optional dropdown. |

### 1.8 Access Control

- All new nav links require `$currentUser`. Staff link requires role in (staff, admin). My store / Add item require store membership (store_users).
