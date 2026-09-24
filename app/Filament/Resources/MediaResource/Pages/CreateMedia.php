<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->hydrateFileData($data);
    }

    private function hydrateFileData(array $data): array
    {
        $data['user_id'] = $data['user_id'] ?? auth()->id();

        $path = $data['path'] ?? null;
        if ($path) {
            $disk = Storage::disk('public');
            $data['mime_type'] = $disk->mimeType($path) ?? 'application/octet-stream';
            $data['size'] = $disk->size($path) ?? 0;
            $data['filename'] = $data['filename'] ?: pathinfo($path, PATHINFO_FILENAME);
        }

        return $data;
    }
}
