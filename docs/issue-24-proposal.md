## Proposal (Issue #24)

**Context:** Code comments reference planning docs with notation like "01 §10", "01 §11", "08.9", "v2.5". We should ensure the referenced docs and section numbers exist and are accurate.

**References found in code:**
| File | Reference | Meaning |
|------|-----------|---------|
| `Views.php` | 01 §10 | "current = latest row in transaction_statuses" → doc 01 = `v2/01-ACCOUNTING-SPECIFICATION.md`, §10 = "Database Views (Current)" (10.1 describes current = latest row). **Accurate.** |
| `StatusMachine.php` | 01 §11 | "Append-only status machine" → doc 01, §11 = "Invariants" (invariant 1: current = latest row, no updates). **Accurate.** |
| `Config.php` | 01 §12 | "Config/settings table" → doc 01, §12 = "Config (Accounting-Relevant)". **Accurate.** |
| `Env.php` | 08.9 | "PHP must NOT load Python-only secrets" → doc 08 = `v2/08-PLANNING-DECISIONS-QA.md`, §8.9 = "External repos follow-up". **Accurate.** |
| `ApiKey.php` | 08.9 | "Rate limit 60/min per key". **Accurate (08.9).** |
| `Session.php` | 08.9 | "SESSION_SALT from env". **Accurate.** |
| `.env.example` | 08.9 | "PHP loads ONLY the vars listed...". **Accurate.** |
| `recover.php` | v2.5 | "no email in v2.5" (product scope). **v2.5 is the planning folder; ref is descriptive, not a section number.** |
| `cron/tasks.py` | 01 §9 | "Cron tasks per 01 §9" → doc 01, §9 = "Cron / Background Tasks". **Accurate.** |
| `cron/tasks.py` / `escrow.py` | v2.5 | "Vendor CMS" / "v2.5" (scope). **Descriptive.** |
| `verification/plan.php` | v2.5 | "no purchase in v2.5". **Descriptive.** |

**Proposed solution:**
1. Add a short doc (e.g. `docs/planning/INLINE-REFS.md`) or section in an existing planning README that lists each inline ref (file, comment, doc/section) and confirms "exists and accurate" or the correction applied.
2. Audit each ref against the actual doc: open `01-ACCOUNTING-SPECIFICATION.md`, `08-PLANNING-DECISIONS-QA.md`, and v2.5 docs; verify section numbers (§9, §10, §11, §12, 8.9) and fix any comment that points to the wrong section.
3. If any ref is stale (e.g. doc renumbered), update the code comment to the correct section or remove the ref.

No production logic changes; documentation and comment-only updates.
