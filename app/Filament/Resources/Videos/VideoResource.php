<?php

namespace App\Filament\Resources\Videos;

use App\Filament\Support\MediaPicker;
use App\Models\Video;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-video-camera';

    protected static string|UnitEnum|null $navigationGroup = 'Multimedia';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('title')->required()->maxLength(191)->columnSpanFull(),
            TextInput::make('slug')->alphaDash()->unique(ignoreRecord: true)->maxLength(191)->helperText('Leave blank to generate.'),
            Select::make('video_type')->options(Video::TYPES)->required()->default('interview'),
            Select::make('source')->options(['youtube' => 'YouTube', 'upload' => 'Uploaded file'])->required()->default('youtube')->live(),
            TextInput::make('youtube_id')->label('YouTube URL or ID')
                ->visible(fn (Get $get) => $get('source') === 'youtube')
                ->required(fn (Get $get) => $get('source') === 'youtube')
                ->dehydrateStateUsing(fn (?string $state) => Video::parseYoutubeId($state))
                ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                    if ($value && ! Video::parseYoutubeId($value)) {
                        $fail('Enter a valid YouTube link or 11-character video ID.');
                    }
                }),
            FileUpload::make('file_path')->label('Video file')->disk('public')->directory('videos')->visibility('public')
                ->acceptedFileTypes(['video/mp4', 'video/webm'])->maxSize(512000)
                ->visible(fn (Get $get) => $get('source') === 'upload')
                ->required(fn (Get $get) => $get('source') === 'upload'),
            MediaPicker::make('thumbnail_media_id', 'thumbnail', 'Thumbnail (optional for YouTube)'),
            Select::make('category_id')->relationship('category', 'name')->searchable()->preload(),
            TextInput::make('duration_seconds')->label('Duration (seconds)')->numeric()->minValue(1),
            Textarea::make('description')->rows(4)->maxLength(5000)->columnSpanFull(),
            Section::make('Rights & publishing')->columnSpanFull()->columns(3)->schema([
                Toggle::make('rights_confirmed')->label('SportyCiti holds distribution rights')->required()->accepted()
                    ->helperText('Do not publish match footage without a licence.'),
                TextInput::make('rights_holder')->maxLength(120),
                Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])->default('draft')->required(),
                DateTimePicker::make('published_at')->seconds(false)->native(false)->default(now()),
                Toggle::make('is_featured')->label('Featured'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->limit(60)->description(fn (Video $record) => $record->typeLabel()),
                TextColumn::make('source')->badge(),
                TextColumn::make('status')->badge()->color(fn ($state) => $state === 'published' ? 'success' : 'gray'),
                IconColumn::make('rights_confirmed')->label('Rights')->boolean(),
                ToggleColumn::make('is_featured')->label('Featured'),
                TextColumn::make('view_count')->label('Views')->numeric()->sortable(),
                TextColumn::make('published_at')->dateTime('j M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('video_type')->label('Type')->options(Video::TYPES),
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageVideos::route('/')];
    }
}
