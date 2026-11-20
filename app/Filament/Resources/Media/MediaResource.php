<?php

namespace App\Filament\Resources\Media;

use App\Filament\Support\MediaPicker;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use UnitEnum;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $modelLabel = 'media item';

    protected static ?string $pluralModelLabel = 'Media library';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|UnitEnum|null $navigationGroup = 'Newsroom';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(MediaPicker::uploadSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('uploader:id,name'))
            ->columns([
                ImageColumn::make('path')->label('')->disk('public')->square()->size(64),
                TextColumn::make('title')->searchable()->description(fn (Media $record) => $record->alt_text)->wrap(),
                TextColumn::make('credit')->searchable()->placeholder('—'),
                TextColumn::make('dimensions')->state(fn (Media $record) => $record->width ? "{$record->width}×{$record->height}" : 'processing…'),
                TextColumn::make('size')->formatStateUsing(fn ($state) => Number::fileSize((int) $state)),
                IconColumn::make('rights_confirmed')->label('Rights')->boolean(),
                TextColumn::make('uploader.name')->label('Uploaded by')->toggleable(),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                Filter::make('missing_alt')->label('Missing alt text')->query(fn ($query) => $query->whereNull('alt_text')),
                Filter::make('mine')->label('My uploads')->query(fn ($query) => $query->where('uploaded_by', auth()->id())),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMedia::route('/')];
    }
}
