<?php

namespace App\Filament\Resources\HomepageSections;

use App\Models\AdPlacement;
use App\Models\HomepageSection;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class HomepageSectionResource extends Resource
{
    protected static ?string $model = HomepageSection::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-plus';

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Homepage sections';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('title')->required()->maxLength(120),
            Select::make('type')->options(HomepageSection::TYPES)->required()->live(),
            Select::make('category_id')->label('Category')->relationship('category', 'name')->searchable()->preload()
                ->visible(fn (Get $get) => in_array($get('type'), ['category', 'previews', 'insights'], true))
                ->required(fn (Get $get) => $get('type') === 'previews'),
            Toggle::make('options.football')->label('Use all football categories')
                ->visible(fn (Get $get) => $get('type') === 'category'),
            Select::make('options.placement')->label('Ad placement')
                ->options(fn () => AdPlacement::pluck('name', 'key'))
                ->visible(fn (Get $get) => $get('type') === 'ad'),
            TextInput::make('item_limit')->label('Number of items')->numeric()->minValue(1)->maxValue(24)->default(6),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('position')
            ->defaultSort('position')
            ->paginated(false)
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('type')->formatStateUsing(fn ($state) => HomepageSection::TYPES[$state] ?? $state)->badge()->color('gray'),
                TextColumn::make('category.name')->placeholder('—'),
                TextColumn::make('item_limit')->label('Items'),
                ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageHomepageSections::route('/')];
    }
}
