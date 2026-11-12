<?php

declare(strict_types=1);

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\User\App\Models\User;

class Thread extends Model
{
    use SoftDeletes;

    protected $table = 'community_threads';

    protected $fillable = ['board_id', 'user_id', 'title', 'slug', 'body', 'country', 'is_pinned', 'is_locked', 'is_hidden', 'last_post_at'];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'is_hidden' => 'boolean',
        'reply_count' => 'integer',
        'view_count' => 'integer',
        'report_count' => 'integer',
        'last_post_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(static function (self $thread): void {
            $thread->setAttribute('slug', Str::limit(Str::slug((string) $thread->getAttribute('title')), 160, '') ?: 'thread');

            if ($thread->getAttribute('last_post_at') === null) {
                $thread->setAttribute('last_post_at', now());
            }
        });
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'thread_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }

    public function url(): string
    {
        return route('community.thread', ['thread' => $this->getKey(), 'slug' => $this->getAttribute('slug')]);
    }

    public function refreshCounts(): void
    {
        $visible = $this->replies()->where('is_hidden', false);
        $this->forceFill([
            'reply_count' => (clone $visible)->count(),
            'last_post_at' => (clone $visible)->max('created_at') ?? $this->getAttribute('created_at'),
        ])->saveQuietly();
    }

    public function authorName(): string
    {
        return Community::displayName($this->user);
    }
}
