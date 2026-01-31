# Marketplace PHP App

Plain PHP on LEMP. Document root must point at **`app/public`** (this directory's sibling).

- **Entry point**: `public/index.php`
- **Nginx**: Set `root` to the full path to `app/public`; PHP-FPM for `.php`. See `app/nginx.conf.example`.
- **.env**: Repo root `.env` (from `.env.example`). PHP loads only DB_*, SITE_*, session/cookie/CSRF salts.
