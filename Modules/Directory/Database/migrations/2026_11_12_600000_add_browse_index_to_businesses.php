<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS businesses_browse_index ON businesses (country, type, tier_rank DESC, is_verified DESC, name) WHERE is_published = true AND deleted_at IS NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS businesses_country_town_index ON businesses (country, type, town)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS businesses_browse_index');
        DB::statement('DROP INDEX IF EXISTS businesses_country_town_index');
    }
};
