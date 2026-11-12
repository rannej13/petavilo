<?php

declare(strict_types=1);

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\App\Models\User;

class Reply extends Model
{
    use SoftDeletes;

    protected $table = 'community_replies';

    protected $fillable = ['thread_id', 'user_id', 'body', 'is_hidden'];

    protected $casts = ['is_hidden' => 'boolean', 'report_count' => 'integer'];

    protected static function booted(): void
    {
        $refresh = static function (self $reply): void {
            $reply->thread?->refreshCounts();
        };

        static::saved($refresh);
        static::deleted($refresh);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authorName(): string
    {
        return Community::displayName($this->user);
    }
}
