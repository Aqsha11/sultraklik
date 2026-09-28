<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_REVIEW = 'review';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * Path pertama yang dipakai route sistem. Slug artikel hidup di root domain,
     * jadi nilai-nilai ini tidak boleh dipakai artikel.
     *
     * @var list<string>
     */
    public const RESERVED_SLUGS = [
        'admin', 'api', 'artikel', 'category', 'css', 'home', 'images', 'js',
        'kategori', 'komentar', 'login', 'logout', 'page', 'register', 'rss',
        'rss.xml', 'search', 'sitemap', 'sitemap.xml', 'storage', 'sultra',
        'tag', 'up', 'upload-image', 'user', 'users',
    ];

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content',
        'category_id', 'region_id', 'author_id',
        'featured_image', 'image_caption', 'image_credit',
        'status', 'published_at',
        'is_headline', 'is_featured',
        'views', 'comments_count', 'likes_count',
        'seo_title', 'seo_description', 'og_image',
        'video_url',
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

            if ($article->slug !== null) {
                $article->slug = self::normalizeSlug($article->slug);
            }

            if ($article->content !== null) {
                $article->content = HtmlSanitizer::clean($article->content);
            }

            if ($article->video_url !== null) {
                $article->video_url = self::normalizeVideoUrl($article->video_url);
            }

            if ($article->is_featured) {
                $isHeadline = $article->is_headline
                    || Headline::query()->where('article_id', $article->id)->exists();

                if ($isHeadline) {
                    $article->is_featured = false;
                }
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

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
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

        return Storage::disk('public')->url($this->featured_image);
    }

    public function getSeoImageAttribute(): ?string
    {
        return $this->og_image ?: $this->featured_image_url ?: $this->video_thumbnail_url;
    }

    /**
     * ID video YouTube, diturunkan dari video_url yang sudah dinormalisasi.
     */
    public function getYoutubeIdAttribute(): ?string
    {
        return self::youtubeId($this->video_url);
    }

    public function getVideoThumbnailUrlAttribute(): ?string
    {
        if (! $this->youtube_id) {
            return null;
        }

        return 'https://i.ytimg.com/vi/'.$this->youtube_id.'/hqdefault.jpg';
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        if (! $this->youtube_id) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/'.$this->youtube_id;
    }

    public function hasVideo(): bool
    {
        return $this->youtube_id !== null;
    }

    /**
     * Ambil ID video (11 karakter) dari berbagai bentuk URL YouTube.
     * Nilai balik null untuk apa pun yang bukan YouTube, sehingga string
     * dari input pengguna tidak pernah dipakai ulang untuk membuat HTML.
     */
    public static function youtubeId(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $url = trim($url);

        $patterns = [
            '#^(?:https?:)?//(?:www\.|m\.)?youtube(?:-nocookie)?\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})#i',
            '#^(?:https?:)?//(?:www\.)?youtu\.be/([A-Za-z0-9_-]{11})#i',
            '#^(?:https?:)?//(?:www\.|m\.)?youtube(?:-nocookie)?\.com/embed/([A-Za-z0-9_-]{11})#i',
            '#^(?:https?:)?//(?:www\.|m\.)?youtube(?:-nocookie)?\.com/shorts/([A-Za-z0-9_-]{11})#i',
            '#^(?:https?:)?//(?:www\.|m\.)?youtube(?:-nocookie)?\.com/live/([A-Za-z0-9_-]{11})#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Simpan hanya bentuk kanonik. Input kosong atau non-YouTube jadi null.
     */
    public static function normalizeVideoUrl(?string $url): ?string
    {
        $id = self::youtubeId($url);

        return $id ? 'https://www.youtube.com/watch?v='.$id : null;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->published_at?->isPast();
    }

    public static function normalizeSlug(string $slug): string
    {
        $normalized = Str::slug($slug);

        if ($normalized === '') {
            $normalized = 'artikel-'.Str::lower(Str::random(6));
        }

        if (in_array($normalized, self::RESERVED_SLUGS, true)) {
            $normalized .= '-berita';
        }

        return $normalized;
    }
}
