<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'position', 'type', 'image', 'code', 'url',
        'starts_at', 'ends_at', 'is_active', 'clicks',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query, string $position): Builder
    {
        return $query->where('is_active', true)
            ->where('position', $position)
            ->where(function (Builder $q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return str_starts_with($this->image, ['http://', 'https://'])
            ? $this->image
            : asset('storage/'.$this->image);
    }
}