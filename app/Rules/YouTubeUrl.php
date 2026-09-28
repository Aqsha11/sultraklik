<?php

namespace App\Rules;

use App\Models\Article;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class YouTubeUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (! is_string($value) || Article::youtubeId($value) === null) {
            $fail('Tautan harus URL YouTube yang valid: youtube.com/watch, youtu.be, /shorts, /live, atau /embed.');
        }
    }
}
