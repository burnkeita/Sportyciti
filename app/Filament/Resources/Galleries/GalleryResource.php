<?php

namespace App\Filament\Resources\Galleries;

use App\Filament\Support\MediaPicker;
use App\Models\Gallery;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-camera';

    protected static string|UnitEnum|null $navigationGroup = 'Multimedia';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('title')->required()->maxLength(191)->columnSpanFull(),
            TextInput::make('slug')->alphaDash()->unique(ignoreRecord: true)->maxLength(191),
            TextInput::make('photographer')->maxLength(120),
            Select::make('category_id')->relationship('category', 'name')->searchable()->preload(),
            MediaPicker::make('cover_media_id', 'cover', 'Cover image (defaults to first photo)'),
            Textarea::make('description')->rows(3)->maxLength(2000)->columnSpanFull(),
            Repeater::make('items')->relationship()->orderColumn('position')->columnSpanFull()
                ->schema([
                    Select::make('media_id')->label('Photo')
                        ->options(fn () => Media::latest()->limit(200)->pluck('title', 'id'))
                        ->getSearchResultsUsing(fn (string $search) => Media::where('title', 'like', "%{$search}%")->limit(50)->pluck('title', 'id'))
                        ->searchable()->required()
                        ->createOptionForm(MediaPicker::uploadSchema())
                        ->createOptionUsing(fn (array $data) => Media::create($data)->getKey()),
                    TextInput::make('caption')->maxLength(191),
                ])
                ->columns(2)->reorderable()->collapsible()->addActionLabel('Add photo'),
            Section::make('Rights & publishing')->columnSpanFull()->columns(3)->schema([
                Toggle::make('rights_confirmed')->label('SportyCiti holds rights to all photos')->required()->accepted(),
                Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])->default('draft')->required(),
                DateTimePicker::make('published_at')->seconds(false)->native(false)->default(now()),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->withCount('items'))
            ->columns([
                TextColumn::make('title')->searchable()->limit(60),
                TextColumn::make('items_count')->label('Photos')->numeric(),
                TextColumn::make('status')->badge()->color(fn ($state) => $state === 'published' ? 'success' : 'gray'),
                IconColumn::make('rights_confirmed')->label('Rights')->boolean(),
                TextColumn::make('published_at')->dateTime('j M Y')->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageGalleries::route('/')];
    }
}
