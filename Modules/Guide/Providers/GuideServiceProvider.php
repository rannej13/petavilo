<?php

declare(strict_types=1);

namespace Modules\Guide\Providers;

use App\Support\SitemapRegistry;
use Illuminate\Support\ServiceProvider;
use Modules\Guide\Models\Guide;

class GuideServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Guide', 'Database/migrations'));
        $this->loadRoutesFrom(module_path('Guide', 'routes/web.php'));
        $this->loadViewsFrom(module_path('Guide', 'resources/views'), 'guide');
        $this->loadTranslationsFrom(module_path('Guide', 'lang'), 'guide');

        SitemapRegistry::register(static fn (): array => [
            ['url' => route('guides.index')],
            ...Guide::query()->published()->get(['slug', 'updated_at'])->map(static fn (Guide $guide): array => [
                'url' => route('guides.show', $guide->getAttribute('slug')),
                'updated' => $guide->getAttribute('updated_at')?->toAtomString(),
            ])->all(),
        ]);
    }
}
