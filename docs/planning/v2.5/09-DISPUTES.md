## 9. Disputes

### 9.1 Goal

Start dispute from transaction page; dispute detail (view, claims, status); dispute admin list for staff.

### 9.2 Current State

- Schema: `disputes`, `dispute_claims` exist. API: `api/disputes.php`. No web: start dispute, detail page, admin list.

### 9.3 Target State

- Payment page: "Open dispute" link → form (reason, message) → POST create dispute + first claim. Link dispute to transaction (transactions.dispute_uuid).
- Dispute detail: `dispute.php?uuid=…` — show dispute, claims; buyer/vendor add claim; staff set status, partial refund (per v2/01).
- Admin list: `admin/disputes.php` or `staff/disputes.php` — list all disputes; link to detail.

### 9.4 Schema

- Disputes: ensure transaction_uuid or link via transaction.dispute_uuid. v2 disputes have uuid, status, resolver_user_uuid. Dispute_claims: dispute_uuid, user_uuid, body, etc. Add transaction_uuid to disputes if not present for "start from tx" flow.

### 9.5 URLs / Scripts

| Page | URL | Notes |
|------|-----|--------|
| Start dispute | From payment: link to `/dispute/new.php?transaction_uuid=…` or form on payment. | POST create dispute, set transaction.dispute_uuid, insert first claim. |
| Dispute detail | `/dispute.php?uuid=…` | GET view; POST add claim; staff POST status, partial_refund. |
| Admin list | `/staff/disputes.php` only (staff and admin both use it) | List disputes; link to detail. |

### 9.6 Files to Create/Modify

| Action | File |
|--------|------|
| Create | `app/public/dispute/new.php` — form + POST start dispute. |
| Create | `app/public/dispute.php` — detail by uuid; add claim; staff actions. |
| Create | `app/public/staff/disputes.php` — list disputes (staff and admin; no separate admin/disputes.php). |
| Modify | `app/public/payment.php` — add "Open dispute" link when applicable (e.g. COMPLETED/FROZEN). |
