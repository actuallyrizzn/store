## Proposal (Issue #23)

**Context:** `Config::seedDefaults()` uses driver-specific SQL: `INSERT OR IGNORE` for SQLite and `INSERT IGNORE` for MariaDB/MySQL. A unit test already mocks PDO with driver `mysql` to assert the correct statement is used. The issue asks for an **integration** test that runs schema + `Config::seedDefaults()` against both drivers so driver-specific DML bugs are caught with a real DB.

**Current state:**
- `ConfigIntegrationTest` runs against `Db::pdo()` (SQLite in test bootstrap). It has `testSeedDefaultsInsertsAllKeys` (count >= 15) and `testGetSetRoundtrip`.
- Test bootstrap sets `DB_DRIVER=sqlite` and runs schema + views + config seed on SQLite. So SQLite path is already exercised in integration.
- No integration test runs `Config::seedDefaults()` against a real MariaDB/MySQL connection.

**Proposed solution:**
1. Add an integration test that runs `Schema::run()` then `Config::seedDefaults()` against the current `Db::pdo()` (SQLite) and asserts config keys are present and idempotent. This may overlap with existing tests but makes the “both drivers” story explicit.
2. Add a test that, when a MariaDB/MySQL DSN is available (e.g. via `TEST_MARIADB_DSN` or `DB_DRIVER=mariadb` in a separate CI matrix job), creates a PDO to that DSN, runs schema + views + `Config::seedDefaults()`, and asserts no exception and that expected config keys exist. If the env var is not set, the test is **skipped** (e.g. `$this->markTestSkipped('TEST_MARIADB_DSN not set')`). This keeps the default test run (SQLite only) 100% green while allowing CI to run the same test against MariaDB when configured.

No change to production code; only new or extended integration tests in `app/tests/Integration/`.
