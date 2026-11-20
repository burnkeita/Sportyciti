<?php

namespace App\Filament\Resources\AdPlacements;

use App\Models\AdPlacement;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class AdPlacementResource extends Resource
{
    protected static ?string $model = AdPlacement::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';

    protected static string|UnitEnum|null $navigationGroup = 'Advertising';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Placements';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('key')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(60)
                ->helperText('Referenced by theme templates — do not change for built-in placements.'),
            TextInput::make('size_hint')->maxLength(40),
            TextInput::make('description')->maxLength(191),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('advertisements'))
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('key')->badge()->color('gray'),
                TextColumn::make('size_hint')->label('Size'),
                TextColumn::make('advertisements_count')->label('Campaigns')->numeric(),
                ToggleColumn::make('is_active')->label('Enabled'),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageAdPlacements::route('/')];
    }
}
