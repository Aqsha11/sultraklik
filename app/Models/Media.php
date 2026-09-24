<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'filename', 'path', 'mime_type', 'size', 'alt', 'caption', 'credit',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path);
    }

    public function getHumanSizeAttribute(): string
    {
        if ($this->size >= 1048576) {
            return number_format($this->size / 1048576, 1).' MB';
        }

        if ($this->size >= 1024) {
            return number_format($this->size / 1024, 1).' KB';
        }

        return $this->size.' B';
    }

    protected static function booted(): void
    {
        static::creating(function (Media $media) {
            if (empty($media->slug) && $media->filename) {
                $media->slug = Str::slug(pathinfo($media->filename, PATHINFO_FILENAME));
            }
        });
    }
}