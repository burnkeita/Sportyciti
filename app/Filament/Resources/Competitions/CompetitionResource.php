<?php

namespace App\Filament\Resources\Competitions;

use App\Jobs\SyncFootballData;
use App\Models\AuditLog;
use App\Models\Competition;
use App\Models\Sport;
use App\Services\SportsData\Providers\ApiFootballProvider;
use App\Services\SportsData\Providers\DemoProvider;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class CompetitionResource extends Resource
{
    protected static ?string $model = Competition::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|UnitEnum|null $navigationGroup = 'Football';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('short_name')->maxLength(40),
            TextInput::make('slug')->alphaDash()->unique(ignoreRecord: true)->maxLength(120)->helperText('Leave blank to generate.'),
            Select::make('type')->options(['league' => 'League', 'cup' => 'Cup / tournament'])->required()->default('league'),
            Select::make('sport_id')->relationship('sport', 'name')->required()->default(fn () => Sport::where('slug', 'football')->value('id')),
            Select::make('country_id')->relationship('country', 'name')->searchable()->preload(),
            Select::make('category_id')->label('News category')->relationship('category', 'name')->searchable()->preload()
                ->helperText('Stories in this category appear on the competition page.'),
            TextInput::make('logo_url')->url()->maxLength(255),
            Section::make('Data provider')->columnSpanFull()->columns(3)->schema([
                Select::make('provider')->options([ApiFootballProvider::KEY => 'API-Football', DemoProvider::KEY => 'Demo (fictional)'])->required(),
                TextInput::make('external_id')->label('Provider league ID')->maxLength(64)
                    ->helperText('e.g. API-Football league id. Verify in your provider dashboard.'),
                TextInput::make('current_season_year')->label('Current season (start year)')->numeric()->minValue(1990)->maxValue(2100),
                Toggle::make('sync_enabled')->label('Sync automatically'),
                Toggle::make('has_standings')->label('Has league table'),
            ]),
            Toggle::make('is_active')->label('Visible on website')->default(true),
            Toggle::make('is_featured')->label('Featured (navigation, homepage scores)'),
            Textarea::make('description')->rows(2)->maxLength(500)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        $sync = fn (string $resource, string $label) => Action::make('sync_'.$resource)
            ->label($label)
            ->icon('heroicon-o-arrow-path')
            ->action(function (Competition $record) use ($resource, $label) {
                SyncFootballData::dispatch($resource, competitionId: $record->id);
                AuditLog::record('sports_sync_requested', $record, ['resource' => $resource]);
                Notification::make()->title("{$label} queued for {$record->name}")->body('Results appear in Sync logs once the queue worker processes the job.')->success()->send();
            });

        return $table
            ->reorderable('position')
            ->defaultSort('position')
            ->modifyQueryUsing(fn ($query) => $query->with('country')->withCount('fixtures'))
            ->columns([
                TextColumn::make('name')->searchable()->description(fn (Competition $record) => $record->country?->name),
                TextColumn::make('provider')->badge()->color(fn ($state) => $state === 'demo' ? 'warning' : 'info'),
                TextColumn::make('external_id')->label('Provider ID')->placeholder('not set'),
                TextColumn::make('current_season_year')->label('Season'),
                TextColumn::make('fixtures_count')->label('Fixtures')->numeric(),
                ToggleColumn::make('is_active')->label('Live on site'),
                ToggleColumn::make('is_featured')->label('Featured'),
                IconColumn::make('sync_enabled')->label('Sync')->boolean(),
                TextColumn::make('last_synced_at')->label('Last season sync')->since()->placeholder('never'),
            ])
            ->recordActions([
                ActionGroup::make([
                    $sync('season', 'Sync fixtures'),
                    $sync('standings', 'Sync table'),
                ])->label('Refresh data')->icon('heroicon-o-arrow-path')->button()->color('gray'),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCompetitions::route('/')];
    }
}
