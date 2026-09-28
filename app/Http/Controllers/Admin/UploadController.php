<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /** @var array<string, string> */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function image(Request $request): JsonResponse
    {
        abort_unless((bool) $request->user()?->isEditor(), 403);

        $request->validate([
            'upload' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $file = $request->file('upload');
        $extension = $this->detectExtension($file->getRealPath());

        if ($extension === null) {
            return response()->json(['message' => 'Format gambar tidak didukung.'], 422);
        }

        $basename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = Str::limit($basename, 60, '').'-'.Str::random(16).'.'.$extension;
        $path = $file->storeAs('uploads/ckeditor', $filename, 'public');

        if ($path === false) {
            return response()->json(['message' => 'Gagal menyimpan gambar.'], 500);
        }

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    private function detectExtension(string $path): ?string
    {
        $size = @getimagesize($path);

        if ($size === false || empty($size['mime'])) {
            return null;
        }

        $detected = strtolower($size['mime']);

        return self::ALLOWED_MIME_TYPES[$detected] ?? null;
    }
}
