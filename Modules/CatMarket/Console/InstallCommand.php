<?php

declare(strict_types=1);

namespace Modules\CatMarket\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\CatMarket\Database\Seeders\AdminUserSeeder;
use Modules\CatMarket\Database\Seeders\CatMarketSeeder;

class InstallCommand extends Command
{
    private const MARKER = 'catmarket-installed';

    protected $signature = 'catmarket:install {--force : Run the full seed again}';

    protected $description = 'Seed the cat marketplace (categories, fields, UK locations, prices, pages, guides, directory).';

    public function handle(): int
    {
        $disk = Storage::disk('local');

        if ($disk->exists(self::MARKER) && ! $this->option('force')) {
            $this->call('db:seed', ['--class' => AdminUserSeeder::class, '--force' => true]);
            $this->call('db:seed', ['--class' => \Modules\CatMarket\Database\Seeders\CatPageSeeder::class, '--force' => true]);
            $this->call('db:seed', ['--class' => \Modules\CatMarket\Database\Seeders\ExampleListingSeeder::class, '--force' => true]);
            $this->call('db:seed', ['--class' => \Modules\Community\Database\Seeders\CommunitySeeder::class, '--force' => true]);
            $this->info('Already installed. Admin user checked.');

            return self::SUCCESS;
        }

        $this->call('db:seed', ['--class' => CatMarketSeeder::class, '--force' => true]);
        $disk->put(self::MARKER, now()->toIso8601String());
        $this->info('Cat marketplace installed.');

        return self::SUCCESS;
    }
}
