<?php

namespace App\Filament\Resources\AuditLogs;

use App\Models\AuditLog;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Audit log';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('user.name')->placeholder('System'),
            TextEntry::make('event')->badge(),
            TextEntry::make('auditable_type')->label('Record')->formatStateUsing(fn ($state, AuditLog $record) => class_basename((string) $state).' #'.$record->auditable_id),
            TextEntry::make('ip_address')->placeholder('—'),
            TextEntry::make('user_agent')->placeholder('—'),
            KeyValueEntry::make('old_values')->columnSpanFull(),
            KeyValueEntry::make('new_values')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('user:id,name'))
            ->columns([
                TextColumn::make('created_at')->label('When')->dateTime('j M Y H:i:s')->sortable(),
                TextColumn::make('user.name')->label('User')->placeholder('System')->searchable(),
                TextColumn::make('event')->badge(),
                TextColumn::make('auditable_type')->label('Record')->formatStateUsing(fn ($state, AuditLog $record) => $state ? class_basename($state).' #'.$record->auditable_id : '—'),
                TextColumn::make('changes')->state(fn (AuditLog $record) => implode(', ', array_keys($record->new_values ?? $record->old_values ?? [])))->limit(60)->toggleable(),
                TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')->options(fn () => AuditLog::distinct()->pluck('event', 'event')),
                SelectFilter::make('auditable_type')->label('Record type')->options(fn () => AuditLog::whereNotNull('auditable_type')->distinct()->pluck('auditable_type')->mapWithKeys(fn ($t) => [$t => class_basename($t)])),
            ])
            ->recordActions([ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageAuditLogs::route('/')];
    }
}
