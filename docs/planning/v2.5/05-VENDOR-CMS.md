## 5. Vendor CMS

### 5.1 Goal

- **Deposits:** List my stores' deposits; add deposit (create address/record); withdraw (request withdraw; Python cron may perform).
- **Item edit:** Form to edit item (name, description, store_uuid); owner or store member only.
- **Item delete:** Soft-delete (set items.deleted_at).

### 5.2 Current State

- API: `api/deposits.php`, `api/items.php` exist. No web UI for deposits or item edit/delete.

### 5.3 Target State

- Web: deposits list, add, withdraw; item edit form; item delete button (or action on edit page).

### 5.4 Schema

- **Deposits / deposit_history:** Already exist. Add deposit = PHP creates row with store_uuid, **sends** chain_id (and currency/crypto per existing schema); Python defines allowed options dynamically; default ETH mainnet if none defined. PHP sets address = NULL; Python cron fills address. Withdraw = `deposit_withdraw_intents`; Python cron performs (see **5.4.1 Withdraw binding**). Keep schema consistent with v2; no duplicate columns or duplicate v2 logic.
- **Items:** Already has deleted_at. Soft-delete = set deleted_at = now.

### 5.4.1 Deposit withdraw (binding)

Implementers must follow this in §5; do not build a withdraw form with a free-text address field.

- **Store must have `stores.withdraw_address`.** One EVM address per store; withdrawals from that store's deposits go **only** to this address. Store owner sets it in store settings. If the store has no `withdraw_address`, **reject** the withdraw request (require store owner to set it first).
- **Owner only** can request withdraw. User must have `store_users` row for that store with `role = 'owner'`. Collaborators may list/add deposits but **cannot** create withdraw intents.
- **System sets `to_address`.** When creating a withdraw intent, PHP sets `to_address = (SELECT withdraw_address FROM stores WHERE uuid = (SELECT store_uuid FROM deposits WHERE uuid = ?))`. **No user input** for address. The withdraw UI must **not** let the user type an arbitrary address.
- **If missing `withdraw_address` → reject.** Return an error (e.g. "Set withdraw address in store settings first").

### 5.5 URLs / Scripts

| Page | URL | Method |
|------|-----|--------|
| Deposits list | `/deposits.php` | GET — list deposits for my stores (via store_users). |
| Add deposit | `/deposits/add.php` or `/deposits.php?action=add` | GET form, POST — create deposit (call API or duplicate logic). |
| Withdraw | `/deposits/withdraw.php?uuid=…` | GET + POST — request withdraw for deposit uuid. |
| Item edit | `/item/edit.php?uuid=…` | GET form, POST save. |
| Item delete | POST to `/item/edit.php?uuid=…&action=delete` or `/item/delete.php` | POST — set deleted_at. |

### 5.6 Implementation

- Deposits: reuse API logic (include API helpers or internal functions). List: JOIN deposits with store_users WHERE user_uuid = current.
- Item edit: load item; check store membership; form name, description; POST update items SET name, description, updated_at. Delete: POST set deleted_at.

### 5.7 Files to Create/Modify

| Action | File |
|--------|------|
| Create | `app/public/deposits.php` — list; optional add form on same page. |
| Create | `app/public/deposits/add.php` — add deposit. |
| Create | `app/public/deposits/withdraw.php` — withdraw request. |
| Create | `app/public/item/edit.php` — edit + delete action. |
