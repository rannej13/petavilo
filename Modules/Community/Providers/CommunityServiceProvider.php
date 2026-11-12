<?php

declare(strict_types=1);

namespace Modules\Community\Providers;

use App\Support\SitemapRegistry;
use Illuminate\Support\ServiceProvider;
use Modules\Community\Models\Board;
use Modules\Community\Models\Thread;

class CommunityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Community', 'Database/migrations'));
        $this->loadRoutesFrom(module_path('Community', 'routes/web.php'));
        $this->loadViewsFrom(module_path('Community', 'resources/views'), 'community');
        $this->loadTranslationsFrom(module_path('Community', 'lang'), 'community');

        SitemapRegistry::register(static fn (): array => [
            ['url' => route('community.index')],
            ...Board::query()->active()->get(['slug'])->map(static fn (Board $board): array => [
                'url' => route('community.board', $board->getAttribute('slug')),
            ])->all(),
            ...Thread::query()->visible()->latest('last_post_at')->limit(5000)->get(['id', 'slug', 'last_post_at'])->map(static fn (Thread $thread): array => [
                'url' => $thread->url(),
                'updated' => $thread->getAttribute('last_post_at')?->toAtomString(),
            ])->all(),
        ]);
    }
}
