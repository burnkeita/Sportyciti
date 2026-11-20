<?php

namespace App\Filament\Resources\Fixtures;

use App\Enums\FixtureStatus;
use App\Jobs\SyncFootballData;
use App\Models\Fixture;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Fixtures come exclusively from the sports data provider and are not edited by hand
 * (avoids duplicating provider data). Editors can feature fixtures and request a refresh.
 */
class FixtureResource extends Resource
{
    protected static ?string $model = Fixture::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Football';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Fixtures & featured matches';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('kickoff_at', 'asc')
            ->modifyQueryUsing(fn ($query) => $query->with(['homeTeam:id,name', 'awayTeam:id,name', 'competition:id,name']))
            ->columns([
                TextColumn::make('kickoff_at')->label('Kick-off (UTC)')->dateTime('D j M, H:i')->sortable(),
                TextColumn::make('match')->state(fn (Fixture $record) => "{$record->homeTeam?->name} vs {$record->awayTeam?->name}")
                    ->searchable(query: fn ($query, string $search) => $query->where(fn ($q) => $q
                        ->whereHas('homeTeam', fn ($t) => $t->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('awayTeam', fn ($t) => $t->where('name', 'like', "%{$search}%")))),
                TextColumn::make('score')->state(fn (Fixture $record) => $record->home_score === null ? '—' : "{$record->home_score} – {$record->away_score}"),
                TextColumn::make('status')->badge()->color(fn ($state) => FixtureStatus::isLive($state) ? 'danger' : (FixtureStatus::isFinished($state) ? 'gray' : 'info')),
                TextColumn::make('competition.name')->label('Competition'),
                ToggleColumn::make('is_featured')->label('Featured'),
                TextColumn::make('provider')->badge()->color(fn ($state) => $state === 'demo' ? 'warning' : 'info')->toggleable(),
                TextColumn::make('details_synced_at')->label('Details synced')->since()->placeholder('never')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('competition_id')->label('Competition')->relationship('competition', 'name'),
                Filter::make('upcoming')->label('Upcoming only')->query(fn ($query) => $query->where('kickoff_at', '>=', now()->subHours(3)))->default(),
                Filter::make('live')->label('Live now')->query(fn ($query) => $query->whereIn('status', FixtureStatus::LIVE)),
                TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->recordActions([
                Action::make('refresh')
                    ->label('Refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(function (Fixture $record) {
                        SyncFootballData::dispatch('fixture', fixtureId: $record->id);
                        Notification::make()->title('Refresh queued')->success()->send();
                    }),
                Action::make('open')->label('Match centre')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                    ->url(fn (Fixture $record) => $record->url(), shouldOpenInNewTab: true),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageFixtures::route('/')];
    }
}
