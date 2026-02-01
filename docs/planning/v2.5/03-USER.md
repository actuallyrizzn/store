## 3. User

### 3.1 Goal

- **Profile / about:** Public page for a user (username, join date, feedback count if we have it; link to their store if vendor).
- **User settings:** Change password, email (if added), preferences.
- **Store settings:** Store name, description, vendorship re-agree (only for store owner).
- **Referrals:** Referral link, referred users list, referral earnings (if referral_payments used).
- **Verification:** Vendorship agreement (re-agree + timestamp); optional verification "plan" (gold/silver/bronze) page.

### 3.2 Current State

- No `user.php` (public profile). No `settings/` routes. No `referrals.php`. No verification pages. `users` has inviter_uuid; `stores` has vendorship_agreed_at; `referral_payments` exists.

### 3.3 Target State

- Public profile at `/user.php?username=…`. Settings: `/settings/user.php` (password, prefs), `/settings/store.php` (store; store owner only). Referrals: `/referrals.php`. Verification: `/verification/agreement.php` (re-agree) only. No plan page in v2.5.

### 3.4 Schema

- **Users:** Already has created_at, inviter_uuid. Optional: add email (TEXT) for recovery and display; if not in v2.5, recovery by username only.
- **Stores:** Already has storename, description, vendorship_agreed_at. Store settings just update these.
- **Referral_payments:** Exists; referrals page JOIN referral_payments with users to show earnings. "Referred users": SELECT * FROM users WHERE inviter_uuid = ?.
- No new tables required for profile/settings/referrals/agreement. Verification "plan" might be config or separate table (e.g. account_tiers); can be read-only page for v2.5.

### 3.5 URLs / Scripts

| Page | URL | Method | Notes |
|------|-----|--------|--------|
| Profile | `/user.php?username=…` | GET | Public; show username, created_at, "Member since"; if vendor link to store. |
| User settings | `/settings/user.php` | GET form, POST | Change password; optional email/prefs. |
| Store settings | `/settings/store.php` | GET form, POST | store_uuid from store_users (owner); update storename, description; re-agree vendorship (update vendorship_agreed_at). |
| Referrals | `/referrals.php` | GET | Referral link (e.g. /register.php?invite=USER_REFCODE or site ref code); list referred users; list referral_payments for current user. |
| Agreement | `/verification/agreement.php` | GET + POST | Show agreement text; POST sets stores.vendorship_agreed_at for user's store(s). |
| Plan | `/verification/plan.php` | GET | Info on tiers (gold/silver/bronze); optional purchase later. |

### 3.6 Flows

- **Profile:** GET user by username; 404 if not found; render. If user has store (store_users), query store and link to `/store.php?uuid=…`.
- **User settings:** POST: validate current password, set new password (hash), redirect.
- **Store settings:** Resolve store for current user (store_users); POST: update stores.storename, description; if "I agree" checkbox, set vendorship_agreed_at = now.
- **Referrals:** Same mechanism as invite codes; consolidate (KISS). Referral link = invite code or user-scoped code; one consistent flow. List: SELECT * FROM users WHERE inviter_uuid = current_user. List earnings: SELECT * FROM referral_payments WHERE user_uuid = current_user.
- **Agreement:** POST: update vendorship_agreed_at for user's store(s).

### 3.7 Files to Create/Modify

| Action | File |
|--------|------|
| Create | `app/public/user.php` — public profile by username. |
| Create | `app/public/settings/user.php` — change password (and prefs if any). |
| Create | `app/public/settings/store.php` — store settings (store owner). |
| Create | `app/public/referrals.php` — referral link, referred users, earnings. |
| Create | `app/public/verification/agreement.php` — vendorship re-agree. |
| Create | `app/public/verification/plan.php` — tier info (static or from config). |

### 3.8 Access Control

- Profile: public. User/store settings: require $currentUser; store settings require store ownership. Referrals: current user only. Verification: current user, store owner for agreement.
