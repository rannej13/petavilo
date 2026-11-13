<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('species', 20);
            $table->string('breed', 80)->nullable();
            $table->string('sex', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('colour', 120)->nullable();
            $table->string('photo_path', 200)->nullable();
            $table->string('neutered', 20)->nullable();
            $table->text('microchip_number')->nullable();
            $table->string('microchip_database', 80)->nullable();
            $table->text('medical_notes')->nullable();
            $table->text('allergies')->nullable();
            $table->text('medications')->nullable();
            $table->string('diet', 200)->nullable();
            $table->string('vet_name', 120)->nullable();
            $table->string('vet_phone', 40)->nullable();
            $table->string('insurer', 120)->nullable();
            $table->text('policy_number')->nullable();
            $table->string('policy_type', 40)->nullable();
            $table->date('policy_renewal')->nullable();
            $table->decimal('policy_excess', 8, 2)->nullable();
            $table->decimal('policy_limit', 10, 2)->nullable();
            $table->string('insurer_claims_phone', 40)->nullable();
            $table->string('owner_phone', 40)->nullable();
            $table->string('emergency_contact', 120)->nullable();
            $table->string('emergency_phone', 40)->nullable();
            $table->string('pass_token', 48)->nullable()->unique();
            $table->boolean('pass_enabled')->default(false);
            $table->json('pass_fields')->nullable();
            $table->boolean('is_missing')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pet_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pet_id')->constrained('pets')->cascadeOnDelete();
            $table->string('type', 30)->index();
            $table->string('title', 120);
            $table->date('happened_on')->nullable();
            $table->date('due_on')->nullable()->index();
            $table->decimal('value', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('reminded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_records');
        Schema::dropIfExists('pets');
    }
};
