<?php

declare(strict_types=1);

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Board extends Model
{
    protected $table = 'community_boards';

    protected $fillable = ['name', 'slug', 'description', 'image', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    protected static function booted(): void
    {
        static::saving(static function (self $board): void {
            if (blank($board->getAttribute('slug'))) {
                $board->setAttribute('slug', Str::slug((string) $board->getAttribute('name')));
            }
        });
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class, 'board_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function imageUrl(): ?string
    {
        $name = trim((string) $this->getAttribute('image'));

        return $name === '' ? null : \Modules\CatMarket\Support\SiteImages::url($name);
    }
}
