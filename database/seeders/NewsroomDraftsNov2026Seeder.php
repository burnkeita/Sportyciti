<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Enums\InsightType;
use App\Jobs\OptimiseImage;
use App\Models\Article;
use App\Models\Category;
use App\Models\Media;
use App\Models\Tag;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Real-news DRAFTS researched on 15 November 2026 from published reports (sources listed per article).
 * Saved as Draft so an editor verifies the facts before publishing. Photos are freely licensed
 * Wikimedia Commons images stored with credit and licence; captions identify file photos as such.
 *
 * Run: php artisan db:seed --class=NewsroomDraftsNov2026Seeder
 */
class NewsroomDraftsNov2026Seeder extends Seeder
{
    public function run(): void
    {
        $desk = User::firstOrCreate(['email' => 'desk@sportyciti.com'], [
            'name' => 'SportyCiti Sports Desk',
            'password' => Str::password(32),
            'job_title' => 'Newsroom',
            'specialisation' => 'Ghanaian and international sport',
            'bio' => 'Reports from the SportyCiti newsroom.',
            'is_public_author' => true,
        ]);
        $desk->forceFill(['email_verified_at' => now()])->save();
        $desk->syncRoles([Permissions::JOURNALIST]);

        $media = $this->media();
        $cat = fn (string $slug) => Category::where('slug', $slug)->firstOrFail();

        foreach ($this->articles() as $a) {
            if (Article::withTrashed()->where('slug', $a['slug'])->exists()) {
                continue;
            }

            $sub = $cat($a['category']);
            $article = Article::create([
                'title' => $a['title'],
                'slug' => $a['slug'],
                'excerpt' => $a['excerpt'],
                'body' => $a['body'],
                'type' => $a['type'],
                'insight_type' => $a['insight_type'] ?? null,
                'insight_blocks' => array_values(array_filter([
                    $a['takeaways'] ?? null,
                    ['type' => 'references', 'data' => ['items' => $a['sources']]],
                ])),
                'status' => ArticleStatus::Draft,
                'category_id' => $sub->parent_id ?? $sub->id,
                'subcategory_id' => $sub->parent_id ? $sub->id : null,
                'author_id' => $desk->id,
                'featured_media_id' => isset($a['image']) ? $media[$a['image']]->id : null,
                'review_notes' => 'Researched from published reports on 15 Nov 2026. Verify facts against the sources listed at the bottom before publishing.',
            ]);

            $article->tags()->sync(collect($a['tags'])->map(fn ($name) => Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id));
        }
    }

    /** @return array<string, Media> */
    protected function media(): array
    {
        $dir = 'media/2026/11/';
        $items = [
            'goal' => ['black-stars-goal-celebration.jpg', 'Black Stars players celebrate a goal', 'Ghana players celebrate a goal (file photo, 2008).', 'Benjamin Mussler / Wikimedia Commons, CC BY-SA 2.0'],
            'essipong' => ['essipong-stadium.jpg', 'Sekondi-Takoradi (Essipong) Stadium', 'The Essipong Sports Stadium in Sekondi-Takoradi (file photo).', 'Oluniyi Ajao / Wikimedia Commons, CC BY-SA 3.0'],
            'training' => ['black-stars-training.jpg', 'Black Stars players during a training session', 'Black Stars in training (file photo, 2008).', 'Benjamin Mussler / Wikimedia Commons, CC BY-SA 2.0'],
            'anthem' => ['black-stars-anthem.jpg', 'Ghana players with hands on their chests during the national anthem', 'Black Stars players during the national anthem (file photo).', 'Benjamin Mussler / Wikimedia Commons, CC BY-SA 2.0'],
            'bq-team' => ['black-queens-zambia-02.jpg', 'Black Queens starting line-up posing for a team photo', 'The Black Queens before a match against Zambia in 2024 (file photo).', 'Esthee2010 / Wikimedia Commons, CC BY-SA 4.0'],
            'bq-action' => ['black-queens-zambia-04.jpg', 'Black Queens players in action against Zambia', 'Black Queens in action against Zambia in 2024 (file photo).', 'Esthee2010 / Wikimedia Commons, CC BY-SA 4.0'],
            'baba-yara' => ['baba-yara-stadium.jpg', 'Baba Yara Sports Stadium in Kumasi', 'Baba Yara Sports Stadium, Kumasi (file photo).', 'Michael Schubert / Wikimedia Commons, public domain'],
            'pochettino' => ['pochettino-2026.jpg', 'Mauricio Pochettino on the touchline', 'Mauricio Pochettino coaching the USA against Australia in June 2026.', 'Bryan Berlin / Wikimedia Commons, CC BY-SA 4.0'],
        ];

        $out = [];
        foreach ($items as $key => [$file, $alt, $caption, $credit]) {
            $out[$key] = Media::firstOrCreate(['path' => $dir.$file], [
                'title' => Str::of($file)->beforeLast('.')->replace('-', ' ')->title(),
                'disk' => 'public',
                'alt_text' => $alt,
                'caption' => $caption,
                'credit' => $credit,
                'rights_confirmed' => true,
            ]);
            if (empty($out[$key]->variants)) {
                (new OptimiseImage($out[$key]->id))->handle();
                $out[$key]->refresh();
            }
        }

        return $out;
    }

