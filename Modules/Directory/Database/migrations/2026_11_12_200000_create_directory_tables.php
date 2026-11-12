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
        Schema::create('businesses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('type', 40)->index();
            $table->text('description')->nullable();
            $table->string('address_line')->nullable();
            $table->string('town')->nullable()->index();
            $table->string('postcode', 12)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('opening_hours')->nullable();
            $table->string('licence_number')->nullable();
            $table->unsignedBigInteger('owner_user_id')->nullable()->index();
            $table->string('tier', 16)->default('basic');
            $table->unsignedTinyInteger('tier_rank')->default(1);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('claimed_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'type', 'tier_rank']);
        });

        DB::statement('CREATE UNIQUE INDEX businesses_slug_unique ON businesses (slug) WHERE deleted_at IS NULL');

        Schema::create('business_claims', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('role_at_business')->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 16)->default('pending')->index();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_claims');
        Schema::dropIfExists('businesses');
    }
};
