## 4. Store (Vendor)

### 4.1 Goal

- Store settings: see User § Store settings.
- **Reviews page:** List reviews for the store (rater, transaction, score, comment).
- **Warnings page:** List warnings for the store; allow resolve/ack.

### 4.2 Current State

- `store.php` shows store by uuid (storename, description, etc.). No reviews or warnings in schema or UI.

### 4.3 Target State

- Store page: add tab or subpage for Reviews and Warnings. `/store.php?uuid=…&tab=reviews` or `/store/reviews.php?uuid=…`, `/store/warnings.php?uuid=…`.

### 4.4 Schema

**New tables:**

- **reviews**
  - `id` (PK), `transaction_uuid` (FK transactions), `store_uuid` (FK stores), `rater_user_uuid` (FK users, buyer), `score` (INTEGER 1–5 or similar), `comment` (TEXT), `created_at` (TEXT).
  - One review per transaction (buyer rates after complete). Index store_uuid for listing.
- **store_warnings**
  - `id` (PK), `store_uuid` (FK stores), `author_user_uuid` (FK users, staff), `message` (TEXT), `status` (e.g. open, resolved, acked), `created_at`, `updated_at`, `resolved_at` (TEXT nullable).
  - Index store_uuid. Staff create and resolve; vendor can ack only (per §4.4 binding).

### 4.5 URLs / Scripts

| Page | URL | Notes |
|------|-----|--------|
| Store reviews | `/store.php?uuid=…&tab=reviews` or `/store/reviews.php?uuid=…` | List reviews for store; public or store members. |
| Store warnings | `/store/warnings.php?uuid=…` or tab | List warnings; vendor/staff see; staff can add/resolve. |

### 4.6 Implementation

- Add `createReviews()`, `createStoreWarnings()` to Schema. Run migration.
- Reviews: list WHERE store_uuid = ? ORDER BY created_at DESC. "Add review" from transaction page when status = RELEASED (buyer only, one per tx).
- Warnings: list WHERE store_uuid = ?. Create from staff panel; resolve/ack via POST.

### 4.7 Files to Create/Modify

| Action | File |
|--------|------|
| Modify | `app/public/includes/Schema.php` — createReviews(), createStoreWarnings(). |
| Create or modify | `app/public/store.php` — add tabs or links to reviews/warnings; or `app/public/store/reviews.php`, `app/public/store/warnings.php`. |