    protected function articles(): array
    {
        return [
            [
                'slug' => 'black-stars-beat-somalia-first-win-2027-afcon-qualifiers',
                'title' => 'Black Stars beat Somalia 2-0 for first win of 2027 AFCON qualifying campaign',
                'excerpt' => 'Jonas Adjetey and Felix Afena-Gyan scored at the Essipong Stadium as Ghana finally got off the mark in Group C, despite a missed early penalty from captain Jordan Ayew.',
                'category' => 'black-stars', 'type' => ArticleType::MatchReport, 'image' => 'goal',
                'tags' => ['Black Stars', 'AFCON 2027 qualifiers', 'Somalia', 'Jordan Ayew'],
                'body' => <<<'HTML'
<p>Ghana collected their first points of the 2027 Africa Cup of Nations qualifiers on Thursday, beating Somalia 2-0 at the Essipong Sports Stadium in Sekondi.</p>
<p>The Black Stars went into the Group C fixture bottom of the table after defeats to Côte d'Ivoire and The Gambia in their opening two matches, and the evening started with a setback. Captain Jordan Ayew, back in the squad after injury, saw an early penalty saved by Somalia goalkeeper Ibrahim Hussein.</p>
<p>Ghana broke through on the stroke of half-time. Defender Jonas Adjetey scored in first-half stoppage time, his first goal for the national team. Felix Afena-Gyan doubled the lead shortly after the hour mark to settle the contest.</p>
<p>The win lifts Ghana to three points in Group C, while Somalia remain without a point. Agency reports described the victory as the end of a 37-month winless run in AFCON qualifying for Ghana.</p>
<h2>Second leg on Monday</h2>
<p>The two sides meet again on Monday, 16 November. Somalia have given up home advantage, so the return fixture will also be played in Ghana, offering interim coach Kim Lars Björkegren's side a chance to close the gap on the top two in the group.</p>
HTML,
                'sources' => [
                    ['title' => '3News — Ghana defeat Somalia to record first win of campaign', 'url' => 'https://3news.com/sports/2027-afcon-qualifiers-ghana-defeat-somalia-to-record-first-win-of-campaign'],
                    ['title' => 'SuperSport — Ghana end 37-month winless AFCON run', 'url' => 'https://supersport.com/football/news/bf295db6-520e-406f-a4b3-e765245b38a5/ghana-end-37-month-winless-afcon-run-ivory-coast-senegal-triumph'],
                    ['title' => 'Pulse Nigeria — Ghana 2-0 Somalia', 'url' => 'https://www.pulse.ng/story/ghana-vs-somalia-2-0-afcon-qualifier-result-2026111218461269266'],
                ],
            ],
            [
                'slug' => 'preview-ghana-vs-somalia-afcon-2027-qualifier-second-leg',
                'title' => 'Preview: Ghana look to build momentum in Somalia rematch',
                'excerpt' => 'After ending their wait for a qualifying win on Thursday, the Black Stars face Somalia again on Monday with a top-two finish in Group C back in their sights.',
                'category' => 'match-previews', 'type' => ArticleType::Insight, 'insight_type' => InsightType::MatchPreview, 'image' => 'essipong',
                'tags' => ['Black Stars', 'AFCON 2027 qualifiers', 'Somalia', 'Match preview'],
                'body' => <<<'HTML'
<p>Ghana and Somalia meet for the second time in five days on Monday, 16 November, with the Black Stars aiming to follow up Thursday's 2-0 win and strengthen their position in Group C of the 2027 Africa Cup of Nations qualifiers.</p>
<h2>Where things stand</h2>
<p>Ghana opened the campaign with defeats to Côte d'Ivoire and The Gambia before beating Somalia at the Essipong Sports Stadium in Sekondi, where goals from Jonas Adjetey and Felix Afena-Gyan secured three points. Somalia remain bottom of the group without a point.</p>
<p>Somalia have ceded home advantage for the return fixture, so the Black Stars will again play in front of their own supporters.</p>
<h2>What to watch</h2>
<p>Interim coach Kim Lars Björkegren, who also leads the Black Queens, will want a more convincing performance in front of goal after Thursday's game produced a missed early penalty from captain Jordan Ayew. The returns of Mohammed Kudus and Daniel-Kofi Kyereh give him attacking options.</p>
<p>With only the top two teams in the group qualifying, back-to-back wins would put Ghana firmly back in contention heading into the final rounds of qualifying.</p>
HTML,
                'takeaways' => ['type' => 'key_takeaways', 'data' => ['heading' => 'Key takeaways', 'items' => [
                    ['text' => 'Ghana won the first meeting 2-0 on Thursday, their first win of the campaign.'],
                    ['text' => 'Somalia have ceded home advantage, so Ghana host again.'],
                    ['text' => 'Only the top two in Group C qualify, so back-to-back wins would put Ghana back in the race.'],
                ]]],
                'sources' => [
                    ['title' => 'SuperSport — Ghana end 37-month winless AFCON run', 'url' => 'https://supersport.com/football/news/bf295db6-520e-406f-a4b3-e765245b38a5/ghana-end-37-month-winless-afcon-run-ivory-coast-senegal-triumph'],
                    ['title' => 'Pulse Ghana — Black Stars squad for Somalia qualifiers', 'url' => 'https://www.pulse.com.gh/story/ghana-black-stars-squad-somalia-afcon-qualifiers-2026110915390546376'],
                ],
            ],
            [
                'slug' => 'kim-lars-bjorkegren-names-black-stars-squad-somalia-kudus-ayew-return',
                'title' => 'Kudus and Ayew back as interim coach Björkegren names Black Stars squad for Somalia double-header',
                'excerpt' => 'The Black Queens coach, appointed on an interim basis while the GFA searches for a permanent appointment, named a 26-man squad that includes a first call-up in nearly four years for Daniel-Kofi Kyereh.',
                'category' => 'black-stars', 'type' => ArticleType::News, 'image' => 'training',
                'tags' => ['Black Stars', 'Kim Lars Björkegren', 'Mohammed Kudus', 'Jordan Ayew'],
                'body' => <<<'HTML'
<p>Kim Lars Björkegren has named a 26-man Black Stars squad for Ghana's 2027 Africa Cup of Nations qualifiers against Somalia, with Mohammed Kudus and captain Jordan Ayew among the players returning.</p>
<p>Björkegren, the head coach of the Black Queens, has taken charge of the men's team on an interim basis for the two matches while the Ghana Football Association continues its search for a permanent appointment.</p>
<h2>Returns and recalls</h2>
<ul>
<li><strong>Mohammed Kudus</strong> returns after injury kept the Tottenham midfielder out of Ghana's earlier internationals in 2026.</li>
<li><strong>Jordan Ayew</strong> is back after missing the qualifiers against Côte d'Ivoire and The Gambia with a groin injury.</li>
<li><strong>Daniel-Kofi Kyereh</strong> earns his first call-up in nearly four years after recovering from an ACL injury.</li>
<li><strong>Alidu Seidu</strong> also returns in defence.</li>
</ul>
<p>The squad includes home-based players Isaac Afful of FC Samartex, Musa Hamzata of Asante Kotoko, and Medeama SC pair Etse Dogli and Emmanuel Annor.</p>
<p>The team opened camp in Accra on Sunday, 8 November, ahead of the first match against Somalia at the Essipong Sports Stadium in Sekondi on 12 November. The return fixture follows on 16 November.</p>
HTML,
                'sources' => [
                    ['title' => 'Pulse Ghana — Black Stars squad: Kudus and Ayew return', 'url' => 'https://www.pulse.com.gh/story/ghana-black-stars-squad-somalia-afcon-qualifiers-2026110915390546376'],
                    ['title' => 'Flashscore — Ghana to appoint interim technical team for November qualifiers', 'url' => 'https://www.flashscore.com/news/soccer-africa-cup-of-nations-ghana-to-appoint-interim-black-stars-technical-team-for-november-afcon-qualifiers/pnX9wM7D/'],
                ],
            ],
            [
                'slug' => 'ghana-shortlisted-caf-mens-national-team-of-the-year-2026',
                'title' => 'Ghana shortlisted for CAF Men\'s National Team of the Year after World Cup knockout run',
                'excerpt' => 'The Black Stars are among ten nominees after reaching the Round of 32 at the 2026 World Cup, their best showing since 2010.',
                'category' => 'black-stars', 'type' => ArticleType::News, 'image' => 'anthem',
                'tags' => ['Black Stars', 'CAF Awards', 'World Cup 2026'],
                'body' => <<<'HTML'
<p>Ghana's Black Stars have been shortlisted for Men's National Team of the Year at the 2026 CAF Awards.</p>
<p>The nomination recognises Ghana's return to the knockout stage of the World Cup. At the 2026 tournament, Ghana beat Panama 1-0, drew 0-0 with England and lost 2-1 to Croatia, progressing as one of the best third-placed teams before a 1-0 defeat to Colombia in the Round of 32. It was an improvement on the group-stage exits of 2014 and 2022.</p>
<h2>The shortlist</h2>
<p>Ghana are joined on the ten-team list by Algeria, Cabo Verde, Côte d'Ivoire, DR Congo, Egypt, Morocco, Senegal, South Africa and Tunisia.</p>
<p>The nomination comes during a difficult start to the 2027 Africa Cup of Nations qualifiers, in which Ghana lost their first two matches before beating Somalia on 12 November.</p>
HTML,
                'sources' => [
                    ['title' => 'Pulse Ghana — Ghana shortlisted for 2026 CAF Team of the Year', 'url' => 'https://www.pulse.com.gh/story/ghana-shortlisted-2026-caf-team-of-the-year-2026110414101027235'],
                ],
            ],
            [
                'slug' => 'black-queens-nominated-caf-womens-national-team-of-the-year-2026',
                'title' => 'Black Queens nominated for CAF Women\'s National Team of the Year',
                'excerpt' => 'A quarter-final run at the Women\'s Africa Cup of Nations has earned Ghana a place on a six-team shortlist, with the winner to be named in Lagos on 7 December.',
                'category' => 'black-queens', 'type' => ArticleType::News, 'image' => 'bq-team',
                'tags' => ['Black Queens', 'CAF Awards', 'WAFCON'],
                'body' => <<<'HTML'
<p>Ghana's Black Queens have been nominated for Women's National Team of the Year at the 2026 CAF Awards.</p>
<p>The nomination follows Ghana's run to the quarter-finals of the Women's Africa Cup of Nations in Morocco, where they were eliminated by Malawi.</p>
<p>The Black Queens are on a six-team shortlist with Cameroon, Malawi, Morocco, Nigeria and South Africa. The winner will be announced at the awards ceremony in Lagos on 7 December.</p>
<h2>World Cup play-offs next</h2>
<p>The recognition comes as the Black Queens prepare for the FIFA Women's World Cup inter-confederation play-offs in Morocco, where they will chase a place at the 2027 tournament in Brazil.</p>
HTML,
                'sources' => [
                    ['title' => 'Adomonline — Black Queens nominated for 2026 CAF Women\'s Team of the Year', 'url' => 'https://www.adomonline.com/black-queens-nominated-for-2026-caf-womens-team-of-the-year-award/'],
                    ['title' => 'Pulse Ghana — Black Queens nominated', 'url' => 'https://www.pulse.com.gh/story/black-queens-nominated-2026-caf-womens-team-of-the-year-2026110312394401874'],
                    ['title' => 'Modern Ghana — Black Queens nominated', 'url' => 'https://www.modernghana.com/sports/1541877/black-queens-nominated-for-2026-caf-womens-team.html'],
                ],
            ],
            [
                'slug' => 'black-queens-world-cup-play-offs-rabat-ecuador',
                'title' => 'Black Queens to open World Cup play-off campaign against Ecuador in Rabat',
                'excerpt' => 'Ghana are one of six teams in the preliminary stage of the inter-confederation play-offs for the 2027 Women\'s World Cup in Brazil.',
                'category' => 'black-queens', 'type' => ArticleType::News, 'image' => 'bq-action',
                'tags' => ['Black Queens', 'Women\'s World Cup 2027', 'Ecuador'],
                'body' => <<<'HTML'
<p>The Black Queens will begin their bid to reach the 2027 FIFA Women's World Cup in Brazil against Ecuador at the inter-confederation play-offs in Rabat, Morocco.</p>
<p>Ghana are one of six teams in the preliminary phase, alongside Ecuador, Uzbekistan, Papua New Guinea, Chinese Taipei and South Africa. Each team plays three matches, and the top two advance to the final phase of the play-offs in February 2027, when four more nations join. Three places at the World Cup are available through the play-offs.</p>
<p>Ghana's group opponents are Ecuador, Uzbekistan and Papua New Guinea.</p>
<p><strong>Dates:</strong> reports differ on when the tournament starts — The Ghana Report lists 28 November to 5 December, while other outlets give 24 November as the start date. SportyCiti will confirm the schedule once FIFA publishes the official fixtures.</p>
<p>The 2027 Women's World Cup takes place in Brazil from 24 June to 25 July 2027.</p>
HTML,
                'sources' => [
                    ['title' => 'The Ghana Report — Black Queens to play World Cup play-offs in Morocco', 'url' => 'https://theghanareport.com/sports/black-queens-to-play-world-cup-play-offs-in-morocco/'],
                    ['title' => 'Africa Top Sports — Black Queens open play-off campaign against Ecuador', 'url' => 'https://en.africatopsports.com/black-queens-open-2027-womens-world-cup-play-off-campaign-against-ecuador/'],
                ],
            ],
            [
                'slug' => 'ghana-premier-league-week-11-medeama-go-three-points-clear',
                'title' => 'Ghana Premier League Week 11: Braye brace sends Medeama three points clear',
                'excerpt' => 'Holders Medeama won 2-1 at Young Apostles as Hearts of Oak and Asante Kotoko were both held, and AshantiGold struck in the 89th minute to beat Karela United.',
                'category' => 'ghana-premier-league', 'type' => ArticleType::MatchReport, 'image' => 'baba-yara',
                'tags' => ['Ghana Premier League', 'Medeama', 'Hearts of Oak', 'Asante Kotoko', 'AshantiGold'],
                'body' => <<<'HTML'
<p>Medeama opened a three-point lead at the top of the Ghana Premier League after a 2-1 win at Young Apostles on Sunday, with Kingsley Braye scoring both goals for the champions.</p>
<p>The result took Medeama to 24 points from 11 matches. Hearts of Oak drew 0-0 with Samartex at the Nsenkyire Arena and are now level on 21 points with Port City, who won 3-1 at Debibi United.</p>
<p>Asante Kotoko were held to a goalless draw by GoldStars at the Baba Yara Stadium, while Bechem United climbed to fourth on 19 points with a 1-0 win away to Aduana at Nana Agyemang Badu Park.</p>
<h2>Late winner in Obuasi</h2>
<p>At the Len Clay Stadium, captain Kojo Twumasi scored in the 89th minute to give FC AshantiGold a 1-0 win over Karela United. The result extends AshantiGold's unbeaten home run in league football to 21 matches across this season and their Division One campaign, while Karela are still searching for their first win of the season.</p>
<h2>Week 11 results</h2>
<ul>
<li>Heart of Lions 1-2 Dreams FC (Saturday)</li>
<li>FC AshantiGold 1-0 Karela United</li>
<li>Holy Stars 1-0 Vision FC</li>
<li>Swedru All Blacks 0-0 Berekum Chelsea</li>
<li>Debibi United 1-3 Port City</li>
<li>Young Apostles 1-2 Medeama</li>
<li>Samartex 0-0 Hearts of Oak</li>
<li>Aduana 0-1 Bechem United</li>
<li>Asante Kotoko 0-0 GoldStars</li>
</ul>
<p>Defeat leaves Heart of Lions in the relegation zone.</p>
HTML,
                'sources' => [
                    ['title' => 'Panafrica Football — Ghana Premier League Week 11 round-up', 'url' => 'https://panafricafootball.com/article/ghana-premier-league-week-10'],
                    ['title' => 'GHANAsoccernet — FC AshantiGold 1-0 Karela United match report', 'url' => 'https://ghanasoccernet.com/2026-27-ghana-premier-league-week-11-match-report-fc-ashantigold-1-0-karela-united'],
                ],
            ],
            [
                'slug' => 'manchester-united-appoint-pochettino-carrick-sacked',
                'title' => 'Manchester United appoint Mauricio Pochettino after sacking Michael Carrick',
                'excerpt' => 'The former Tottenham and Chelsea boss takes charge on 18 November after finishing his duties with the USA, with a trip to Anfield first on his fixture list.',
                'category' => 'english-premier-league', 'type' => ArticleType::News, 'image' => 'pochettino',
                'tags' => ['Manchester United', 'Mauricio Pochettino', 'Michael Carrick', 'Premier League'],
                'body' => <<<'HTML'
<p>Manchester United have appointed Mauricio Pochettino as head coach, days after sacking Michael Carrick.</p>
<p>Carrick was dismissed only 15 games into the two-year contract he signed in May, with United in the relegation zone after their worst start to a league season in 40 years.</p>
<p>The club said Pochettino will take up the role from 18 November. He first completes his commitments with the United States national team, whom he will lead in their upcoming matches against Haiti. British media reports say United had to pay compensation to US Soccer to release him from his contract.</p>
<h2>Liverpool first</h2>
<p>Pochettino's first match in charge is against Liverpool at Anfield on Sunday, 22 November.</p>
<p>The Argentine previously managed Southampton, Tottenham Hotspur, Paris Saint-Germain and Chelsea in a career that has included a Champions League final with Tottenham in 2019.</p>
HTML,
                'sources' => [
                    ['title' => 'Sky Sports — Pochettino appointed Manchester United head coach', 'url' => 'https://www.skysports.com/football/news/13610882/mauricio-pochettino-appointed-as-manchester-united-head-coach-after-michael-carricks-sacking-at-old-trafford'],
                    ['title' => 'ESPN — Man United confirm Pochettino as Carrick replacement', 'url' => 'https://www.espn.com/soccer/story/_/id/50469660/man-united-mauricio-pochettino-manager-michael-carrick'],
                    ['title' => 'Al Jazeera — Pochettino announced as new Manchester United manager', 'url' => 'https://www.aljazeera.com/sports/2026/11/13/manchester-united-new-manager-mauricio-pochettino-usa-soccer'],
                ],
            ],
            [
                'slug' => 'nathaniel-suppey-rayton-okwiri-wba-africa-title-nairobi',
                'title' => 'Unbeaten Ghanaian Nathaniel Suppey to fight Rayton Okwiri for WBA Africa title in Nairobi',
                'excerpt' => 'The 24-year-old, who has won nine of his ten professional fights by knockout, will box outside Ghana for the first time on 28 November.',
                'category' => 'boxing', 'type' => ArticleType::News,
                'tags' => ['Boxing', 'Nathaniel Suppey', 'WBA Africa'],
                'body' => <<<'HTML'
<p>Unbeaten Ghanaian boxer Nathaniel Suppey will face Kenya's Rayton Okwiri for the vacant WBA Africa gold title in Nairobi on 28 November.</p>
<p>Suppey, 24, has won all ten of his professional bouts, nine of them inside the distance. The fight on the SportPesa UFS Boxing Series card will be the first time he has boxed outside Ghana.</p>
<p>Okwiri is returning to the ring and will be fighting in front of a home crowd.</p>
<p><em>Editor's note: confirm the weight class and the latest bout details with the promoter before publishing.</em></p>
HTML,
                'sources' => [
                    ['title' => 'Nation Africa — Okwiri eyes WBA title against undefeated Ghanaian', 'url' => 'https://nation.africa/kenya/sports/boxing/okwiri-eyes-wba-title-against-undefeated-ghanaian-on-return-5667512'],
                ],
            ],
        ];
    }
}
