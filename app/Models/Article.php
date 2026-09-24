<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_REVIEW = 'review';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content',
        'category_id', 'region_id', 'author_id',
        'featured_image', 'image_caption', 'image_credit',
        'status', 'published_at',
        'is_headline', 'is_featured',
        'views', 'comments_count',
        'seo_title', 'seo_description', 'og_image',
    ];

    protected $casts = [
        'is_headline' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Article $article) {
            if (empty($article->slug) && $article->title) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('is_approved', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('published_at');
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (! $this->featured_image) {
            return null;
        }

        if (Str::startsWith($this->featured_image, ['http://', 'https://'])) {
            return $this->featured_image;
        }

        return asset('storage/'.$this->featured_image);
    }

    public function getSeoImageAttribute(): ?string
    {
        return $this->og_image ?: $this->featured_image_url;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->published_at?->isPast();
    }
}