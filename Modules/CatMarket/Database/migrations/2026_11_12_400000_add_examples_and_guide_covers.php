<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('listings', 'is_example')) {
            Schema::table('listings', function (Blueprint $table): void {
                $table->boolean('is_example')->default(false)->index();
            });
        }

        if (Schema::hasTable('guides') && ! Schema::hasColumn('guides', 'cover_image')) {
            Schema::table('guides', function (Blueprint $table): void {
                $table->string('cover_image')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('listings', 'is_example')) {
            Schema::table('listings', function (Blueprint $table): void {
                $table->dropColumn('is_example');
            });
        }

        if (Schema::hasTable('guides') && Schema::hasColumn('guides', 'cover_image')) {
            Schema::table('guides', function (Blueprint $table): void {
                $table->dropColumn('cover_image');
            });
        }
    }
};
