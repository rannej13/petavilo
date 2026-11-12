<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_plans', function (Blueprint $table): void {
            $table->decimal('price_usd', 10, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('promotion_plans', function (Blueprint $table): void {
            $table->dropColumn('price_usd');
        });
    }
};
