# Public API v1 (read-only)

- **Base URL:** `https://sportyciti.com/api/v1`
- **Authentication:** none. Every endpoint is read-only and returns published, public data.
- **Rate limit:** 60 requests per minute per IP. Exceeding it returns HTTP 429.
- **Format:** JSON. Times are ISO-8601 in UTC.

## News

### `GET /articles`

Lists published articles, newest first, with pagination.

| Query parameter | Description |
|---|---|
| `category` | Category slug (includes its subcategories), e.g. `ghana`, `black-stars` |
| `q` | Search terms |
| `per_page` | 1–50 (default 20) |

```json
{
  "data": [{
    "id": 12, "title": "…", "slug": "…", "url": "https://sportyciti.com/news/…",
    "excerpt": "…", "type": "news", "insight_type": null,
    "category": {"name": "Ghana Sports", "slug": "ghana"},
    "subcategory": {"name": "Black Stars", "slug": "black-stars"},
    "author": {"name": "…", "url": "…"},
    "image": {"url": "…", "alt": "…", "credit": "…"},
    "is_sponsored": false, "reading_time": 4, "published_at": "2026-11-15T10:00:00+00:00"
  }],
  "links": {}, "meta": {}
}
```

### `GET /articles/{slug}`

Returns a single article. The response includes `body` (sanitised HTML), `tags` and `updated_at`.

### `GET /categories`

Returns the navigation tree of top-level categories and their children.

## Football

Every football response includes `"demo": true|false`. When it is `true`, the data is fictional demonstration data and must not be shown to end users as real results.

| Endpoint | Description |
|---|---|
| `GET /football/live` | Matches currently in play |
| `GET /football/fixtures?date=YYYY-MM-DD&competition={slug}` | Fixtures on a UTC date (default: today) |
| `GET /football/competitions` | Active competitions with their current season |
| `GET /football/competitions/{slug}/standings` | Current table, grouped by group name (an empty key means a single table) |

Example fixture:

```json
{"id": 86, "url": "…", "competition": "Ghana Premier League", "kickoff_at": "…", "status": "2H", "elapsed": 67,
 "home": {"name": "…", "score": 1, "logo": "…"}, "away": {"name": "…", "score": 0, "logo": null}}
```

Status codes follow the API-Football convention:

| Group | Codes |
|---|---|
| Not started | `NS`, `TBD` |
| In play | `1H`, `HT`, `2H`, `ET`, `P` |
| Finished | `FT`, `AET`, `PEN` |
| Off | `PST` (postponed), `CANC` (cancelled), `ABD` (abandoned) |
