<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rescue_donations', function (Blueprint $table): void {
            $table->decimal('tip', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('rescue_donations', function (Blueprint $table): void {
            $table->dropColumn('tip');
        });
    }
};
