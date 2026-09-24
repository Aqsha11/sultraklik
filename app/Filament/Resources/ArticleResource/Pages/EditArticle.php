<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === Article::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('preview')
                ->label('Lihat di Website')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => $this->record->isPublished()
                    ? url($this->record->slug)
                    : null)
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->isPublished()),
        ];
    }
}