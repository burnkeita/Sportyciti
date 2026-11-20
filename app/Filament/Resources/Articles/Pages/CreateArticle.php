<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Services\Content\ArticleWorkflow;
use App\Support\Permissions;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (! $user->can(Permissions::EDIT_ANY_ARTICLE) || empty($data['author_id'])) {
            $data['author_id'] = $user->id;
        }

        return app(ArticleWorkflow::class)->normalise($data, $user);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
