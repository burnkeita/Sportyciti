<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Text;

/**
 * Reusable editorial components for SportyCiti Insights.
 *
 * Every block that displays figures requires a named source and an explicit
 * "verified" confirmation; unverified data blocks are never rendered publicly.
 * Form, head-to-head and match information blocks pull from the licensed sports
 * data feed when the article is linked to a fixture.
 */
class InsightBlocks
{
    public static function make(): Builder
    {
        return Builder::make('insight_blocks')
            ->label('Insight components')
            ->blocks([
                Block::make('key_takeaways')->label('Key takeaways')->icon('heroicon-o-light-bulb')->schema([
                    TextInput::make('heading')->default('Key takeaways')->maxLength(80),
                    Repeater::make('items')->simple(TextInput::make('text')->required()->maxLength(240))->minItems(1)->maxItems(8)->defaultItems(3),
                ]),

                Block::make('stat_table')->label('Statistics table')->icon('heroicon-o-table-cells')->schema([
                    TextInput::make('title')->required()->maxLength(120),
                    Grid::make(5)->schema([
                        TextInput::make('label_heading')->label('Row label heading')->placeholder('Metric'),
                        TextInput::make('col1')->label('Column 1')->required(),
                        TextInput::make('col2')->label('Column 2'),
                        TextInput::make('col3')->label('Column 3'),
                        TextInput::make('col4')->label('Column 4'),
                    ]),
                    Repeater::make('rows')->schema([
                        Grid::make(5)->schema([
                            TextInput::make('label')->required(),
                            TextInput::make('v1'), TextInput::make('v2'), TextInput::make('v3'), TextInput::make('v4'),
                        ]),
                    ])->minItems(1)->maxItems(30)->collapsible(),
                    ...self::sourceFields(),
                ]),

                Block::make('team_comparison')->label('Team comparison')->icon('heroicon-o-scale')->schema([
                    TextInput::make('title')->default('Head to head in numbers')->maxLength(120),
                    Grid::make(2)->schema([
                        TextInput::make('team_a')->required()->maxLength(60),
                        TextInput::make('team_b')->required()->maxLength(60),
                    ]),
                    Repeater::make('rows')->schema([
                        Grid::make(3)->schema([
                            TextInput::make('metric')->required(),
                            TextInput::make('a')->label('Team A value')->required(),
                            TextInput::make('b')->label('Team B value')->required(),
                        ]),
                    ])->minItems(1)->maxItems(20),
                    ...self::sourceFields(),
                ]),

                Block::make('player_stats')->label('Player statistics')->icon('heroicon-o-user')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('player')->required()->maxLength(80),
                        TextInput::make('team')->maxLength(80),
                        TextInput::make('context')->placeholder('e.g. 2026/27 league season')->maxLength(80),
                    ]),
                    Repeater::make('rows')->schema([
                        Grid::make(2)->schema([
                            TextInput::make('metric')->required(),
                            TextInput::make('value')->required(),
                        ]),
                    ])->minItems(1)->maxItems(12),
                    ...self::sourceFields(),
                ]),

                Block::make('chart')->label('Bar chart')->icon('heroicon-o-chart-bar')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('title')->required()->maxLength(120),
                        TextInput::make('suffix')->label('Value suffix')->placeholder('%, goals, xG…')->maxLength(12),
                    ]),
                    Repeater::make('rows')->schema([
                        Grid::make(2)->schema([
                            TextInput::make('label')->required(),
                            TextInput::make('value')->numeric()->required(),
                        ]),
                    ])->minItems(2)->maxItems(20),
                    TextInput::make('caption')->maxLength(200),
                    ...self::sourceFields(),
                ]),

                Block::make('formation')->label('Tactical formation diagram')->icon('heroicon-o-squares-2x2')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('team')->required()->maxLength(60),
                        TextInput::make('formation')->required()->regex('/^\d(-\d){1,4}$/')->placeholder('4-3-3'),
                        TextInput::make('label')->placeholder('e.g. In possession')->maxLength(60),
                    ]),
                    Repeater::make('players')->helperText('Goalkeeper first, then defenders → attackers, matching the formation.')->schema([
                        Grid::make(2)->schema([
                            TextInput::make('number')->numeric()->maxLength(3),
                            TextInput::make('name')->required()->maxLength(40),
                        ]),
                    ])->minItems(11)->maxItems(11)->defaultItems(11)->collapsible()->collapsed(),
                    Textarea::make('notes')->rows(2)->maxLength(400),
                ]),

                Block::make('match_info')->label('Match information')->icon('heroicon-o-calendar')->schema([
                    TextInput::make('title')->default('Match information'),
                    Text::make('Linked fixture data (from the sports data provider) is used automatically when the article has a linked match. Fill the fields below only for matches not covered by the provider.'),
                    Grid::make(3)->schema([
                        TextInput::make('home'), TextInput::make('away'), TextInput::make('competition'),
                        TextInput::make('date'), TextInput::make('venue'),
                    ]),
                    ...self::sourceFields(required: false),
                ]),

                Block::make('recent_form')->label('Recent form (from data feed)')->icon('heroicon-o-arrow-trending-up')->schema([
                    Text::make('Shows both teams’ last results from the licensed data feed. Requires a linked match.'),
                    TextInput::make('title')->default('Recent form'),
                    TextInput::make('matches')->numeric()->minValue(3)->maxValue(10)->default(5),
                ]),

                Block::make('head_to_head')->label('Head-to-head (from data feed)')->icon('heroicon-o-arrows-right-left')->schema([
                    Text::make('Lists previous meetings stored from the licensed data feed. Requires a linked match.'),
                    TextInput::make('title')->default('Head-to-head'),
                    TextInput::make('matches')->numeric()->minValue(1)->maxValue(10)->default(6),
                ]),

                Block::make('expert_quote')->label('Expert commentary')->icon('heroicon-o-chat-bubble-bottom-center-text')->schema([
                    Textarea::make('quote')->required()->rows(3)->maxLength(600),
                    Grid::make(2)->schema([
                        TextInput::make('name')->required()->maxLength(80),
                        TextInput::make('role')->maxLength(120),
                    ]),
                ]),

                Block::make('references')->label('Supporting references')->icon('heroicon-o-link')->schema([
                    Repeater::make('items')->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')->required()->maxLength(200),
                            TextInput::make('url')->url()->maxLength(500),
                        ]),
                    ])->minItems(1)->maxItems(20),
                ]),
            ])
            ->collapsible()
            ->blockNumbers(false)
            ->addActionLabel('Add insight component');
    }

    /** @return array<Field> */
    private static function sourceFields(bool $required = true): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('source')->label('Data source')->required($required)->maxLength(120)
                    ->placeholder('e.g. API-Football, club statement, SportyCiti research'),
                TextInput::make('source_url')->label('Source URL')->url()->maxLength(500),
            ]),
            Toggle::make('verified')
                ->label('I confirm these figures come from the stated source and have been checked by an editor')
                ->helperText('Unverified data blocks are not shown on the website.')
                ->accepted($required)
                ->default(false),
        ];
    }
}
