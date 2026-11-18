<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            if (! Schema::hasColumn('businesses', 'pilot_tier')) {
                $table->string('pilot_tier', 20)->nullable();
                $table->timestamp('pilot_started_at')->nullable();
                $table->timestamp('pilot_ends_at')->nullable()->index();
                $table->timestamp('pilot_reminded_at')->nullable();
                $table->unsignedInteger('phone_clicks')->default(0);
            }
        });

        if (! Schema::hasTable('business_stat_days')) {
            Schema::create('business_stat_days', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->date('day');
                $table->unsignedInteger('views')->default(0);
                $table->unsignedInteger('website')->default(0);
                $table->unsignedInteger('booking')->default(0);
                $table->unsignedInteger('phone')->default(0);
                $table->unique(['business_id', 'day']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('business_stat_days');
        Schema::table('businesses', function (Blueprint $table): void {
            $table->dropColumn(['pilot_tier', 'pilot_started_at', 'pilot_ends_at', 'pilot_reminded_at', 'phone_clicks']);
        });
    }
};
