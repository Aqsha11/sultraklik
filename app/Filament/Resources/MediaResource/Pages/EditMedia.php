<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $path = $data['path'] ?? null;
        if ($path && $path !== $this->record->path) {
            $disk = Storage::disk('public');
            $data['mime_type'] = $disk->mimeType($path) ?? $this->record->mime_type;
            $data['size'] = $disk->size($path) ?? $this->record->size;
            $data['filename'] = $data['filename'] ?: pathinfo($path, PATHINFO_FILENAME);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
