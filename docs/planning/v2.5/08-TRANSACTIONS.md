## 8. Transactions

### 8.1 Goal

From transaction (payment) page: actions **shipping**, **release**, **cancel**, **complete** (and **Open dispute**) by role and current status. **All allowed actions and semantics are binding in the State & Permission Matrix** ([README](README.md#state--permission-matrix-binding)). No ad hoc checks.

### 8.2 Current State

- `payment.php`: shows order (uuid, status, escrow); buyer or vendor can view. No buttons for shipping/release/cancel/complete.
- `transaction_intents` table exists; StatusMachine and append-only status/shipping exist. Python cron picks intents and performs chain actions.

### 8.3 Target State

- Payment page shows **only** the buttons allowed by the State & Permission Matrix for (current role, payment_status, shipping_status, dispute_status). Use the matrix as the single source of truth.
- **Cancel:** Buyer only when PENDING; staff/admin can cancel when PENDING (override). Vendor has no Cancel button; cron handles "completed but not dispatched in 72h → freeze."
- **Complete ("Confirm received"):** Buyer only when COMPLETED. Effect: set `buyer_confirmed_at` (or equivalent); does **not** by itself trigger release. Release = vendor Release button or auto-release after completed_duration.
- POST handlers: validate (role, payment_status, shipping_status, dispute_status) against matrix; then insert intent or append shipping_status; redirect back to payment page.

### 8.4 Flows

- **Mark shipped:** Allowed per matrix (vendor/staff/admin when COMPLETED). POST → append shipping_status (DISPATCHED, user_uuid).
- **Release:** Allowed per matrix. POST → insert transaction_intents (action=RELEASE); Python cron performs.
- **Cancel:** Allowed per matrix (buyer or staff/admin when PENDING only). POST → insert transaction_intents (action=CANCEL); Python cron performs.
- **Confirm received:** Allowed per matrix (buyer when COMPLETED). POST → set buyer_confirmed_at (add column or status row); no intent.
- **Open dispute:** Link to dispute start; create dispute and link to transaction. **Binding:** Creating a dispute immediately sets transaction status to FROZEN (append transaction_status row).

### 8.5 Files to Create/Modify

| Action | File |
|--------|------|
| Modify | `app/public/payment.php` — add buttons (by role + current_status); add POST handler or include form that POSTs to same page or `payment/action.php`. |
| Create (optional) | `app/public/payment/action.php` — POST only; validate; call StatusMachine/intent; redirect to payment.php?uuid=…. |

### 8.6 Security

- Only buyer or vendor (store_users) or admin can perform actions. Validate current status allows transition (e.g. release only when COMPLETED).
