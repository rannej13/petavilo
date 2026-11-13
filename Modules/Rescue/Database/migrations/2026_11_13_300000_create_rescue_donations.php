<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rescue_organisations', function (Blueprint $table): void {
            $table->string('stripe_account_id', 64)->nullable();
            $table->boolean('payouts_ready')->default(false);
            $table->boolean('donations_enabled')->default(true);
        });

        Schema::create('rescue_donations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organisation_id')->constrained('rescue_organisations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('fee_cover', 10, 2)->default(0);
            $table->string('currency', 3);
            $table->string('donor_name', 80)->nullable();
            $table->string('donor_email', 160)->nullable();
            $table->string('message', 300)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('status', 20)->default('pending')->index();
            $table->string('stripe_session_id', 120)->nullable()->unique();
            $table->string('stripe_account_id', 64)->nullable();
            $table->string('payment_intent', 120)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescue_donations');

        Schema::table('rescue_organisations', function (Blueprint $table): void {
            $table->dropColumn(['stripe_account_id', 'payouts_ready', 'donations_enabled']);
        });
    }
};
