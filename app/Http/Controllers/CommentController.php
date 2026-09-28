<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\Turnstile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CommentController extends Controller
{
    private const SUBMISSION_LIMIT = 5;

    private const SUBMISSION_DECAY = 3600;

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

        $limitKey = $this->rateLimitKey($request);

        if (RateLimiter::tooManyAttempts($limitKey, self::SUBMISSION_LIMIT)) {
            return back()->with(
                'comment_error',
                'Terlalu banyak komentar dikirim dari perangkat ini. Silakan coba lagi nanti.'
            );
        }

        // Cloudflare Turnstile. Bot yang lolos honeypot masih tertangkap di sini.
        // Pesan errornya sengaja disamakan dengan pesan sukses, supaya bot
        // tidak belajar bahwa ini filter.
        if (! Turnstile::verify($request, Turnstile::ACTION_COMMENT)) {
            return back()->with('comment_status', 'Komentar berhasil dikirim dan akan tampil setelah disetujui redaksi.');
        }

        // Anti-bot: honeypot teks harus kosong & form minimal ~3 detik sejak dimuat.
        if (filled($request->input('website'))
            || (is_numeric($request->input('formed_at'))
                && (time() - (int) $request->input('formed_at')) < 3)) {
            return back()->with('comment_status', 'Komentar berhasil dikirim dan akan tampil setelah disetujui redaksi.');
        }

        $article->comments()->create([
            'name' => trim($data['name']),
            'email' => isset($data['email']) && $data['email'] !== '' ? trim($data['email']) : null,
            'body' => trim($data['body']),
            'is_approved' => false,
        ]);

        $article->increment('comments_count');
        RateLimiter::hit($limitKey, self::SUBMISSION_DECAY);

        return back()->with('comment_status', 'Komentar berhasil dikirim dan akan tampil setelah disetujui redaksi.');
    }

    private function rateLimitKey(Request $request): string
    {
        return 'comment-submit:'.sha1($request->ip().'|'.$request->userAgent());
    }
}
