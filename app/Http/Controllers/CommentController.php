<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Article $article): RedirectResponse
    {
        if (! $article->isPublished()) {
            abort(404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $article->comments()->create([
            'name' => trim($data['name']),
            'email' => isset($data['email']) && $data['email'] !== '' ? trim($data['email']) : null,
            'body' => trim($data['body']),
            'is_approved' => false,
        ]);

        $article->increment('comments_count');

        return back()->with('comment_status', 'Komentar berhasil dikirim dan akan tampil setelah disetujui redaksi.');
    }
}
