# Copilot instructions for Digital Hobby Community Club

## Quick summary ✅

- Minimal, **procedural PHP** app backed by MySQL. No framework.
- Public UI lives under `public/` (user pages) and `public/admin/` (admin pages).
- Reusable helpers and global config live in `includes/` (`config.php`, `functions.php`).
- DB schema: `sql/init.sql`. CLI helpers: `scripts/` (`create_admin.php`, `seed.php`).

---

## Project big picture & how things flow 🔧

- Request → a PHP file in `public/` handles it. Top-of-file pattern: include `includes/header.php` or `includes/config.php` and `includes/functions.php`, then implement logic and HTML.
- Authentication & authorization: `includes/config.php` exports `is_logged_in()` and `is_admin()`; use `require_login()` / `require_admin()` (in `includes/functions.php`) at the top of handlers that need protection.
- Forms follow POST-redirect pattern: set `flash('key', 'message')`, `header('Location: ...')`, then `exit` to avoid duplicate submissions. Flash UI is shown via `includes/header.php`.
- CSRF protection: call `csrf_input()` in forms and verify using `verify_csrf($_POST['csrf_token'])` in POST handlers.
- Data access uses `mysqli` with prepared statements. Keep using prepared statements and `bind_param()`.

---

## Setup & common developer workflows 💡

- Local: copy to Apache doc root (XAMPP htdocs) OR run the built-in server from project root:
  - php -S localhost:8000 -t public/
- DB initialization: import `sql/init.sql` (phpMyAdmin or mysql CLI).
- Create admin quickly via CLI: `php scripts/create_admin.php "Admin" "admin@example.com" "admin123"`.
- Seed demo data: `php scripts/seed.php`.
- There is no automated test suite or build step; use manual verification in browser and DB inspection.

---

## Conventions & patterns to follow (code examples) ✍️

- Protect routes:
  - For user-only pages: `require_login();`
  - For admin-only pages: `require_admin();`
- Example POST handler pattern:
  - Validate input → verify CSRF → run prepared statement → flash success/error → redirect and exit.
  - See `public/actions/join_club.php` and `public/create_club.php` for canonical examples.
- HTML structure: reuse `includes/header.php` and `includes/footer.php` for layout and flash messages. UI uses MDBootstrap via CDN.
- Escaping: use `e($s)` (wraps htmlspecialchars) to escape output when rendering user-provided content.
- Database schema changes: update `sql/init.sql` so fresh installs get the changes.

---

## Integration points & gotchas ⚠️

- Session bootstrap: `session_start()` is in `includes/config.php`. Ensure any script that uses session includes `config.php` first.
- CSRF & session tokens are stored in `$_SESSION` — do not overwrite them inadvertently.
- Passwords: hashed with `password_hash()` and validated with `password_verify()`.
- Some pages use `INSERT IGNORE` (membership insertion) to avoid duplicate constraint errors — follow this pattern when idempotence is needed.
- When adding admin pages, place them in `public/admin/` and use `require_admin()`.

---

## When you add features 👣

- New UI pages go in `public/` (or `public/admin/`).
- Use includes for header/footer and helpers. Follow the POST/redirect/flash/CSRF pattern.
- Keep DB access via prepared statements against `$mysqli` from `includes/config.php`.
- Add any new table DDL to `sql/init.sql`.

---

If any part is unclear or you want instruction examples for a concrete change (add a CRUD page, new API endpoint, or tests), tell me which area and I'll expand these notes.
