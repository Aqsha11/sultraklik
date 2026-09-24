<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Like;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request, Article $article): JsonResponse
    {
        if (! $article->isPublished()) {
            abort(404);
        }

        $ip = $request->ip();
        $existing = Like::where('article_id', $article->id)->where('ip_address', $ip)->first();

        if ($existing) {
            $existing->delete();
            $article->decrement('likes_count');
            $liked = false;
        } else {
            Like::create([
                'article_id' => $article->id,
                'user_id' => $request->user()?->id,
                'ip_address' => $ip,
            ]);
            $article->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'likes' => $article->fresh()->likes_count,
            'liked' => $liked,
        ]);
    }
}
