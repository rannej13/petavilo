<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('triage_clinics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('email', 160);
            $table->string('phone', 40)->nullable();
            $table->string('booking_url', 200)->nullable();
            $table->string('emergency_text', 300)->nullable();
            $table->string('country', 2)->default('GB');
            $table->string('token', 40)->unique();
            $table->boolean('is_suspended')->default(false);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('triage_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clinic_id')->constrained('triage_clinics')->cascadeOnDelete();
            $table->string('tier', 10)->index();
            $table->string('owner_name', 80);
            $table->string('owner_email', 160)->nullable();
            $table->string('owner_phone', 40)->nullable();
            $table->string('pet_name', 60)->nullable();
            $table->json('answers');
            $table->text('soap');
            $table->timestamp('emailed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triage_submissions');
        Schema::dropIfExists('triage_clinics');
    }
};
