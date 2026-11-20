<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * name => [color, is_football, children => [name => is_football]]
     */
    private const TREE = [
        'Ghana Sports' => ['#12B76A', true, [
            'Ghana Premier League' => true,
            'Division One League' => true,
            'Black Stars' => true,
            'Black Queens' => true,
            'Ghanaian Players Abroad' => true,
            'Ghana Football Association' => true,
            'Local Football' => true,
            'School and University Sports' => false,
        ]],
        'International Sports' => ['#0B5CAD', true, [
            'English Premier League' => true,
            'UEFA Champions League' => true,
            'La Liga' => true,
            'Serie A' => true,
            'Bundesliga' => true,
            'Ligue 1' => true,
            'FIFA World Cup' => true,
            'AFCON' => true,
            'International Transfers' => true,
        ]],
        'Other Sports' => ['#7A5AF8', false, [
            'Boxing' => false,
            'Basketball' => false,
            'Athletics' => false,
            'Tennis' => false,
            'Formula 1' => false,
            'Volleyball' => false,
            'Other Sporting Events' => false,
        ]],
        'SportyCiti Insights' => ['#FF7A00', true, [
            'Match Analysis' => true,
            'Tactical Analysis' => true,
            'Player Performance' => true,
            'Team Performance' => true,
            'Football Statistics' => true,
            'Match Previews' => true,
            'Expert Opinions' => true,
            'Sports Business' => false,
            'Transfer Analysis' => true,
        ]],
    ];

    public function run(): void
    {
        $position = 0;

        foreach (self::TREE as $name => [$color, $football, $children]) {
            $parent = Category::updateOrCreate(
                ['slug' => $this->slug($name)],
                ['name' => $name, 'color' => $color, 'position' => $position++, 'is_football' => $football, 'is_active' => true, 'show_in_nav' => true],
            );

            $childPosition = 0;
            foreach ($children as $childName => $childFootball) {
                Category::updateOrCreate(
                    ['slug' => $this->slug($childName)],
                    ['name' => $childName, 'parent_id' => $parent->id, 'color' => $color, 'position' => $childPosition++, 'is_football' => $childFootball, 'is_active' => true, 'show_in_nav' => true],
                );
            }
        }
    }

    private function slug(string $name): string
    {
        return match ($name) {
            'SportyCiti Insights' => 'insights',
            'Ghana Sports' => 'ghana',
            'International Sports' => 'international',
            'Other Sports' => 'other-sports',
            default => Str::slug($name),
        };
    }
}
