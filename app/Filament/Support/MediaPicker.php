<?php

namespace App\Filament\Support;

use App\Models\Media;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

/**
 * Reusable media-library picker: search existing media or upload a new image
 * (with alt text, caption, credit and rights confirmation) inline.
 */
class MediaPicker
{
    public static function make(string $field, string $relationship, string $label = 'Image'): Select
    {
        return Select::make($field)
            ->label($label)
            ->relationship($relationship, 'title')
            ->searchable()
            ->preload(false)
            ->getOptionLabelFromRecordUsing(fn (Media $record) => $record->title.($record->credit ? " — {$record->credit}" : ''))
            ->createOptionForm(self::uploadSchema())
            ->createOptionModalHeading('Upload to media library');
    }

    /** @return array<Field> */
    public static function uploadSchema(): array
    {
        return [
            FileUpload::make('path')
                ->label('Image file')
                ->image()
                ->disk('public')
                ->directory(fn () => 'media/'.now()->format('Y/m'))
                ->visibility('public')
                ->maxSize(8192)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                ->imageEditor()
                ->required(),
            TextInput::make('title')->maxLength(191)->helperText('Internal name used to find this image later.'),
            TextInput::make('alt_text')->label('Alt text')->required()->maxLength(191)->helperText('Describe the image for screen readers and search engines.'),
            TextInput::make('caption')->maxLength(191),
            TextInput::make('credit')->label('Photo credit')->maxLength(191),
            Toggle::make('rights_confirmed')->label('SportyCiti has the right to publish this image')->accepted()->required(),
        ];
    }
}
