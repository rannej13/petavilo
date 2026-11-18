<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tv_videos')) {
            Schema::create('tv_videos', function (Blueprint $table): void {
                $table->id();
                $table->string('title', 140);
                $table->string('platform', 20);
                $table->string('video_id', 64);
                $table->string('source_url', 300);
                $table->string('category', 30)->index();
                $table->string('species', 20)->nullable();
                $table->string('credit', 120)->nullable();
                $table->boolean('own_or_licensed')->default(false);
                $table->string('next_label', 120)->nullable();
                $table->string('next_url', 300)->nullable();
                $table->string('next_label_2', 120)->nullable();
                $table->string('next_url_2', 300)->nullable();
                $table->boolean('is_published')->default(true)->index();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('plays')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pet_of_week_entries')) {
            Schema::create('pet_of_week_entries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('week_start')->index();
                $table->string('pet_name', 60);
                $table->string('species', 20);
                $table->string('caption', 200)->nullable();
                $table->string('photo_path', 200);
                $table->string('status', 20)->default('pending')->index();
                $table->boolean('consent_feature')->default(false);
                $table->unsignedInteger('votes_count')->default(0);
                $table->timestamp('decided_at')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'week_start']);
            });
        }

        if (! Schema::hasTable('pet_of_week_votes')) {
            Schema::create('pet_of_week_votes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('entry_id')->constrained('pet_of_week_entries')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('week_start');
                $table->timestamps();
                $table->unique(['user_id', 'week_start']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_of_week_votes');
        Schema::dropIfExists('pet_of_week_entries');
        Schema::dropIfExists('tv_videos');
    }
};
