<?php

namespace App\Filament\Resources\SyncLogs;

use App\Models\SyncLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SyncLogResource extends Resource
{
    protected static ?string $model = SyncLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-signal';

    protected static string|UnitEnum|null $navigationGroup = 'Football';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Sync logs';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->columns([
                TextColumn::make('created_at')->label('When')->dateTime('j M H:i:s')->sortable(),
                TextColumn::make('provider')->badge()->color(fn ($state) => $state === 'demo' ? 'warning' : 'info'),
                TextColumn::make('resource'),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'success' => 'success', 'failed' => 'danger', default => 'warning'
                }),
                TextColumn::make('records')->numeric(),
                TextColumn::make('duration_ms')->label('Duration')->suffix(' ms')->numeric(),
                TextColumn::make('message')->wrap()->limit(120)->placeholder('—'),
                TextColumn::make('context')->formatStateUsing(fn ($state) => is_array($state) ? collect($state)->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ') : $state)->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['success' => 'Success', 'failed' => 'Failed', 'skipped' => 'Skipped']),
                SelectFilter::make('resource')->options(fn () => SyncLog::distinct()->pluck('resource', 'resource')),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageSyncLogs::route('/')];
    }
}
