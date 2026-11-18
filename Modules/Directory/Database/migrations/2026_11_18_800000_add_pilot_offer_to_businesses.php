<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('businesses', 'pilot_offer_months')) {
            Schema::table('businesses', function (Blueprint $table): void {
                $table->unsignedTinyInteger('pilot_offer_months')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->dropColumn('pilot_offer_months');
        });
    }
};
