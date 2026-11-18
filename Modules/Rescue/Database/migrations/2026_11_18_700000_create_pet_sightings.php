<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pet_sightings')) {
            Schema::create('pet_sightings', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('listing_id')->index();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reporter_name', 80)->nullable();
                $table->string('reporter_contact', 120)->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('place', 160);
                $table->timestamp('seen_at');
                $table->string('note', 500)->nullable();
                $table->boolean('is_hidden')->default(false);
                $table->string('ip_hash', 64)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_sightings');
    }
};
