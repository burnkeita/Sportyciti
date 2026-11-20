# SportyCiti architecture

## Overview

SportyCiti is a modular monolith. Each module owns its models, services and views, and modules talk to each other only through services, never through each other's controllers or views.

| Module | Main code | Responsibility |
|---|---|---|
| Content | `App\Models\{Article,Category,Tag,Media,ArticleRevision}`, `App\Services\Content\*`, `App\Observers\*` | Articles, categories, tags, media library, editorial workflow, revisions, sanitising, view counting |
| Insights | `Filament\Resources\Articles\Schemas\InsightBlocks`, `resources/views/articles/blocks/*` | Structured analysis components (tables, comparisons, charts, formations, data-feed widgets) |
| Sports data | `App\Services\SportsData\*`, `App\Jobs\SyncFootballData`, `App\Models\{Competition,Season,Team,Player,Fixture,…}` | Provider adapters, sync, caching, rate limits, read queries |
| Football UI | `App\Http\Controllers\Football\*`, `App\Livewire\Football\*` | Live scores, fixtures, results, tables, match centre, teams, transfers |
| Engagement | `App\Livewire\{Comments,BookmarkButton,NewsletterForm}`, `App\Services\Engagement\*` | Comments, bookmarks, favourite sections, newsletter |
| Advertising | `App\Services\Ads\AdService`, `App\Models\{AdPlacement,Advertisement}` | Placements, campaigns, rotation, impression and click tracking |
| SEO | `App\Support\Seo`, `App\Http\Controllers\SeoController`, `resources/views/partials/seo.blade.php` | Meta tags, Open Graph, JSON-LD, sitemaps, Google News sitemap, robots.txt, RSS |
| Admin | `App\Filament\*` | CMS, dashboards, settings |

The read-only API in `routes/api.php` is for future mobile apps. New services, such as premium content or a separately licensed partner, should be added as new modules with their own tables and routes.

## Roles and permissions

`App\Support\Permissions` is the single source of truth. After changing it, run `php artisan db:seed --class=RolesAndPermissionsSeeder`.

| Role | Can do |
|---|---|
| Super Administrator | Everything (via a `Gate::before` bypass), including managing roles |
| Administrator | Everything except role management |
| Editor-in-Chief | Editorial work plus categories, homepage, sports data, newsletter and audit log |
| Editor | Edit any article, publish, schedule and feature it, manage tags, media, comments, multimedia and transfers |
| Journalist | Create articles and edit their own while they are draft or in review, upload media |
| Contributor | Create articles and edit their own while they are draft or in review |
| Moderator | Moderate comments |
| Registered reader (no role) | Bookmarks, interests, comments. No admin access |

Publishing rules are enforced in three places:

1. `ArticleWorkflow::normalise()` runs on every CMS save.
2. `ArticlePolicy` controls who can view, edit and delete.
3. `ArticleObserver::saving()` is a last line of defence that refuses any status change into scheduled, published or archived by a user without the publish permission.

## Editorial workflow

Articles move through these states: **draft → in review → scheduled or published → archived.**

- `published_at` in the future means *scheduled*. `articles:publish-scheduled` runs every minute and releases due articles.
- Each update that changes the headline, excerpt, body, insight blocks or status writes an `article_revisions` row. Revisions can be restored from the CMS.
- The body HTML is sanitised on save using Symfony HtmlSanitizer. Only iframes from YouTube, Vimeo, Instagram and X are allowed.

## Data model (main tables)

