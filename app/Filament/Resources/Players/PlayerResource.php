<?php

namespace App\Filament\Resources\Players;

use App\Models\Player;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Player profiles used by transfer stories (created manually or by future squad syncs). */
class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'Football';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('slug')->alphaDash()->unique(ignoreRecord: true)->maxLength(120)->helperText('Leave blank to generate.'),
            Select::make('team_id')->label('Current club')->relationship('team', 'name')->searchable(),
            Select::make('nationality_id')->label('Nationality')->relationship('nationality', 'name')->searchable()->preload(),
            Select::make('position')->options(['Goalkeeper' => 'Goalkeeper', 'Defender' => 'Defender', 'Midfielder' => 'Midfielder', 'Forward' => 'Forward']),
            DatePicker::make('birth_date')->native(false),
            TextInput::make('photo_url')->url()->maxLength(255)->columnSpanFull(),
            Textarea::make('bio')->rows(3)->maxLength(2000)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('team.name')->label('Club')->placeholder('—'),
                TextColumn::make('nationality.name')->placeholder('—'),
                TextColumn::make('position')->placeholder('—'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePlayers::route('/')];
    }
}
