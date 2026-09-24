<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function image(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'upload' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $file = $request->file('upload');
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'-'.uniqid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/ckeditor', $filename, 'public');

        return response()->json([
            'url' => asset('storage/'.$path),
        ]);
    }
}