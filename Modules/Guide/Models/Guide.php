<?php

declare(strict_types=1);

namespace Modules\Guide\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class Guide extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'care' => 'Cat care',
        'breed' => 'Breed guides',
        'buying' => 'Adopting and buying',
        'comparison' => 'Product comparisons',
        'insurance' => 'Insurance explained',
    ];

    protected $fillable = [
        'title', 'slug', 'type', 'excerpt', 'cover_image', 'body', 'has_affiliate_links', 'sponsor_name', 'author_name',
        'meta_title', 'meta_description', 'reviewed_on', 'is_published', 'published_at',
    ];

    protected $casts = [
        'has_affiliate_links' => 'boolean',
        'is_published' => 'boolean',
        'reviewed_on' => 'date',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(static function (self $guide): void {
            if (blank($guide->getAttribute('slug'))) {
                $guide->setAttribute('slug', Str::slug((string) $guide->getAttribute('title')));
            }

            if ($guide->getAttribute('is_published') && $guide->getAttribute('published_at') === null) {
                $guide->setAttribute('published_at', now());
            }

            if (str_contains((string) $guide->getAttribute('body'), 'amzn.to') || str_contains((string) $guide->getAttribute('body'), 'amazon.co.uk')) {
                $guide->setAttribute('has_affiliate_links', true);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('published_at', '<=', now());
    }

    public static function browse(?string $type): LengthAwarePaginator
    {
        return static::query()
            ->published()
            ->when($type !== null, static fn (Builder $query): Builder => $query->where('type', $type))
            ->latest('published_at')
            ->paginate(18)
            ->withQueryString();
    }

    public static function findPublished(string $slug): ?self
    {
        return static::query()->published()->where('slug', $slug)->first();
    }

    public function related(int $limit = 3): Collection
    {
        return static::query()->published()
            ->where('type', $this->getAttribute('type'))
            ->whereKeyNot($this->getKey())
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function coverUrl(): ?string
    {
        $path = trim((string) $this->getAttribute('cover_image'));

        if ($path !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return asset('storage/'.ltrim($path, '/'));
        }

        return \Modules\CatMarket\Support\SiteImages::url('guide-'.(string) $this->getAttribute('type'))
            ?? \Modules\CatMarket\Support\SiteImages::url('hero');
    }

    public function typeLabel(): string
    {
        return self::TYPES[(string) $this->getAttribute('type')] ?? Str::headline((string) $this->getAttribute('type'));
    }

    public function metaTitleValue(): string
    {
        return filled($this->getAttribute('meta_title')) ? (string) $this->getAttribute('meta_title') : (string) $this->getAttribute('title');
    }

    public function metaDescriptionValue(): string
    {
        return filled($this->getAttribute('meta_description')) ? (string) $this->getAttribute('meta_description') : (string) $this->getAttribute('excerpt');
    }
}
