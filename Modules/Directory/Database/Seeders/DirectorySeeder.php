<?php

declare(strict_types=1);

namespace Modules\Directory\Database\Seeders;

use Illuminate\Database\Seeder;

class DirectorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Directory ready. Add businesses in the admin panel or let owners add their own at /directory/add.');
    }
}
