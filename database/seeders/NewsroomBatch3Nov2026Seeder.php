<?php

namespace Database\Seeders;

use App\Enums\ArticleType;
use App\Enums\InsightType;
use App\Jobs\OptimiseImage;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Third batch of stand-in stories (researched 20 November 2026): November-window results and the
 * sections that were still empty. Photos are attached only when the licensed file exists locally,
 * so the seeder can be re-run later to add photos that failed to download.
 */
class NewsroomBatch3Nov2026Seeder extends NewsroomDraftsNov2026Seeder
{
    /** @return array<string, Media> */
    protected function media(): array
    {
        $dir = 'media/2026/11/';
        $items = [
            'goal' => ['black-stars-goal-celebration.jpg', 'Black Stars players celebrate a goal', 'Ghana players celebrate a goal (file photo, 2008).', 'Benjamin Mussler / Wikimedia Commons, CC BY-SA 2.0'],
            'anthem' => ['black-stars-anthem.jpg', 'Ghana players with hands on their chests during the national anthem', 'Ghana players during the national anthem (file photo).', 'Benjamin Mussler / Wikimedia Commons, CC BY-SA 2.0'],
            'afcon' => ['afcon-2024-guinea.jpg', 'Guinea players applaud supporters after a match at the Africa Cup of Nations', 'Guinea players after a match at AFCON 2023 in Côte d\'Ivoire, January 2024 (file photo).', 'Fédération Guinéenne de Football / Wikimedia Commons, public domain'],
            'training' => ['black-stars-training.jpg', 'Black Stars players during a training session', 'Black Stars in training (file photo, 2008).', 'Benjamin Mussler / Wikimedia Commons, CC BY-SA 2.0'],
            'allianz' => ['allianz-arena.jpg', 'The Allianz Arena in Munich in daylight', 'The Allianz Arena, home of Bayern Munich (file photo).', 'Richard Bartz / Wikimedia Commons, CC BY-SA 2.5'],
            'metropolitano' => ['metropolitano-madrid.jpg', 'Inside the Metropolitano stadium in Madrid', 'The Metropolitano, home of Atlético Madrid (file photo).', 'Zarateman / Wikimedia Commons, CC0'],
            'olimpico' => ['stadio-olimpico-rome.jpg', 'A packed Stadio Olimpico in Rome during a Serie A match', 'The Stadio Olimpico in Rome, home of Lazio and Roma (file photo, 2014).', 'romazone / Wikimedia Commons, CC BY 2.0'],
            'shelton' => ['shelton-basel-2026.jpg', 'Ben Shelton hitting a forehand', 'Ben Shelton at the Swiss Indoors in Basel, October 2026.', 'Skyscraper2010 / Wikimedia Commons, CC BY-SA 4.0'],
            'accra' => ['accra-sports-stadium.jpg', 'Accra Sports Stadium in Ghana', 'Accra Sports Stadium (file photo).', 'Fanti Salms / Wikimedia Commons, CC BY-SA 4.0'],
        ];

        $out = [];
        foreach ($items as $key => [$file, $alt, $caption, $credit]) {
            $path = $dir.$file;
            $disk = Storage::disk('public');

            // Skip photos whose download failed (e.g. rate-limited responses saved as HTML).
            if (! $disk->exists($path) || ! str_starts_with((string) $disk->mimeType($path), 'image/')) {
                continue;
            }

            $out[$key] = Media::firstOrCreate(['path' => $path], [
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
                'slug' => 'ghana-beat-somalia-1-0-jordan-ayew-penalty',
                'title' => 'Jordan Ayew penalty gives Ghana second win over Somalia',
                'excerpt' => 'The captain made amends for his missed spot-kick on Thursday as the Black Stars completed a November double over Somalia, though the 1-0 win was far from convincing.',
                'category' => 'black-stars', 'type' => ArticleType::MatchReport, 'image' => 'goal',
                'tags' => ['Black Stars', 'AFCON 2027 qualifiers', 'Somalia', 'Jordan Ayew'],
                'body' => <<<'HTML'
<p>Jordan Ayew's first-half penalty gave Ghana a 1-0 win over Somalia at the Essipong Sports Stadium on Monday, completing a double over the Group C strugglers in the 2027 Africa Cup of Nations qualifiers.</p>
<p>The captain, who had an early penalty saved in Thursday's 2-0 win, converted from the spot in the 36th minute.</p>
<p>It was a laboured display. Ghana dominated possession, with 68 per cent of the ball, and had 13 shots, but only four were on target. Somalia managed two attempts all game, and neither troubled goalkeeper Lawrence Ati-Zigi.</p>
<p>Ati-Zigi was booked for time-wasting late on. Brandon Thomas-Asante was also cautioned before being replaced by Felix Afena-Gyan, Thursday's scorer, in the 58th minute.</p>
<h2>Where it leaves Ghana</h2>
<p>The win takes Ghana to six points, one behind second-placed The Gambia, who drew 2-2 with group leaders Côte d'Ivoire on Tuesday. With two matchdays remaining, Ghana's fate is still in their own hands, but they will need better finishing.</p>
HTML,
                'sources' => [
                    ['title' => 'Africa Top Sports — Jordan Ayew penalty earns Ghana narrow victory over Somalia', 'url' => 'https://en.africatopsports.com/2027-afcon-q-jordan-ayew-penalty-earns-ghana-narrow-victory-over-somalia-in-afcon-qualifier/'],
                    ['title' => 'allAfrica — Black Stars labour to narrow win over Somalia', 'url' => 'https://allafrica.com/stories/202611180538.html'],
                    ['title' => 'Modern Ghana — Jordan Ayew scores as Ghana beat Somalia', 'url' => 'https://www.modernghana.com/sports/1545064/2027-afcon-qualifiers-jordan-ayew-scores-as-ghana.html'],
                ],
            ],
            [
                'slug' => 'gambia-hold-cote-divoire-2-2-group-c',
                'title' => 'The Gambia hold Côte d\'Ivoire to keep Group C race alive',
                'excerpt' => 'Yankuba Minteh and Musa Barrow scored as The Gambia drew 2-2 with the leaders in Bakau, denying Côte d\'Ivoire early qualification.',
                'category' => 'afcon', 'type' => ArticleType::MatchReport, 'image' => 'afcon',
                'tags' => ['AFCON 2027 qualifiers', 'The Gambia', 'Côte d\'Ivoire'],
                'body' => <<<'HTML'
<p>The Gambia drew 2-2 with Côte d'Ivoire at the Independence Stadium in Bakau on Tuesday, a result that stopped the Elephants securing early qualification for the 2027 Africa Cup of Nations.</p>
<p>Yankuba Minteh and Musa Barrow scored for The Gambia, with Barrow's goal coming from the penalty spot in the 80th minute. Côte d'Ivoire hit back late to share the points.</p>
<p>Five days earlier, Côte d'Ivoire had beaten The Gambia 2-1 in Bouaké, with goals from Malick Yalcouyé and Elye Wahi.</p>
<h2>Group C</h2>
<p>Côte d'Ivoire lead the group with 10 points from four matches. The Gambia are second on seven, Ghana third on six after their two wins over Somalia, and Somalia are without a point. Two matchdays remain.</p>
HTML,
                'sources' => [
                    ['title' => 'Panafrica Football — Ivory Coast match Gambia in thrilling draw', 'url' => 'https://panafricafootball.com/article/afcon-q-gambia-civ-md4'],
                    ['title' => 'The Rio Times — Ivory Coast held 2-2 by Gambia', 'url' => 'https://www.riotimesonline.com/ivory-coast-gambia-2-2-afcon-2027-qualifier-draw'],
                    ['title' => 'beIN Sports — Ivory Coast beat Gambia 2-1', 'url' => 'https://www.beinsports.com/en-us/soccer/africa-cup-of-nations-qualification/articles-video/ivory-coast-vs-gambia-highlights-afcon-qualifiers-2026-11-12'],
                ],
            ],
            [
                'slug' => 'analysis-ghana-afcon-2027-hopes-after-november-window',
                'title' => 'Analysis: Two wins, six points — where Ghana stand after the November window',
                'excerpt' => 'Ghana have recovered from two opening defeats, but their qualification hopes now rest on the return games against the two teams above them.',
                'category' => 'match-analysis', 'type' => ArticleType::Insight, 'insight_type' => InsightType::TeamForm, 'image' => 'training',
                'tags' => ['Black Stars', 'AFCON 2027 qualifiers', 'Analysis'],
                'body' => <<<'HTML'
<p>Six points from the November double-header against Somalia have put Ghana's 2027 Africa Cup of Nations qualifying campaign back on track, but the job is only half done.</p>
<h2>The table</h2>
<p>After four rounds, Côte d'Ivoire lead Group C on 10 points, The Gambia are second on seven, Ghana third on six, and Somalia have none. Only the top two qualify for the finals.</p>
<h2>The run-in</h2>
<p>Ghana opened the campaign with defeats to Côte d'Ivoire and The Gambia. In a group where every team meets the others home and away, that means their last two matches are the return fixtures against those same two sides.</p>
<p>That cuts both ways. Ghana are one point behind The Gambia, so beating them would lift the Black Stars above their rivals. But both opponents have already beaten Ghana in this campaign.</p>
<h2>What needs to improve</h2>
<p>The Somalia games produced three goals in two matches against the group's weakest side, with a penalty missed in the first and only four shots on target from 13 in the second. Interim coach Kim Lars Björkegren's side will need to be far more clinical against stronger opposition, and the Ghana FA's choice of permanent coach before the next window will shape that.</p>
HTML,
                'takeaways' => ['type' => 'key_takeaways', 'data' => ['heading' => 'Key takeaways', 'items' => [
                    ['text' => 'Group C after four rounds: Côte d\'Ivoire 10, The Gambia 7, Ghana 6, Somalia 0.'],
                    ['text' => 'Ghana\'s last two games are the return fixtures against Côte d\'Ivoire and The Gambia.'],
                    ['text' => 'A win over The Gambia would move Ghana above them; finishing is the main concern.'],
                ]]],
                'sources' => [
                    ['title' => 'Panafrica Football — Ivory Coast match Gambia in thrilling draw', 'url' => 'https://panafricafootball.com/article/afcon-q-gambia-civ-md4'],
                    ['title' => 'Africa Top Sports — Jordan Ayew penalty earns Ghana narrow victory', 'url' => 'https://en.africatopsports.com/2027-afcon-q-jordan-ayew-penalty-earns-ghana-narrow-victory-over-somalia-in-afcon-qualifier/'],
                ],
            ],
            [
                'slug' => 'la-liga-fermin-lopez-double-barcelona-win-at-atletico',
                'title' => 'Fermín López double sends Barcelona three points clear after win at Atlético',
                'excerpt' => 'Barcelona came from behind at the Metropolitano to restore their lead at the top of La Liga going into the international break.',
                'category' => 'la-liga', 'type' => ArticleType::MatchReport, 'image' => 'metropolitano',
                'tags' => ['La Liga', 'Barcelona', 'Atlético Madrid', 'Fermín López'],
                'body' => <<<'HTML'
<p>Fermín López scored twice as Barcelona came from behind to beat Atlético Madrid 2-1 on 8 November and restore their three-point lead at the top of La Liga.</p>
<p>Giuliano Simeone put Atlético ahead in the 10th minute, but López equalised on the stroke of half-time and scored the winner in the 66th minute.</p>
<p>Real Madrid had drawn level with Barcelona on points by beating Valencia on Saturday, but the result in Madrid sent the leaders back three points clear.</p>
<h2>Other results on Sunday</h2>
<ul>
<li>Elche 0-2 Real Betis</li>
<li>Real Sociedad 1-0 Rayo Vallecano</li>
<li>Villarreal 2-0 Getafe</li>
<li>Celta Vigo 0-0 Levante</li>
</ul>
<p>La Liga resumes after the international break this weekend.</p>
HTML,
                'sources' => [
                    ['title' => 'ESPN — Atlético 1-2 Barcelona match report', 'url' => 'https://www.espn.com/soccer/report/_/gameId/401882816'],
                    ['title' => 'Al Jazeera — Atlético Madrid vs Barcelona live', 'url' => 'https://www.aljazeera.com/sports/liveblog/2026/11/8/atletico-madrid-vs-barcelona-la-liga-live'],
                    ['title' => 'Football Web Pages — La Liga fixtures and results', 'url' => 'https://www.footballwebpages.co.uk/spanish-la-liga/fixtures-results'],
                ],
            ],
            [
                'slug' => 'serie-a-lazio-unbeaten-top-of-table',
                'title' => 'Unbeaten Lazio lead Serie A with Inter and Roma in close pursuit',
                'excerpt' => 'Lazio have taken 29 points from 11 matches without defeat, one ahead of Inter, as the top three are separated by two points.',
                'category' => 'serie-a', 'type' => ArticleType::News, 'image' => 'olimpico',
                'tags' => ['Serie A', 'Lazio', 'Inter', 'Roma'],
                'body' => <<<'HTML'
<p>Lazio sit top of Serie A going into the international break, unbeaten after 11 matches with nine wins and two draws.</p>
<p>Their 29 points put them one ahead of Inter on 28, with city rivals Roma third on 27. AC Milan are fourth on 24 points, five behind the leaders.</p>
<p>Lazio have the best goal difference in the division, at +16, and are the only side yet to lose a league game this season.</p>
<h2>Trouble at the bottom</h2>
<p>Udinese dismissed their head coach on 2 November with the club 19th in the table, following Venezia, who changed coach in October while bottom of the league.</p>
<p>Serie A resumes this weekend after the international break.</p>
HTML,
                'sources' => [
                    ['title' => 'AC Milan — Serie A standings 2026/27', 'url' => 'https://www.acmilan.com/en/season/2026/standings/serie-a'],
                    ['title' => 'Wikipedia — 2026–27 Serie A', 'url' => 'https://en.wikipedia.org/wiki/2026%E2%80%9327_Serie_A'],
                ],
            ],
            [
                'slug' => 'bundesliga-bayern-dortmund-level-at-the-top',
                'title' => 'Bayern and Dortmund level at the top of the Bundesliga',
                'excerpt' => 'Unbeaten Bayern Munich lead only on goal difference, with Borussia Dortmund matching their 21 points.',
                'category' => 'bundesliga', 'type' => ArticleType::News, 'image' => 'allianz',
                'tags' => ['Bundesliga', 'Bayern Munich', 'Borussia Dortmund'],
                'body' => <<<'HTML'
<p>Bayern Munich and Borussia Dortmund are level on 21 points at the top of the Bundesliga, with Bayern ahead on goal difference.</p>
<p>Bayern remain unbeaten after nine matches, with six wins and three draws, and have scored 29 goals while conceding nine. Dortmund have scored 16 and conceded seven. Bayer Leverkusen are fifth on 16 points.</p>
<h2>Recent results</h2>
<ul>
<li>RB Leipzig 5-2 Augsburg</li>
<li>Stuttgart 1-0 Werder Bremen</li>
</ul>
<p>The Bundesliga resumes after the international break this weekend.</p>
HTML,
                'sources' => [
                    ['title' => 'Bundesliga — official table', 'url' => 'https://www.bundesliga.com/en/bundesliga/table'],
                    ['title' => 'LiveSoccerTV — Bundesliga fixtures and results', 'url' => 'https://www.livesoccertv.com/competitions/germany/bundesliga/'],
                ],
            ],
            [
                'slug' => 'atp-finals-shelton-reaches-first-semi-final-zverev-tops-group',
                'title' => 'ATP Finals: Shelton reaches first semi-final as Zverev tops his group',
                'excerpt' => 'Ben Shelton beat Rafael Jodar to become the youngest American in the Turin last four since Andy Roddick in 2004.',
                'category' => 'tennis', 'type' => ArticleType::News, 'image' => 'shelton',
                'tags' => ['Tennis', 'ATP Finals', 'Ben Shelton', 'Alexander Zverev'],
                'body' => <<<'HTML'
<p>Ben Shelton reached the semi-finals of the Nitto ATP Finals for the first time on Thursday, beating Rafael Jodar 7-6(8), 7-5 in two hours and seven minutes in Turin.</p>
<p>The 24-year-old American finished the round robin with two wins from three matches and goes through as runner-up in the John McEnroe Group, behind world No 1 Alexander Zverev. He is the youngest American to reach the last four at the season finale since Andy Roddick in 2004.</p>
<p>Jodar leaves Turin without a win after losing all three of his matches.</p>
<p>Carlos Alcaraz has also qualified for the semi-finals from the Ivan Lendl Group. The tournament, at the Inalpi Arena, ends with the final on Sunday, 22 November.</p>
HTML,
                'sources' => [
                    ['title' => 'ATP Tour — Shelton defeats Jodar, reaches Nitto ATP Finals semi-finals', 'url' => 'https://www.atptour.com/en/news/shelton-jodar-nitto-atp-finals-2026-thursday'],
                    ['title' => 'Olympics.com — Zverev books first spot in semi-finals', 'url' => 'https://www.olympics.com/en/news/atp-finals-2026-alexander-zverev-good-tennis-semi-finals'],
                ],
            ],
            [
                'slug' => 'ghanaian-players-abroad-adu-ayew-agyei-score',
                'title' => 'Ghanaians abroad: Adu rescues Plzen in Prague derby as Ayew and Agyei score',
                'excerpt' => 'Prince Kwabena Adu struck a stoppage-time equaliser against Sparta Prague, while Jordan Ayew, Daniel Agyei and Michael Adu-Poku were also on the scoresheet.',
                'category' => 'ghanaian-players-abroad', 'type' => ArticleType::News, 'image' => 'anthem',
                'tags' => ['Ghanaian players abroad', 'Prince Kwabena Adu', 'Jordan Ayew'],
                'body' => <<<'HTML'
<p>Prince Kwabena Adu was the pick of the Ghanaian performers abroad over the weekend of 7 and 8 November, scoring a stoppage-time equaliser to earn Viktoria Plzen a 1-1 draw against Sparta Prague. Fellow Ghanaian Sampson Dweh provided the assist.</p>
<h2>Goals</h2>
<ul>
<li><strong>Jordan Ayew</strong> headed in for Sheffield United in the 25th minute, but they lost 2-1 to Charlton Athletic.</li>
<li><strong>Daniel Agyei</strong> scored in the fifth minute of Kocaelispor's 2-0 win away to Eyüpspor in Turkey.</li>
<li><strong>Michael Adu-Poku</strong> scored as Newport County beat Walsall 3-1 in the FA Cup first round.</li>
<li><strong>Michael Baidoo</strong> came off the bench to score in Plymouth Argyle's 4-0 FA Cup win over Alvechurch.</li>
<li><strong>David Abagna</strong> scored and assisted as FC Ordabasy won the Kazakhstan Cup final 4-1 against Kaysar.</li>
</ul>
<h2>Assists</h2>
<ul>
<li><strong>Sion Oppong</strong> set up a goal in Toulouse's 3-1 Ligue 1 win over Strasbourg.</li>
<li><strong>Jesurun Rak-Sakyi</strong> assisted in the third minute of Kasımpaşa's 2-0 win at Gaziantep.</li>
<li><strong>Mohammed Fuseini</strong> laid on the equaliser as Derby County came from behind to beat Bolton 3-1.</li>
<li><strong>Frank Junior Adjei</strong> assisted in Hammarby's 4-1 win over Elfsborg.</li>
<li><strong>Ernest Poku</strong> set up Beşiktaş's late equaliser in the Europa League, though they lost 3-2 to Celtic.</li>
</ul>
<p>In Canada, Prince Osei Owusu was named CF Montréal's most valuable player for the second season running after scoring 18 goals in 2026.</p>
HTML,
                'sources' => [
                    ['title' => 'Citi Sports Online — Ghanaian players abroad: Ayew, Adu among weekend scorers', 'url' => 'https://citisportsonline.com/2026/11/ghanaian-players-abroad-ayew-adu-among-weekend-scorers-as-rak-sakyi-rahim-ibrahim-assist/'],
                ],
            ],
            [
                'slug' => 'division-one-league-zone-leaders-after-matchday-8',
                'title' => 'Division One League: Mysterious Dwarfs, Semper Fi and Sankara Nationals set the pace',
                'excerpt' => 'Eight matchdays in, the four zone leaders are separated from their nearest challengers by between two and five points.',
                'category' => 'division-one-league', 'type' => ArticleType::News, 'image' => 'accra',
                'tags' => ['Division One League', 'Mysterious Dwarfs', 'Semper Fi', 'Sankara Nationals'],
                'body' => <<<'HTML'
<p>The race for promotion to the Ghana Premier League is taking shape after eight matchdays of the Division One League, with Mysterious Dwarfs the highest points-getters across the four zones.</p>
<h2>Zone by zone</h2>
<ul>
<li><strong>Zone One A:</strong> Sankara Nationals lead on 19 points, five clear of Real Tamale United (14).</li>
<li><strong>Zone One B:</strong> Mighty Royals top the table on 16 points, two ahead of Bechtero Sasana (14).</li>
<li><strong>Zone Two:</strong> Mysterious Dwarfs have 21 points, two more than Ebony (19).</li>
<li><strong>Zone Three:</strong> Semper Fi lead on 19 points, four clear of WAFA (15).</li>
</ul>
<p>Only Sankara Nationals have opened up a lead of more than four points, which leaves every zone open with the season still in its early stages.</p>
HTML,
                'sources' => [
                    ['title' => 'Ghana Football Association — Division One League statistics after Matchday 8', 'url' => 'https://www.ghanafa.org/umb-division-one-league-statistics-after-matchday-8'],
                ],
            ],
        ];
    }
}
