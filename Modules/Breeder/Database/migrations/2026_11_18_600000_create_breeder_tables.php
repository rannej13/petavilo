<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('breeder_animals')) {
            Schema::create('breeder_animals', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('name', 80);
                $table->string('species', 20);
                $table->string('breed', 80)->nullable();
                $table->string('sex', 10);
                $table->date('date_of_birth')->nullable();
                $table->text('microchip_number')->nullable();
                $table->string('registration', 80)->nullable();
                $table->text('health_tests')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('breeder_litters')) {
            Schema::create('breeder_litters', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('name', 80);
                $table->string('species', 20);
                $table->string('breed', 80)->nullable();
                $table->foreignId('dam_id')->nullable()->constrained('breeder_animals')->nullOnDelete();
                $table->foreignId('sire_id')->nullable()->constrained('breeder_animals')->nullOnDelete();
                $table->string('sire_name', 120)->nullable();
                $table->date('mated_on')->nullable();
                $table->date('due_on')->nullable();
                $table->date('born_on')->nullable();
                $table->boolean('caesarean')->default(false);
                $table->unsignedSmallInteger('born_count')->nullable();
                $table->date('ready_on')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->string('status', 20)->default('planned')->index();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('listing_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('breeder_offspring')) {
            Schema::create('breeder_offspring', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('litter_id')->constrained('breeder_litters')->cascadeOnDelete();
                $table->string('name', 80);
                $table->string('sex', 10)->nullable();
                $table->string('colour', 120)->nullable();
                $table->text('microchip_number')->nullable();
                $table->string('status', 20)->default('available')->index();
                $table->string('buyer_name', 120)->nullable();
                $table->string('buyer_email', 160)->nullable();
                $table->string('buyer_town', 80)->nullable();
                $table->timestamp('reserved_at')->nullable();
                $table->timestamp('sold_at')->nullable();
                $table->string('handover_token', 40)->nullable()->unique();
                $table->timestamp('handover_claimed_at')->nullable();
                $table->text('care_notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('breeder_records')) {
            Schema::create('breeder_records', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('offspring_id')->nullable()->constrained('breeder_offspring')->cascadeOnDelete();
                $table->foreignId('animal_id')->nullable()->constrained('breeder_animals')->cascadeOnDelete();
                $table->string('kind', 20)->index();
                $table->date('recorded_on');
                $table->unsignedInteger('grams')->nullable();
                $table->string('title', 120)->nullable();
                $table->text('details')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('breeder_waitlist')) {
            Schema::create('breeder_waitlist', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('litter_id')->nullable()->constrained('breeder_litters')->nullOnDelete();
                $table->string('name', 120);
                $table->string('email', 160)->nullable();
                $table->string('phone', 40)->nullable();
                $table->string('town', 80)->nullable();
                $table->string('preference', 200)->nullable();
                $table->string('status', 20)->default('waiting')->index();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('breeder_waitlist');
        Schema::dropIfExists('breeder_records');
        Schema::dropIfExists('breeder_offspring');
        Schema::dropIfExists('breeder_litters');
        Schema::dropIfExists('breeder_animals');
    }
};
