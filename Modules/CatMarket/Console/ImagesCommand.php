<?php

declare(strict_types=1);

namespace Modules\CatMarket\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\CatMarket\Support\SiteImages;
use Modules\Category\Models\Category;
use Throwable;

class ImagesCommand extends Command
{
    protected $signature = 'catmarket:images {--force : Download again even if the file exists}';

    protected $description = 'Download the site images and attach them to the categories.';

    public function handle(): int
    {
        $disk = Storage::disk('public');

        foreach ((array) config('catmarket.images', []) as $name => $url) {
            $path = SiteImages::path((string) $name);

            if ($disk->exists($path) && ! $this->option('force')) {
                continue;
            }

            try {
                $response = Http::timeout(60)->get((string) $url);
            } catch (Throwable $exception) {
                $this->warn("{$name}: {$exception->getMessage()}");

                continue;
            }

            if (! $response->successful()) {
                $this->warn("{$name}: HTTP {$response->status()}");

                continue;
            }

            $jpeg = SiteImages::toJpeg($response->body());

            if ($jpeg === null) {
                $this->warn("{$name}: not an image");

                continue;
            }

            $disk->put($path, $jpeg);
            $this->info("{$name}: saved");
        }

        foreach ((array) config('catmarket.category_images', []) as $slug => $name) {
            if ($disk->exists(SiteImages::path((string) $name))) {
                Category::query()->where('slug', $slug)->update(['icon' => 'storage/'.SiteImages::path((string) $name)]);
            }
        }

        return self::SUCCESS;
    }
}
