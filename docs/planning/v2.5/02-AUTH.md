## 2. Auth

### 2.1 Goal

- **Password recovery:** User requests reset → receives token/link → sets new password (2-step flow).
- **Invite-code registration:** `register.php?invite=CODE`; validate code; allow or restrict registration; record code usage.
- **Auth admin:** Admin-only: list users, view user, ban user, grant staff, grant seller (store). No impersonate in v2.5.
### 2.2 Current State

- **Login/Register/Logout:** `app/public/login.php`, `register.php`, `logout.php`; Session, User in includes. No recovery, no invite codes.
- **Admin:** `app/public/admin/index.php` (config, tokens); no user list or user actions.
- **Schema:** `users` has uuid, username, passphrase_hash, role, inviter_uuid, banned, etc. No `password_reset_tokens`, no `invite_codes`.

### 2.3 Target State

- **Recovery:** Step 1: request reset (username only (no email in v2.5)); create token; display reset link in UI. Step 2: open link with token, set new password, invalidate token.
- **Invite:** Register page accepts `?invite=CODE`; validate code; store used_by, used_at; optional: require invite to register (configurable).
- **Auth admin:** `admin/users.php` — list users (table: username, role, banned, created_at); link to user detail; user detail: ban, grant staff, grant seller (create store or add to store_users). No impersonate in v2.5.

### 2.4 Schema

**New tables:**

- **password_reset_tokens**
  - `id` (PK), `user_uuid` (FK users), `token` (unique, index), `expires_at` (TEXT), `created_at` (TEXT).
  - One active token per user or allow multiple; on use delete or mark used.
- **invite_codes**
  - `id` (PK), `code` (TEXT unique, index), `created_by_user_uuid` (FK users, nullable), `used_by_user_uuid` (FK users, nullable), `used_at` (TEXT nullable), `created_at` (TEXT).
  - Validate: code exists, used_at IS NULL; on register set used_by_user_uuid, used_at.

**Users:** Already has `banned`, `role`. "Grant seller" = ensure user has a store (create store or add to store_users); may need "default store" or single store per user for "Add item" in nav.

### 2.5 URLs / Scripts

| Page | URL | Method | Notes |
|------|-----|--------|--------|
| Request reset | `/recover.php` | GET form, POST submit | Submit username; create token; display reset link in UI (no email in v2.5). |
| Reset password | `/recover.php?token=…` or `/recover_step2.php?token=…` | GET form, POST submit | Validate token, set new password, delete token. |
| Register with invite | `/register.php?invite=CODE` | GET prefill, POST same | Validate code on POST; record use on success. |
| Auth admin list | `/admin/users.php` | GET | List users; admin only. |
| Auth admin user | `/admin/users.php?uuid=…` or `?username=…` | GET + POST | User detail; POST actions: ban, set role staff, grant seller (store). No impersonate. |

### 2.6 Flows

**Password recovery (v2.5 binding):**

- **Username-only; no email in v2.5.** Do not add an email column or SMTP for recovery. One path only: username → create token → **display reset link in the UI** (no email sent).
1. User visits `/recover.php`, enters username.
2. POST: find user by username; create row in `password_reset_tokens` (token = random 32 bytes hex, expires_at = now + 1 hour). **Display** the reset link on the same page (e.g. "Reset link: [base URL]/recover.php?token=… — copy and open; link expires in 1 hour"). Do not branch on "if email exists"; there is no email in v2.5.
3. User opens link; GET shows form "New password". POST: validate token (exists, not expired), update user passphrase_hash, delete token, redirect to login.

**Invite registration:**

1. User visits `/register.php?invite=CODE`. GET: validate code (exists, used_at IS NULL); if invalid, show error or redirect to register without invite.
2. POST register: validate invite again; create user; set invite_codes.used_by_user_uuid = new user uuid, used_at = now. If registration required invite, reject if no valid code.

**Auth admin:**

1. Admin GET `/admin/users.php`: list users (paginated), columns: username, uuid, role, banned, created_at.
2. Admin clicks user → GET `/admin/users.php?username=…`: show user detail + actions (Ban, Set role to staff, Grant seller). POST: apply action (update users.banned, users.role; or create store + store_users for "grant seller").
### 2.7 Security

- Reset token: single-use, short expiry (e.g. 1 hour); secure random token.
- Invite codes: don't expose full list to non-admin; validate on server.
- Auth admin: restrict to role admin only (staff do not manage users; escalate to admin). No impersonate in v2.5; if added later, log in audit_log.

### 2.8 Files to Create/Modify

| Action | File |
|--------|------|
| Create | `app/public/recover.php` — request + reset (same script: step 2 when token present). |
| Modify | `app/public/register.php` — accept invite param, validate and consume invite code. |
| Create | `app/public/admin/users.php` — list users; user detail + ban/staff/seller. No impersonate. |
| Modify | `app/public/includes/Schema.php` — add createPasswordResetTokens(), createInviteCodes(). |
| Modify | `app/public/schema.php` (or migration) — run new schema. |

### 2.9 Edge Cases

- Recovery: unknown username → show generic "If that user exists, a link was sent" (no user enumeration).
- Invite: multiple use — one code = one use (used_at set once).
- Grant seller: if user already has store, "Grant seller" is no-op or show "Already has store".
