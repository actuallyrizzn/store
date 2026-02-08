# Inline planning doc references (code → docs)

Code comments refer to planning docs with notation like `01 §10`, `08.9`, `v2.5`. This file verifies each reference (issue #24).

**Convention:** `01` = `docs/planning/v2/01-ACCOUNTING-SPECIFICATION.md`, `08` = `docs/planning/v2/08-PLANNING-DECISIONS-QA.md`. `v2.5` = folder `docs/planning/v2.5/` (scope name, not a section number).

| File | Reference | Doc/section | Verified |
|------|------------|-------------|----------|
| `app/public/includes/Views.php` | 01 §10 | 01-ACCOUNTING §10 "Database Views (Current)" — current = latest row in transaction_statuses | ✓ |
| `app/public/includes/StatusMachine.php` | 01 §11 | 01-ACCOUNTING §11 "Invariants" — append-only, no updates to past rows | ✓ |
| `app/public/includes/Config.php` | 01 §12 | 01-ACCOUNTING §12 "Config (Accounting-Relevant)" | ✓ |
| `app/public/includes/Env.php` | 08.9 | 08-PLANNING-DECISIONS-QA §8.9 "External repos follow-up" — PHP must not load Python-only secrets | ✓ |
| `app/public/includes/ApiKey.php` | 08.9 | 08 §8.9 — rate limit 60/min per key | ✓ |
| `app/public/includes/Session.php` | 08.9 | 08 §8.9 — SESSION_SALT from env | ✓ |
| `app/public/.env.example` | 08.9 | 08 §8.9 — PHP loads only listed vars | ✓ |
| `app/public/recover.php` | v2.5 | Scope: no email in v2.5 (product decision) | ✓ |
| `app/public/verification/plan.php` | v2.5 | Scope: no purchase in v2.5 | ✓ |
| `app/cron/tasks.py` | 01 §9 | 01-ACCOUNTING §9 "Cron / Background Tasks" | ✓ |
| `app/cron/tasks.py` | v2.5 | Scope: Vendor CMS / deposits | ✓ |
| `app/cron/escrow.py` | v2.5 | Scope: v2.5 | ✓ |

All referenced sections exist and match. No updates required.
