<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animal_listing_reviews', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->json('reasons')->nullable();
            $table->string('decision', 16)->default('pending')->index();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('CREATE UNIQUE INDEX animal_listing_reviews_listing_unique ON animal_listing_reviews (listing_id) WHERE deleted_at IS NULL');

        Schema::create('seller_verifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('kind', 32);
            $table->string('reference')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->date('expires_on')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_verifications');
        Schema::dropIfExists('animal_listing_reviews');
    }
};
