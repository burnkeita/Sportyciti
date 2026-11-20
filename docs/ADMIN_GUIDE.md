# SportyCiti newsroom guide

This guide is for editors, journalists and administrators. Sign in at **/admin** with your staff account. Readers' accounts cannot open the admin panel.

**Two-factor authentication.** On the live site you'll be asked to set up an authenticator app at first sign-in: scan the QR code, enter the 6-digit code, and save your recovery codes somewhere safe. If you lose your phone, sign in with a recovery code, then set up 2FA again under your profile (avatar → Profile).

## Writing an article

1. **Newsroom → Articles → New article.**
2. **Headline.** The URL slug fills in automatically. Avoid changing the slug after publication, because it breaks existing links.
3. **Standfirst/excerpt.** One or two sentences, shown under the headline and on cards.
4. **Body.** Use H2/H3 for sub-headings. Images pasted or attached here are stored automatically.
5. **Media.** Pick or upload a **featured image**. Alt text and rights confirmation are required. Add a credit when you have one.
6. **Classification:**
   - **Type:** news, match report, interview, opinion, feature, transfer story or SportyCiti Insight.
   - **Category and subcategory:** for example *Ghana Sports → Black Stars*. The subcategory decides the label readers see.
   - **Tags:** add club, player and topic tags. Team pages use tags that exactly match the team name.
   - **Linked match (optional):** connects the story to the match centre and enables the data-feed insight components.
7. **Search & social (optional):** SEO title, meta description, social image, canonical URL (syndicated content only), and a noindex switch.

### Statuses

| Status | Meaning | Who can set it |
|---|---|---|
| Draft | Work in progress | Everyone |
| In review | Ready for an editor | Everyone (*Submit for review*) |
| Scheduled | Goes live automatically at the publication date | Editors and above |
| Published | Live on the website | Editors and above (*Approve & publish*) |
| Archived | Removed from the site, kept in the CMS | Editors and above |

- Contributors and journalists can edit their own articles only while they are *Draft* or *In review*. After publication, only editors can change them.
- Editors can **return** an article to its author with notes. The author sees the notes in the *Publishing* panel.
- The **Preview** button opens a private one-hour link for unpublished stories.
- **Revision history** at the bottom of the edit page lists every saved change. Use *Restore* to roll back the headline, excerpt, body and insight components.

### Featured, breaking and sponsored

- **Featured** stories lead the homepage. **Breaking** stories appear in the orange ticker, optionally until a set time. Editors and above can set both.
- **Sponsored content** must have the sponsor named. It is always labelled "Sponsored" on the website and must not be presented as independent journalism.

## SportyCiti Insights

Set **Type = SportyCiti Insight** and choose an **insight type** (tactical breakdown, match preview, etc.). Then add components in *SportyCiti Insight components*:

| Component | Use |
|---|---|
| Key takeaways | 2–8 bullet points |
| Statistics table | Up to 4 columns of figures |
| Team comparison | Side-by-side bars for two teams |
| Player statistics | Stat tiles for one player |
| Bar chart | Simple labelled values |
| Tactical formation diagram | Formation plus 11 players (goalkeeper first) |
| Match information, recent form, head-to-head | Filled automatically from the data feed when a match is linked |
| Expert commentary | A quoted opinion |
| Supporting references | Links to sources |

**Never invent statistics.** Every data component requires a *Data source*, and you must tick *"I confirm these figures come from the stated source and have been checked by an editor"*. Components that are not verified **do not appear** on the website.

## Transfers

**Football → Transfers & rumours**

- Use **Rumour** until a club or governing body officially announces the move. Rumours always display a "Rumour" label, the named source and its reliability.
- Enter a **fee** only if it is reported, and tick **Fee verified** only when it is officially confirmed. Unverified fees display as "Reported fee unverified".
- Link the related article so readers can read the full story.

## Football data

- **Competitions:** enable or disable competitions, mark them as featured, reorder them (drag), and set the provider league ID and current season. *Refresh data* queues a fixtures or table sync. Update each competition's season at the start of a new season.
- **Fixtures & featured matches:** read-only provider data. Use the *Featured* toggle to promote a match to the homepage scores strip. *Refresh* re-syncs one match.
- **Sports API settings:** provider, encrypted API key, plan rate limit, demo fallback, *Sync now*, circuit-breaker reset, and quota status.
- **Sync logs:** every provider call with its result. *Skipped* usually means the rate limit was reached, which is normal. *Failed* needs attention; the message explains why (for example, a plan that doesn't cover the current season).

Fixture data is never typed in by hand. If a match is wrong, check the provider and use *Refresh*.

## Multimedia

- **Videos:** paste a YouTube link, or upload MP4/WebM. Confirm that SportyCiti holds distribution rights. **Do not publish match footage without a licence.**
- **Galleries:** add photos (with captions), reorder them by dragging, and confirm photo rights.

## Comments

**Newsroom → Comment moderation** opens on *Pending* comments by default. Approve, reject or mark as spam, individually or in bulk. Only signed-in readers with a verified email can comment. Site settings can auto-approve readers with 5 or more approved comments.

## Homepage

**Site → Homepage sections:** drag to reorder, toggle visibility, and change how many items each section shows. Section types are: top stories, latest, category blocks, insights, match previews, live scores, trending, videos, transfers, newsletter, and ad slots.

## Advertising

- **Placements** are the slots built into the theme: header, homepage banner, sidebar, in-article, below article, mobile sticky and football banner.
- **Campaigns:** choose a placement and a type:
  - **Image:** desktop and mobile creative plus a destination URL. Clicks are tracked.
  - **AdSense unit:** set the publisher ID in Site settings first.
  - **Custom HTML:** trusted partners only.

  You can also set start and end dates and a priority (higher priority wins, and ties rotate). Impressions, clicks and CTR are reported in the list.
- Gambling or betting advertising is not accepted.

## Users and roles (administrators)

**Administration → Users & authors:** create staff accounts and assign roles. Turn on *Show public author page* and fill in the photo, bio, specialisation and social links to give an author a public profile at `/author/{slug}`. Deactivate staff who leave rather than deleting them, so that their article history is kept.

Every change to articles, users, settings, ads, transfers and competitions is recorded in **Administration → Audit log**, along with sign-ins.
