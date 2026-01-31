# Changelog

All notable changes to Clawed Road are documented here.

**Versioning:** Clawed Road is **v2**—a new line, not a minor bump on Tochka. Calling it v1.something would've been dishonest: different stack, different roadmap. We're still in dev; **2.0.0** will be the first stable. Until then, pre-releases use **minor version bumps** (2.0.0-dev, 2.1.0-dev, …). The original stack (Tochka Free Market, Go/Postgres/Redis) lives in `v1/` as reference and will stay once we ship a stable tested release.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

---

## [2.3.0-dev] - 2026-01-31

### Added

- **E2E coverage at all user levels** — Full-stack E2E tests (`app/tests/E2E/FullStackE2ETest.php`) for anonymous, customer (session), vendor (store + item), and admin: public pages and API 401s when unauthenticated; payments, create-store, API keys/stores/items/transactions/deposits/disputes with session; admin dashboard, config, tokens with admin session; customer → admin config 403; book/payment 404 for invalid ids. **149 tests** (Unit + Integration + E2E).
- **Session auth in E2E runner** — `app/tests/run_request.php` accepts `cookies` in the request JSON and adds `session_name` / `session_id` to the response when a session is active (so login/register work in CLI where `headers_list()` is empty). `E2ETestCase::loginAs()` and `parseCookiesFromResponse()` for session-based flows.
- **Admin user seed** — Optional `ADMIN_USERNAME` / `ADMIN_PASSWORD` in `.env`; schema and test bootstrap create or update that user as admin for dev/demo and E2E.
- **Admin dashboard (HTML)** — `app/public/admin/index.php`: config table and accepted tokens list; admin-only; redirects to login when not authenticated.
- **Create store (vendor) page** — `app/public/create-store.php` and form: storename, description, vendorship agreement; session required; redirects to store on success. Header links: "Create store" when logged in, "Admin" when role is admin.

### Changed

- **E2E expectations** — Index and logout assert 302 (no Location in CLI). Login and register accept `session_name`/`session_id` from response when Set-Cookie is unavailable. Register success asserts 302.
- **Test bootstrap** — Seeds admin user after schema/config so E2E can log in as admin. Test `.env` includes `ADMIN_USERNAME` and `ADMIN_PASSWORD`. `Env` allows `ADMIN_USERNAME` / `ADMIN_PASSWORD`.

### Fixed

- **payments.php / payment.php** — Use view column `updated_at` for ORDER BY and `uuid` for WHERE (not `max_timestamp` / `transaction_uuid`); fixes 500 when viewing My orders.
- **admin/index.php** — Cast `chain_id` and `symbol` to string before `htmlspecialchars()` (SQLite can return int).
- **api/items.php** — Use `User::generateUuid()` instead of `$userRepo->generateUuid()`.

---

## [2.2.0-dev] - 2026-01-31

### Added

