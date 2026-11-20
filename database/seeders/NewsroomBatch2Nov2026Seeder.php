<?php

namespace Database\Seeders;

use App\Enums\ArticleType;
use App\Enums\InsightType;
use App\Jobs\OptimiseImage;
use App\Models\Media;
use Illuminate\Support\Str;

/**
 * Second batch of stand-in stories (researched 15 November 2026) to fill site sections until launch.
 * Same rules as the first batch: original wording, sources listed, freely licensed photos only.
 */
class NewsroomBatch2Nov2026Seeder extends NewsroomDraftsNov2026Seeder
{
    /** @return array<string, Media> */
    protected function media(): array
    {
        $dir = 'media/2026/11/';
        $items = [
            'training' => ['black-stars-training.jpg', 'Black Stars players during a training session', 'Black Stars in training (file photo, 2008).', 'Benjamin Mussler / Wikimedia Commons, CC BY-SA 2.0'],
            'afcon' => ['afcon-2024-guinea.jpg', 'Guinea players applaud supporters after a match at the Africa Cup of Nations', 'Guinea players after a match at AFCON 2023 in Côte d\'Ivoire, January 2024 (file photo).', 'Fédération Guinéenne de Football / Wikimedia Commons, public domain'],
            'ucl' => ['ucl-trophy.jpg', 'The UEFA Champions League trophy on display', 'The UEFA Champions League trophy (file photo).', 'Melon5541 / Wikimedia Commons, CC BY-SA 4.0'],
            'norris' => ['norris-singapore-2026.jpg', 'Lando Norris driving his McLaren during a Formula 1 race', 'Lando Norris at the 2026 Singapore Grand Prix sprint (file photo).', 'Liauzh / Wikimedia Commons, CC BY-SA 4.0'],
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
                'slug' => 'afcon-2027-qualifiers-matchday-3-round-up',
                'title' => 'AFCON qualifiers: Senegal and Côte d\'Ivoire win as Ghana\'s rivals The Gambia slip up',
                'excerpt' => 'Côte d\'Ivoire opened a three-point lead in Ghana\'s Group C by handing The Gambia their first defeat, while Senegal came from behind to thrash Sudan.',
                'category' => 'afcon', 'type' => ArticleType::News, 'image' => 'afcon',
                'tags' => ['AFCON 2027 qualifiers', 'Côte d\'Ivoire', 'Senegal', 'The Gambia'],
                'body' => <<<'HTML'
<p>Côte d'Ivoire tightened their grip on Group C of the 2027 Africa Cup of Nations qualifiers with a 2-1 win over The Gambia in Bouaké, a result that also matters for Ghana.</p>
<p>The victory took Côte d'Ivoire to nine points from three matches and inflicted a first defeat of the campaign on The Gambia, who stay on six points. Ghana's 2-0 win over Somalia moved the Black Stars onto three points, keeping them in touch with the top two.</p>
<h2>Senegal come from behind</h2>
<p>In Group J, Senegal recovered after conceding a first-half equaliser to beat Sudan 4-1 in Dakar. Boulaye Dia and Ibrahim Mbaye scored in the second half before Pathé Mboup added a late fourth. Senegal lead the group on seven points, three ahead of Mozambique.</p>
<h2>Other results</h2>
<ul>
<li>Cameroon 2-0 Namibia</li>
<li>Benin 4-1 Central African Republic</li>
<li>DR Congo 3-1 Sierra Leone</li>
</ul>
<p>The top two teams in each group qualify for the 2027 finals.</p>
HTML,
                'sources' => [
                    ['title' => 'Voice of Nigeria — Senegal, Côte d\'Ivoire claim vital wins', 'url' => 'https://von.gov.ng/afcon-qualifiers-senegal-cote-divoire-claim-vital-wins/'],
                    ['title' => 'BusinessDay — Senegal, Cameroon, Ivory Coast record impressive wins', 'url' => 'https://businessday.ng/sports/article/afcon-2027-qualifiers-senegal-cameroon-ivory-coast-record-impressive-wins/'],
                    ['title' => 'SuperSport — AFCON 2027 qualifiers fixtures and results', 'url' => 'https://supersport.com/football/news/fcbfd4cd-2a67-41e0-be6b-a2ecea7dacf9/afcon-2027-qualifiers-fixtures-results-groups-and-broadcast-details'],
                ],
            ],
            [
                'slug' => 'analysis-ghana-group-c-afcon-2027-qualification-maths',
                'title' => 'Analysis: What Ghana need to reach AFCON 2027 after ending their winless run',
                'excerpt' => 'Three points behind The Gambia and six off leaders Côte d\'Ivoire, the Black Stars\' path to the finals now runs through winning their remaining games.',
                'category' => 'match-analysis', 'type' => ArticleType::Insight, 'insight_type' => InsightType::TeamForm, 'image' => 'training',
                'tags' => ['Black Stars', 'AFCON 2027 qualifiers', 'Analysis'],
                'body' => <<<'HTML'
<p>Thursday's 2-0 win over Somalia ended a long wait for a qualifying victory, but it also made the size of Ghana's task clear.</p>
<h2>The table after three rounds</h2>
<p>Côte d'Ivoire lead Group C with nine points from three wins. The Gambia are second on six after losing for the first time, away in Bouaké. Ghana are third on three points, and Somalia are yet to score a point.</p>
<p>Only the top two teams qualify for the finals. With each side playing the others home and away, Ghana have three matches left, starting with Monday's rematch against Somalia.</p>
<h2>What it means</h2>
<p>A second win over Somalia would take Ghana to six points, matching The Gambia's current total, though The Gambia also have a fourth-round match to play. From there, results against Côte d'Ivoire and The Gambia, the two teams who beat Ghana at the start of the campaign, are likely to decide the second qualifying place.</p>
<p>The Black Stars' recent form shows how slim the margin is: two defeats followed by a win in which captain Jordan Ayew missed an early penalty. Converting chances will matter as much as the results themselves.</p>
HTML,
                'takeaways' => ['type' => 'key_takeaways', 'data' => ['heading' => 'Key takeaways', 'items' => [
                    ['text' => 'Group C after three rounds: Côte d\'Ivoire 9 points, The Gambia 6, Ghana 3, Somalia 0.'],
                    ['text' => 'Only the top two qualify, and Ghana have three games left.'],
                    ['text' => 'Beating Somalia again would take Ghana to six points, The Gambia\'s current total.'],
                ]]],
                'sources' => [
                    ['title' => 'SuperSport — Ghana end 37-month winless AFCON run; Ivory Coast, Senegal triumph', 'url' => 'https://supersport.com/football/news/bf295db6-520e-406f-a4b3-e765245b38a5/ghana-end-37-month-winless-afcon-run-ivory-coast-senegal-triumph'],
                    ['title' => 'Voice of Nigeria — Senegal, Côte d\'Ivoire claim vital wins', 'url' => 'https://von.gov.ng/afcon-qualifiers-senegal-cote-divoire-claim-vital-wins/'],
                ],
            ],
            [
                'slug' => 'champions-league-matchday-4-round-up-psg-como-fenerbahce',
                'title' => 'Champions League: PSG hit six, Como score nine and Fenerbahçe beat Liverpool',
                'excerpt' => 'Matchday 4 of the league phase brought big wins for Paris Saint-Germain and Como, while Manchester United, Manchester City and Real Madrid all won by a single goal.',
                'category' => 'uefa-champions-league', 'type' => ArticleType::News, 'image' => 'ucl',
                'tags' => ['Champions League', 'PSG', 'Liverpool', 'Manchester United'],
                'body' => <<<'HTML'
<p>The Champions League league phase reached its halfway point on 3 and 4 November with some of the biggest scorelines of the season so far.</p>
<p>Paris Saint-Germain won 6-0 away to Villarreal, and Como went even further with a 9-1 win over Lens. Fenerbahçe beat Liverpool 3-2 in Istanbul.</p>
<h2>English clubs</h2>
<p>Manchester United came from behind to beat Roma 3-2, Manchester City edged RB Leipzig 3-2, and Arsenal beat Slavia Prague 2-1.</p>
<h2>Elsewhere</h2>
<ul>
<li>Atlético Madrid 0-2 Bayern Munich</li>
<li>Real Madrid 3-2 AEK Athens</li>
<li>Porto 4-1 Napoli</li>
<li>Inter 3-0 Feyenoord</li>
</ul>
<p>Matchday 5 is on 24 and 25 November, with Arsenal against Borussia Dortmund, Manchester City against Napoli and Real Madrid against PSV Eindhoven among the fixtures.</p>
HTML,
                'sources' => [
                    ['title' => 'UEFA — 2026/27 Champions League fixtures and results', 'url' => 'https://www.uefa.com/uefachampionsleague/news/02a8-2174c9e9019d-f909a77bd77a-1000--2026-27-champions-league-all-the-league-phase-fixtures-a/'],
                    ['title' => 'CBS Sports — League phase reaches halfway point with Matchday 4', 'url' => 'https://www.cbssports.com/soccer/news/uefa-champions-league-schedule-matchday-4/'],
                ],
            ],
            [
                'slug' => 'f1-sao-paulo-grand-prix-norris-wins-mercedes-constructors-title',
                'title' => 'Norris wins in São Paulo as Mercedes seal the constructors\' championship',
                'excerpt' => 'Lando Norris took his third win of the season ahead of Max Verstappen and Isack Hadjar, while Kimi Antonelli remains the clear leader in the drivers\' standings.',
                'category' => 'formula-1', 'type' => ArticleType::News, 'image' => 'norris',
                'tags' => ['Formula 1', 'Lando Norris', 'Mercedes', 'Kimi Antonelli'],
                'body' => <<<'HTML'
<p>Lando Norris won the São Paulo Grand Prix on 8 November, his third victory of the 2026 Formula 1 season. Max Verstappen finished second and Isack Hadjar third.</p>
<p>Mercedes clinched the constructors' championship at Interlagos, with three races of the season remaining.</p>
<h2>Drivers' championship</h2>
<p>Kimi Antonelli leads the drivers' standings on 390 points, well clear of Verstappen on 279. George Russell (262) and Lewis Hamilton (261) are next, with Charles Leclerc and Norris close behind.</p>
<p>Published points totals for Leclerc and for Ferrari in the constructors' table differ by a couple of points between sources, so check the official standings for exact figures.</p>
HTML,
                'sources' => [
                    ['title' => 'RacingNews365 — 2026 F1 standings after the São Paulo Grand Prix', 'url' => 'https://racingnews365.com/2026-f1-championship-standings-after-sao-paulo-grand-prix'],
                    ['title' => 'Sports Illustrated — F1 standings and results after the 2026 Brazilian Grand Prix', 'url' => 'https://www.si.com/onsi/f1/news/f1-standings-and-results-after-the-2026-brazilian-grand-prix'],
                ],
            ],
            [
                'slug' => 'hutchinson-beats-buatsi-wbc-light-heavyweight-title',
                'title' => 'Buatsi beaten by Hutchinson in WBC light-heavyweight title fight',
                'excerpt' => 'The Ghanaian-born boxer lost a unanimous decision in Manchester as Willy Hutchinson won his first world title in their rematch.',
                'category' => 'boxing', 'type' => ArticleType::News,
                'tags' => ['Boxing', 'Joshua Buatsi', 'Willy Hutchinson', 'WBC'],
                'body' => <<<'HTML'
<p>Joshua Buatsi's bid for a world title ended in defeat on 7 November as Willy Hutchinson won the vacant WBC light-heavyweight title on a unanimous decision at the AO Arena in Manchester.</p>
<p>The judges scored the fight 117-112, 115-113 and 115-113 for the Scot. It was the first world title of Hutchinson's professional career and reversed the result of the pair's first meeting, which Buatsi won.</p>
<p>Buatsi, who was born in Ghana and moved to England as a child, will now have to rebuild towards another title opportunity.</p>
HTML,
                'sources' => [
                    ['title' => 'DAZN — Boxing weekend review: Hutchinson\'s world title triumph', 'url' => 'https://www.dazn.com/en-GB/news/boxing/boxing-weekend-review-hutchinson-buatsi-ezra-taylor-clarke-billam-smith-conor-benn/12rzeg2gj7j8d14cuvx9denazx'],
                    ['title' => 'Fightmag — Hutchinson beats Buatsi in rematch to win WBC title', 'url' => 'https://www.fightmag.com/boxing-joshua-buatsi-vs-willy-hutchinson-2-live-results/'],
                ],
            ],
            [
                'slug' => 'fnb-accra-marathon-2026-koogo-atia-aida-jerotich-win',
                'title' => 'Student Koogo Atia wins FNB Accra Marathon as Kenya\'s Jerotich takes women\'s title',
                'excerpt' => 'The race returned to the full 42.2km distance, with Atia finishing at Independence Square in 2:28:49.',
                'category' => 'athletics', 'type' => ArticleType::News,
                'tags' => ['Athletics', 'Accra Marathon'],
                'body' => <<<'HTML'
<p>Koogo Atia won the men's elite race at the 2026 FNB Accra Marathon on Saturday, 14 November, as the event returned to the full marathon distance of 42.2km.</p>
<p>Atia, a 27-year-old student at the University of Education, Winneba, crossed the line at Independence Square in two hours, 28 minutes and 49 seconds. David Nakousi, who had won the previous two editions, was among those in the field.</p>
<h2>Women's race</h2>
<p>Kenya's Aida Jerotich won the women's race in 2:30:58, earning GHC25,000 and a place at the Cape Town Marathon. Ghana's Portia Anaba was second in 3:00:13, and Benin's Ayade Collette third in 3:03:22.</p>
HTML,
                'sources' => [
                    ['title' => 'Graphic Online — Koogo Atia wins 2026 FNB Accra Marathon', 'url' => 'https://www.graphic.com.gh/sports/sports-news/koogo-atia-wins-2026-fnb-accra-marathon-as-kenyan-jerotich-takes-womens-crown.html'],
                    ['title' => 'Graphic Online — Nakousi faces toughest title defence as marathon returns to 42.2km', 'url' => 'https://www.graphic.com.gh/sports/sports-news/nakousi-faces-toughest-title-defence-as-fnb-accra-marathon-returns-to-42-2km.html'],
                ],
            ],
            [
                'slug' => 'spintex-knights-road-to-bal-2027-elite-16',
                'title' => 'Spintex Knights chase a place in the 2027 Basketball Africa League',
                'excerpt' => 'Ghana\'s representatives are among 16 clubs in the final qualifying round, with five places in the BAL regular season up for grabs.',
                'category' => 'basketball', 'type' => ArticleType::News,
                'tags' => ['Basketball', 'Spintex Knights', 'Basketball Africa League'],
                'body' => <<<'HTML'
<p>Spintex Knights are carrying Ghana's hopes into the Road to BAL Elite 16, the final qualifying round for the 2027 Basketball Africa League season.</p>
<p>The Knights were among 13 clubs to progress from the earlier qualifying round, joining three automatic qualifiers. The 16 teams are split into two divisions: the West Division in Abidjan, at the Palais des Sports de Treichville, and the East Division in Nairobi, at the Kasarani Indoor Arena from 23 to 29 November.</p>
<h2>Five places available</h2>
<p>The top two teams in each division qualify, along with the best third-placed team across both divisions. The five qualifiers join seven teams already confirmed for the BAL regular season from Angola, Egypt, Morocco, Nigeria, Rwanda, Senegal and Tunisia.</p>
<p>Hosts JCA Kings of Côte d'Ivoire and Kenya's Nairobi City are among the clubs FIBA identified as frontrunners.</p>
HTML,
                'sources' => [
                    ['title' => 'FIBA — Preview: 2027 Road to BAL Elite 16', 'url' => 'https://www.fiba.basketball/en/events/fiba-africa-champions-clubs-road-to-bal-2027/news/preview-2027-road-to-bal-elite-16'],
                ],
            ],
        ];
    }
}
