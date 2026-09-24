<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_id'] = $data['author_id'] ?? auth()->id();

        if (($data['status'] ?? null) === Article::STATUS_PUBLISHED) {
            $data['published_at'] = $data['published_at'] ?? now();
        } elseif (empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}