- **Python SDK** — `sdk/` package (`marketplace-sdk`) for the Marketplace REST API: API key and session auth, all endpoints (health, stores, items, transactions, keys, deposits, disputes, admin config/tokens). Typed exceptions (ValidationError, UnauthorizedError, RateLimitError, etc.). Install: `pip install -e sdk`. See [sdk/README.md](sdk/README.md).
- **SMCP plugin** — `smcp_plugin/marketplace/` MCP plugin exposing marketplace as tools (e.g. `marketplace__list-stores`, `marketplace__create-transaction`). Commands: health, list-stores, list-items, get-auth-user, list-transactions, create-store, create-item, create-transaction, list-keys, create-key, revoke-key, list-deposits, list-disputes. Uses SDK; installable into Sanctum SMCP `plugins/`. See [smcp_plugin/marketplace/README.md](smcp_plugin/marketplace/README.md) and [INSTALL.md](smcp_plugin/marketplace/INSTALL.md).
- **Agents / SDK / MCP docs** — [docs/AGENTS-SDK-SMCP.md](docs/AGENTS-SDK-SMCP.md): intro to SDK, SMCP plugin, and how to run the official **Sanctum SMCP** server ([sanctumos/smcp](https://github.com/sanctumos/smcp)) with SSE or STDIO so any MCP-compatible agent (Letta, Claude Desktop, Cursor, etc.) can use marketplace tools.

### Changed

- **Documentation location** — All app docs moved to workspace root `docs/app/`: main doc as [docs/app/README.md](docs/app/README.md), INDEX, REFERENCE, ARCHITECTURE, API_GUIDE, DATABASE, DEPLOYMENT, DEVELOPER_GUIDE, CHANGELOG. Removed `app/DOCUMENTATION.md`, `app/DOCUMENTATION_INDEX.md`, and `app/docs/`. [docs/README.md](docs/README.md) indexes planning and app; single docs entry point.
- **Root and app READMEs** — Conspicuous **SDK & MCP (Agents)** section in root README with table (SDK, SMCP plugin, AGENTS-SDK-SMCP doc) and link to Sanctum SMCP. Docs table updated with Agents/SDK/MCP, SDK, and SMCP plugin. [docs/README.md](docs/README.md) and [app/README.md](app/README.md) link to agents/SDK/MCP; [docs/app/README.md](docs/app/README.md) adds “Integrating with agents (SDK & MCP)” subsection.

---

## [2.1.0-dev] - 2026-01-31

### Added

- **Documentation (app/)** — Full docs for the PHP/Python app: `app/DOCUMENTATION.md` (overview, quick start, API reference, schema, security, deployment, troubleshooting); `app/docs/` with ARCHITECTURE.md, API_GUIDE.md, DATABASE.md, DEPLOYMENT.md, DEVELOPER_GUIDE.md, README index, CHANGELOG; `app/DOCUMENTATION_INDEX.md` for navigation. README.md updated with links and quick reference.
- **PHP test suite** — Unit, integration, and E2E tests for the PHP side. PHPUnit 10.5 in `app/` with `composer.json`; `app/phpunit.xml` (Unit, Integration, E2E suites, coverage config). Unit tests for Env, Db, User, Session, ApiKey, Config, StatusMachine, bootstrap (`getApiKeyFromRequest`), api_helpers. Integration tests for Schema, Views, Config. E2E tests via `tests/run_request.php` (request file–based runner) for index, login, register, logout, stores, items, transactions, auth-user, deposits, disputes, admin config/tokens, schema. **109 tests, 205 assertions.** `app/tests/README.md` for run instructions; coverage requires PCOV or Xdebug.
- **Db :memory: support** — `app/public/includes/Db.php` accepts `sqlite::memory:` DSN for tests (path not prefixed with baseDir).

### Changed

- **app/README.md** — Expanded with overview, architecture, quick start, directory structure, config summary, documentation links, security notes.

---

## [2.0.0-dev] - 2025-01-31

First changelog entry. Clawed Road is **in development**—not yet stable. **2.0.0** will be the first stable release once tested and shippable. This entry documents the current state: battle-tested marketplace logic from Tochka, re-implemented for agents and exit.

### Added

- **Web app (PHP/LEMP)** — Plain PHP in `app/public/`; Nginx document root. Env, Db, Schema, Config, User, Session, Router, ApiKey, StatusMachine, Views under `app/public/includes/`.
- **Database** — Portable schema (SQLite for MVP, MariaDB for prod). Schema and views in `Schema.php` / `Views.php`; `schema.php` (HTTP or CLI) creates tables and seeds config.
- **Auth** — Username/password (bcrypt), PHP sessions. No PGP or 2FA in MVP.
- **API keys** — Per-user API keys for programmatic access; key inherits user role (admin/vendor/customer). 60 requests/minute rate limit.
- **EVM-only payments** — Ethereum + admin-configurable ERC‑20 tokens. Alchemy API for chain access. HD-derived escrow addresses (Python, `eth-account`).
- **Python cron** — Scheduled crypto tasks in `app/cron/`: escrow derivation, balance checks, transaction status updates. Cron runs and exits; no long-running daemon. DB as contract between PHP and Python.
- **Status machine** — Append-only transaction lifecycle (PENDING → COMPLETED → RELEASED or CANCELLED/FAILED/FROZEN). Intent/state written by PHP; Python cron performs chain actions.
- **Admin panel** — Config defaults and accepted-token management (`/admin/config`, `/admin/tokens`).
- **REST API** — Endpoints for stores, items, packages, transactions, deposits, disputes. Key-authenticated; agent-first.
- **Planning docs** — Accounting spec, EVM design, auth/API keys, LEMP+Python architecture, and binding Q&A in `docs/planning/`.
- **Dual license** — Code: AGPL-3.0. Non-code (docs, images, media): CC-BY-SA 4.0.

### Changed

- **Stack** — Go → plain PHP. Postgres/Redis → SQLite (MVP) / MariaDB (prod). Single DB; no Redis in MVP.
- **Payments** — Bitcoin + Payaka Ethereum → EVM-only via Alchemy. No external payment gate; Python cron owns escrow derivation and sends.
- **Crypto boundary** — All key material and chain calls in Python cron only. PHP never touches mnemonic or signs; writes intent to DB.
- **API auth** — Token-in-URL → per-user API key with role inheritance and rate limiting.

### Removed

- **Bitcoin** — No BTC or multisig; EVM only.
- **PGP** — No PGP 2FA, PGP login, or message signing.
- **Tor / dark-web surface** — No onion UX, encrypted messaging, or dark-web–specific copy. Clearnet-oriented deployment.
- **Payaka** — Replaced by direct Alchemy integration and Python cron.
- **Redis** — Sessions and app state via PHP + DB only in MVP.
- **Long-running crypto process** — Go cron/scheduler → Python cron (run-and-exit).

### Legacy

- **v1/** — Tochka Free Market (Go) codebase retained as reference. Not part of the Clawed Road runtime. We'll keep it once we have a stable tested release; until then it's the comparison baseline.

---

[2.3.0-dev]: https://github.com/your-org/clawed-road/releases/tag/v2.3.0-dev
[2.2.0-dev]: https://github.com/your-org/clawed-road/releases/tag/v2.2.0-dev
[2.1.0-dev]: https://github.com/your-org/clawed-road/releases/tag/v2.1.0-dev
[2.0.0-dev]: https://github.com/your-org/clawed-road/releases/tag/v2.0.0-dev
