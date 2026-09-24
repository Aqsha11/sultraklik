# SULTRAKLIK — Laravel 12 + Filament 3 news portal

Indonesian-language news portal (admin UI labels, seed content, and frontend copy are all in Indonesian). `project.md` at repo root is the product blueprint/spec — read it before building new features.

## Commands
- Full first-time setup: `composer run setup` (composer install → copy `.env` → key:generate → migrate → npm install → build).
- Dev servers all at once: `composer run dev` (concurrently: `php artisan serve`, `queue:listen`, `pail`, vite). Frontend only: `npm run dev`.
- Seed data: `php artisan db:seed` (users, categories, regions, sample articles, pages, breaking news). Tests and local dev both depend on `DatabaseSeeder`.
- Tests: `composer run test` (config:clear + `php artisan test`). Single test: `php artisan test --filter=PublicPagesTest`. phpunit.xml forces in-memory SQLite; tests use `RefreshDatabase` and seed `DatabaseSeeder` in setUp.
- Format: `vendor/bin/pint` (only linter; no CI, no ESLint/Prettier).

## Environment / DB
- `.env` uses **MySQL** — `DB_DATABASE=sultraklik`, host 127.0.0.1, root/no password. MySQL must be running before `migrate`/`db:seed`. `.env.example` defaults to SQLite and does not match `.env`; don't trust it.
- `php artisan storage:link` is required: image URLs are served as `/storage/...` and stored on the `public` disk (`storage/app/public`).

## Architecture gotchas
- Article URLs are slugs at root via a catch-all `Route::get('/{article:slug}')` — it is intentionally the LAST route in `routes/web.php`. Any new system URL (admin, search, kategori, sultra, page, feeds) must be registered before it or it will be captured as an article slug. Region pages are `/sultra/{region:slug}`.
- Roles are a plain `role` ENUM column on `users` (`super_admin` / `admin` / `editor` / `reporter`) — NOT Spatie Permission, despite what `project.md` suggests. Guard access with `User::isAdmin()`/`isEditor()`/`isSuperAdmin()` and follow the pattern in `ArticleResource::getEloquentQuery()` where reporters only see/edit their own articles.
- Filament 3 panel is at `/admin` (`app/Providers/Filament/AdminPanelProvider.php`), auto-discovers Resources/Pages/Widgets. Theme primary color is stored in settings (`Setting::get('theme.primary_color', '#dc2626')`) via `SettingsPage` + `App\Support\ColorPalette`.
- `Setting` model: single table keyed by (`group`, `key`); dotted accessor `Setting::get('general.name')`, flat keys fall back to the `general` group.
- Article editor uses the community package `kahusoftware/filament-ckeditor-field`. Its image upload POSTs to `/upload-image` (auth-only, `app/Http/Controllers/Admin/UploadController.php`) into `storage/app/public/uploads/ckeditor`; keep the `Str::slug(...).'-'.uniqid()` filename convention.
- Tailwind v4, CSS-first (no `tailwind.config.js`; `resources/css/app.css` uses `@import 'tailwindcss'` and `@source` globs). Built via `@tailwindcss/vite`.

## Layout map
- `app/Filament/` — admin CRUD (`Resources/`) + custom pages (`ManageHeadlines`, `SettingsPage`) + widgets (`StatsOverview`).
- `app/Http/Controllers/` — public frontend controllers; `Admin/` holds admin-only endpoints.
- `resources/views/` — public Blade frontend (home, articles, categories, search, SEO/sitemap feeds).
- Seed users: `admin@sultraklik.com`, `editor@sultraklik.com`, `reporter@sultraklik.com`, all password `password` (`database/seeders/UserSeeder.php`).