- **Content:** `articles` (soft deletes, full-text index on MySQL), `categories` (two levels: `parent_id`, `position`, `is_football`), `tags`/`article_tag`, `media` (WebP variants in JSON), `article_media`, `article_related`, `article_revisions`, `article_views` (daily totals)
- **Sports:** `sports`, `countries`, `competitions` (`provider` + `external_id`, `current_season_year`), `seasons`, `teams`, `competition_team`, `players`, `fixtures`, `fixture_events`, `fixture_lineups`, `fixture_statistics`, `standings`, `transfers`, `sync_logs`
- **Multimedia:** `videos`, `galleries`, `gallery_items`
- **Engagement:** `comments`, `bookmarks`, `category_user` (interests), `newsletter_subscribers`
- **Ads:** `ad_placements`, `advertisements`, `advertisement_daily_stats`
- **Site:** `settings` (cached key/value), `homepage_sections`, `audit_logs`, plus the Spatie permission tables

Every provider-sourced row is unique on `(provider, external_id)`. Data is therefore always upserted rather than duplicated, and its origin can always be traced.

## Sports-data pipeline

```
Scheduler ──► SyncFootballData job ──► SyncRunner (logging, timing, circuit breaker)
                                         └─► FootballSyncService (persists normalised data)
                                               └─► FootballDataProvider (interface)
                                                     ├─ ApiFootballProvider (HTTP, quota, cache)
                                                     └─ DemoProvider (fictional, development only)
Pages / Livewire ──► FootballQuery (reads the database only; never calls the provider)
```

- **Provider choice** (`ProviderResolver`): the admin setting comes first, then `.env`. The API key is stored encrypted in `settings`. The demo fallback is used only when it is enabled, which is the default outside production.
- **Only configured competitions are stored.** Anything else the provider returns is ignored.
- **Rate limits:** a per-minute budget (`API_FOOTBALL_RPM`) is enforced via `RateLimiter`. The daily quota is read from response headers, and syncing pauses when it nears the reserve. Rate-limited runs are logged as *skipped*, not *failed*.
- **Failure recovery:** connection retries, provider error parsing, and a circuit breaker that pauses the provider for 5 minutes after 5 consecutive failures. Status shows on the dashboard and on *Sports API settings*.
- **Caching:** HTTP responses are cached for 20 s (live), 10 min (fixtures) and 30 min (standings). Head-to-head results are cached for 12 h and are not persisted.
- **Schedule:**

  | Job | Frequency |
  |---|---|
  | Live scores | Every minute, but only when a match is in play or about to start |
  | Today's fixtures | Every 15 minutes |
  | Date window | Every 3 hours |
  | Standings | Every 2 hours |
  | Full season | Daily at 03:15 |

  Adjust these in `routes/console.php` to fit your plan's quota.
- **Live UI:** Livewire components poll every 30 s, and only while matches are live or on today's date. Kick-off times are rendered from UTC into the visitor's timezone in the browser. A `tz` cookie lets the server compute local day boundaries.

## Performance

- Navigation, settings, breaking news, homepage section data, standings, sitemaps and the ad pool are cached.
- Composite indexes cover every listing query, e.g. `(status, published_at)` and `(category_id, status, published_at)`.
- Images get responsive WebP variants (480/960/1600) through the queued `OptimiseImage` job, served with `srcset`, explicit dimensions, lazy loading, and `fetchpriority` on the hero.
- View counts are written after the response is sent. Ad impressions are buffered in the cache and flushed every 5 minutes.
- Fonts are self-hosted by the Vite build.

## Security

- CSRF on all forms. Output is escaped by Blade. Editorial HTML is sanitised on save. All queries go through the query builder or Eloquent.
- Rate limits: login 5/min, comments 3/min and 50/day, newsletter 5/hour, API 60/min.
- Security headers: `nosniff`, `SAMEORIGIN`, a strict referrer policy, a Permissions-Policy, and HSTS on HTTPS.
- Sessions are encrypted. Comment IPs are stored only as HMAC hashes.
- `audit_logs` records changes to articles, categories, users, ads, transfers, competitions and settings, plus logins and sync requests.
- Uploads are restricted by type and size and stored on the public disk under date folders.
- The cache only unserialises an explicit allow-list of the app's own classes (`config/cache.php`).
- Ad HTML/JS is accepted only from users with the "manage advertisements" permission.
