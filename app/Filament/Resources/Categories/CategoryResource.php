<?php

namespace App\Filament\Resources\Categories;

use App\Models\Category;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|UnitEnum|null $navigationGroup = 'Newsroom';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('slug')->maxLength(120)->alphaDash()->unique(ignoreRecord: true)->helperText('Leave blank to generate.'),
            Select::make('parent_id')->label('Parent category')
                ->relationship('parent', 'name', fn ($query, ?Category $record) => $query->whereNull('parent_id')->when($record, fn ($q) => $q->whereKeyNot($record->id)))
                ->placeholder('— Top-level category —'),
            ColorPicker::make('color'),
            Textarea::make('description')->rows(2)->maxLength(500)->columnSpanFull(),
            Toggle::make('is_active')->default(true),
            Toggle::make('show_in_nav')->label('Show in navigation')->default(true),
            Toggle::make('is_football')->label('Football coverage')->helperText('Stories here appear in the Football hub.'),
            Section::make('SEO')->collapsed()->columnSpanFull()->schema([
                TextInput::make('seo_title')->maxLength(70),
                Textarea::make('meta_description')->rows(2)->maxLength(320),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('position')
            ->defaultSort('position')
            ->modifyQueryUsing(fn ($query) => $query->with('parent')->withCount('articles'))
            ->columns([
                TextColumn::make('name')->searchable()
                    ->formatStateUsing(fn (string $state, Category $record) => $record->parent_id ? '— '.$state : $state)
                    ->weight(fn (Category $record) => $record->parent_id ? null : 'bold'),
                TextColumn::make('parent.name')->label('Parent')->placeholder('Top level'),
                TextColumn::make('slug')->color('gray'),
                TextColumn::make('articles_count')->label('Articles')->numeric(),
                IconColumn::make('is_active')->boolean(),
                IconColumn::make('show_in_nav')->label('In nav')->boolean(),
                IconColumn::make('is_football')->label('Football')->boolean(),
            ])
            ->filters([
                SelectFilter::make('parent_id')->label('Parent')->options(fn () => Category::whereNull('parent_id')->pluck('name', 'id')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (Category $record, DeleteAction $action) {
                        if ($record->articles()->exists() || $record->children()->exists()) {
                            Notification::make()->title('Category in use')->body('Move its articles and subcategories first.')->danger()->send();
                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCategories::route('/')];
    }
}
