<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rescue_organisations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('country', 2)->default('GB')->index();
            $table->string('town', 80)->nullable();
            $table->json('species')->nullable();
            $table->string('charity_number', 40)->nullable();
            $table->string('website', 200)->nullable();
            $table->string('contact_email', 160)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('evidence_note')->nullable();
            $table->boolean('accepts_fosters')->default(false);
            $table->boolean('takes_rehoming_requests')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('adoption_applications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('listing_id')->index();
            $table->foreignId('organisation_id')->constrained('rescue_organisations')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('submitted')->index();
            $table->json('answers')->nullable();
            $table->text('message')->nullable();
            $table->json('history')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('welfare_responses', function (Blueprint $table): void {
            $table->id();
            $table->string('campaign', 60);
            $table->string('answer', 60);
            $table->string('country', 2)->nullable();
            $table->string('fingerprint', 64);
            $table->timestamps();
            $table->unique(['campaign', 'fingerprint']);
            $table->index(['campaign', 'answer']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('welfare_responses');
        Schema::dropIfExists('adoption_applications');
        Schema::dropIfExists('rescue_organisations');
    }
};
