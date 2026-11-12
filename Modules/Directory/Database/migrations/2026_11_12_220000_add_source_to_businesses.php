<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->string('source', 40)->nullable()->after('licence_number');
            $table->string('source_id', 64)->nullable()->after('source');
        });

        DB::statement('CREATE UNIQUE INDEX businesses_source_id_unique ON businesses (source_id) WHERE source_id IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS businesses_source_id_unique');

        Schema::table('businesses', function (Blueprint $table): void {
            $table->dropColumn(['source', 'source_id']);
        });
    }
};
