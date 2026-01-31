# Marketplace PHP App (LEMP)

**Sync:** Only **public/** and **db/** are synced to LEMP. No scripts/, no src/, no data/.

**LEMP design (like tmp/technonomicon.net):** URL path = file path. One PHP script per endpoint. Nginx serves `.php` files directly; no front controller for API/admin. API URLs include `.php`: e.g. `/api/stores.php`, `/api/auth-user.php`, `/admin/config.php`. Root `/` is **index.php**; auth pages are **login.php**, **register.php**, **logout.php**.

- **Document root**: Point Nginx at **public/** (see **nginx.conf.example**). No `try_files` to index.php for `/api/` or `/admin/`—those are real files.
- **DB (SQLite)**: **db/** at same level as **public/**; file `db/store.sqlite`. `.env` uses `DB_DSN=sqlite:db/store.sqlite` (path relative to baseDir = app/).
- **Schema**: **public/schema.php** — run via HTTP (GET/POST) or CLI: `php schema.php` from public/ (baseDir = app/). Creates tables, views, seeds config.
- **.env**: In **app/** (same level as public/ and db/). Copy from `app/.env.example` to `app/.env`. PHP loads only DB_*, SITE_*, session/cookie/CSRF salts.
