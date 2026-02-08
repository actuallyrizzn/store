## Proposal (Issue #22)

**Context:** The code paths that call `User::generateUuid()` in `api/stores.php`, `api/transactions.php`, and `book.php` were fixed in a prior change (issue #2). This issue asks to ensure E2E tests actually hit those paths so regressions are caught.

**Current state:**
- **Stores:** `StoresApiE2ETest::testPostStoresWithSessionAndCsrfSucceeds()` already POSTs to `api/stores.php` with session + CSRF and asserts 200 + `ok` + `uuid`. That path uses `User::generateUuid()` in `api/stores.php`, so store creation via API is covered.
- **Transactions:** `TransactionsApiE2ETest` covers 401/403 (no auth, no CSRF) but has no test that successfully creates a transaction via `POST /api/transactions.php` (session + CSRF + valid `package_uuid`, etc.). So the transaction-creation code path (which uses `User::generateUuid()` in `api/transactions.php`) is not E2E-covered.
- **Book flow:** `book.php` is the web booking flow; E2E that hits `book.php` with a real package would require a full store → item → package setup in test data.

**Proposed solution:**
1. **Stores:** Add one E2E test that, after creating a store via POST `api/stores.php`, calls GET `api/stores.php` and asserts the new store appears (by uuid or storename). This reinforces that the create path is exercised end-to-end.
2. **Transactions:** Add an E2E test that creates a transaction via POST `api/transactions.php` with session + CSRF and valid payload. This requires a valid `package_uuid` (and optionally store_uuid, etc.). Use the test DB bootstrap (which has schema + seed data); create a minimal package (or use a fixture) so that POST has a real package_uuid, then assert 200 and response contains transaction uuid. This will hit the `User::generateUuid()` path in `api/transactions.php`.
3. **Book:** If feasible without disproportionate setup, add a short E2E that POSTs to `book.php` with required params and session and asserts we get a redirect or success (or a known validation error). If the setup is too heavy (many tables, items, packages), document the gap and add a TODO or skip with a comment pointing to this issue.

Implementation will use existing E2E helpers (`loginAs`, `extractCsrfFromBody`, `runRequest`) and the existing test DB state (admin, e2e_customer). For transaction creation we will need at least one package row; we can create it in the test or in bootstrap.
