<?php

namespace App\Filament\Resources\Teams;

use App\Models\Team;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Teams are created by the data sync. Editors may enrich profile fields
 * (short name, venue, founded) but provider identity fields are read-only.
 */
class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup = 'Football';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('short_name')->maxLength(40),
            TextInput::make('slug')->alphaDash()->unique(ignoreRecord: true)->maxLength(120),
            TextInput::make('code')->maxLength(10),
            Select::make('country_id')->relationship('country', 'name')->searchable()->preload(),
            TextInput::make('logo_url')->url()->maxLength(255),
            TextInput::make('venue_name')->maxLength(120),
            TextInput::make('venue_city')->maxLength(120),
            TextInput::make('founded')->numeric()->minValue(1850)->maxValue(2100),
            Toggle::make('is_national')->label('National team'),
            TextInput::make('provider')->disabled()->dehydrated(false),
            TextInput::make('external_id')->label('Provider team ID')->disabled()->dehydrated(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                ImageColumn::make('logo_url')->label('')->size(28),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('country.name')->placeholder('—'),
                TextColumn::make('provider')->badge()->color(fn ($state) => $state === 'demo' ? 'warning' : 'info'),
                TextColumn::make('external_id')->label('Provider ID')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('provider')->options(['api_football' => 'API-Football', 'demo' => 'Demo']),
                SelectFilter::make('competition')->relationship('competitions', 'name'),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageTeams::route('/')];
    }
}
