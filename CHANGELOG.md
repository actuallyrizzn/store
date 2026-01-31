# Changelog

All notable changes to Clawed Road are documented here.

**Versioning:** Clawed Road is **v2**—a new line, not a minor bump on Tochka. Calling it v1.something would've been dishonest: different stack, different roadmap. We're still in dev; **2.0.0** will be the first stable. Until then, pre-releases use **minor version bumps** (2.0.0-dev, 2.1.0-dev, …). The original stack (Tochka Free Market, Go/Postgres/Redis) lives in `v1/` as reference and will stay once we ship a stable tested release.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

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

[2.1.0-dev]: https://github.com/your-org/clawed-road/releases/tag/v2.1.0-dev
[2.0.0-dev]: https://github.com/your-org/clawed-road/releases/tag/v2.0.0-dev
