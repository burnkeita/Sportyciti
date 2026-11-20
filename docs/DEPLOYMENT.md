# Production deployment

## Server requirements

- PHP 8.3+ with these extensions: `pdo_mysql`, `mbstring`, `intl`, `gd` (WebP support), `zip`, `fileinfo`, `bcmath`, `curl`, `openssl`
- MySQL 8 / MariaDB 10.6+
- Redis 6+ (recommended for cache, queue and sessions)
- Node 20+ (build step only)
- Nginx or Apache with the document root set to `public/`
- Supervisor (or systemd) for queue workers, plus cron

## Deploy steps

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan db:seed --force        # first deploy only (roles, categories, competitions, Super Admin)
php artisan storage:link           # first deploy only
php artisan optimize               # config, route, event and view caches
php artisan filament:optimize      # Filament component and icon caches
php artisan queue:restart
```

## Production `.env` essentials

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sportyciti.com
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_SECURE_COOKIE=true
REDIS_CLIENT=predis
SPORTS_DATA_PROVIDER=api_football
SPORTS_DATA_DEMO_FALLBACK=false   # never show demo data in production
API_FOOTBALL_KEY=...              # or set it in Admin → Football → Sports API settings
API_FOOTBALL_RPM=300              # match your plan
MAIL_MAILER=smtp                  # plus your SMTP credentials
BACKUP_DISKS=s3                   # off-site backups (configure the s3 disk)
TRUSTED_PROXIES=*                 # if behind a load balancer or Cloudflare
STAFF_MFA_REQUIRED=true           # staff must set up an authenticator app (default in production)
DB_DUMP_BINARY_PATH=              # only if mysqldump is not on PATH
```

## Workers and scheduler

Cron, for the `www-data` user:

```
* * * * * cd /var/www/sportyciti && php artisan schedule:run >> /dev/null 2>&1
```

Supervisor program:

```ini
[program:sportyciti-worker]
command=php /var/www/sportyciti/artisan queue:work redis --sleep=1 --tries=3 --max-time=3600
numprocs=2
autostart=true
autorestart=true
user=www-data
stopwaitsecs=360
```

## Nginx notes

- Serve `/build/` and `/storage/` with long cache headers (`expires 1y` for `/build/`).
- Enable gzip or brotli.
- Do **not** serve a static `robots.txt`. The app generates it, and it blocks crawling until `APP_ENV=production` and indexing is enabled in Site settings.
- The site works behind a CDN. Asset URLs are versioned, and images use `srcset`.

## Backups

The schedule runs:

- `backup:clean` at 01:30
- `backup:run` at 02:00 (database plus project files, including `storage/app/public` uploads)
- `backup:monitor` at 08:00

Set `BACKUP_DISKS` to an off-site disk and `BACKUP_NOTIFICATION_EMAIL`. `mysqldump` must be on the server's PATH, or set `DB_DUMP_BINARY_PATH`. A local test restore of a backup into a separate database was verified on 15 Nov 2026; repeat it on the production server before launch.

## Monitoring

- `/up` is the health-check endpoint.
- Errors are logged to `storage/logs`. Configure `LOG_CHANNEL` (e.g. `daily` or a log service) and an error tracker.
- Admin dashboard: Sports API status, sync failures in the last 24 h, and pending editorial approvals.
- Admin → Football → Sync logs lists every provider call with its status and duration.

## Go-live checklist

- [ ] `APP_DEBUG=false`, HTTPS enforced, `SESSION_SECURE_COOKIE=true`
- [ ] Change the Super Administrator password. Create named staff accounts with the right roles.
- [ ] With `STAFF_MFA_REQUIRED=true`, each staff member is asked to scan a QR code with an authenticator app (Google Authenticator, Microsoft Authenticator, 1Password…) at first sign-in. Ask them to store their recovery codes safely.
- [ ] Remove the sample content and sample authors if the database was seeded locally.
- [ ] League IDs were verified against API-Football on 15 Nov 2026. Re-check each competition's current season in Admin → Football → Competitions at the start of every season, and make sure your API-Football plan covers current seasons (the free plan only covers 2022–2024).
- [ ] Set `SPORTS_DATA_DEMO_FALLBACK=false`. Run `php artisan football:sync all` and check Sync logs.
- [ ] Turn on indexing in Admin → Site → Site settings → SEO. Submit `/sitemap.xml` and `/news-sitemap.xml` in Google Search Console, and apply to Google News.
- [ ] Add the AdSense publisher ID (if used) and social links.
- [ ] Have the privacy policy, terms and cookie policy reviewed by legal counsel (Ghana Data Protection Act, 2012).
- [ ] Confirm the queue worker, scheduler and backups are running.
