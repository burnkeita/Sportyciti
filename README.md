# SportyCiti.com

Ghanaian sports news and insights, with an international outlook. The platform combines a news publication, a newsroom CMS, live football scores and match data, and SportyCiti Insights analysis.

> **Scope of this phase:** news and sports information only. There are no betting, gambling, wallet, odds or bet-slip features. The modular structure leaves room to add separately licensed services later without touching the news platform.

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 (PHP 8.3+) |
| Database | MySQL 8 / MariaDB 10.4+ (full-text search). SQLite in-memory is used for tests |
| Public UI | Blade, Livewire 4, Alpine.js (bundled with Livewire), Tailwind CSS 4 + Typography, self-hosted Inter / Plus Jakarta Sans |
| Admin / CMS | Filament 5 |
| Auth | Fortify for readers, Filament login with authenticator-app 2FA for staff, Spatie Permission for roles |
| Cache / queue | Database drivers locally, Redis in production (via `predis`, so no PHP extension is needed) |
| Sports data | API-Football v3 adapter, plus a clearly labelled fictional demo provider |
| Backups | spatie/laravel-backup |

## Quick start (local / XAMPP)

```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
# create the database named in .env (default: sportyciti), then:
php artisan migrate --seed          # roles, categories, competitions, ad slots, homepage, Super Admin
php artisan storage:link
php artisan football:sync all       # pulls fixtures/tables (demo data if no API key is set)
php artisan serve                   # http://localhost:8000  ·  admin at /admin
```

In the `local` environment, seeding also creates placeholder articles and three sample authors. Every one of them is labelled "Sample…", and none contains factual claims.

Background processes needed for the full experience:

```bash
php artisan queue:work      # image optimisation, emails, data sync jobs
php artisan schedule:work   # scheduled publishing, live score sync, ad stats, backups
```

The seeder prints the Super Administrator's email and a generated password. Set `SUPER_ADMIN_EMAIL` / `SUPER_ADMIN_PASSWORD` in `.env` beforehand if you want to choose them.

## Tests

```bash
php artisan test
```

There are 50 tests covering:

- public pages and SEO output
- editorial workflow and permissions
- every admin screen, and staff 2FA enforcement
- Filament article forms
- API-Football normalisation, rate limiting and the circuit breaker
- the demo provider
- newsletter double opt-in
- comments, bookmarks and ads
- HTML sanitisation

## Documentation

- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md): modules, data model, sports-data pipeline, security
- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md): production setup, workers, cron, Redis, backups, go-live checklist
- [docs/ADMIN_GUIDE.md](docs/ADMIN_GUIDE.md): guide for editors and administrators
- [docs/API.md](docs/API.md): read-only public JSON API

## Data integrity rules

- Statistics come only from the licensed provider, or are entered by an editor with a named source and an explicit "verified" confirmation. Unverified insight data blocks are never rendered.
- Demonstration data uses fictional "Demo …" teams in a fictional league. It is labelled on every page where it appears, and is excluded from search engines.
- Transfer rumours are always labelled and sourced, and fees are shown only when verified.
- Videos and galleries cannot be published without confirming distribution rights.
