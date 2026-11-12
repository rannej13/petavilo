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
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('social_url', 200)->nullable();
            $table->json('services')->nullable();
            $table->string('price_from', 60)->nullable();
            $table->string('accreditation', 24)->nullable();
            $table->boolean('accreditation_verified')->default(false);
            $table->boolean('emergency_care')->default(false);
            $table->boolean('home_visits')->default(false);
            $table->boolean('cat_only')->default(false);
            $table->string('new_client_offer', 160)->nullable();
            $table->string('booking_url', 200)->nullable();
            $table->string('logo_path')->nullable();
            $table->json('photos')->nullable();
            $table->unsignedInteger('website_clicks')->default(0);
            $table->string('dedupe_key', 160)->nullable()->index();
            $table->string('invite_token', 48)->nullable()->unique();
            $table->timestamp('invited_at')->nullable()->index();
            $table->unsignedTinyInteger('invite_count')->default(0);
            $table->timestamp('unsubscribed_at')->nullable();
        });

        Schema::create('directory_suppressions', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('reason', 40)->default('unsubscribed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('directory_suppressions');

        Schema::table('businesses', function (Blueprint $table): void {
            $table->dropColumn([
                'latitude', 'longitude', 'social_url', 'services', 'price_from', 'accreditation', 'accreditation_verified',
                'emergency_care', 'home_visits', 'cat_only', 'new_client_offer', 'booking_url', 'logo_path', 'photos',
                'website_clicks', 'dedupe_key', 'invite_token', 'invited_at', 'invite_count', 'unsubscribed_at',
            ]);
        });
    }
};
