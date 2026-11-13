<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table): void {
            $table->text('feeding_instructions')->nullable();
            $table->text('medication_instructions')->nullable();
            $table->string('medication_method', 40)->nullable();
            $table->string('hiding_spots', 300)->nullable();
            $table->text('handling_tips')->nullable();
            $table->text('routine_notes')->nullable();
            $table->string('vet_account_number', 60)->nullable();
            $table->decimal('vet_spending_cap', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table): void {
            $table->dropColumn(['feeding_instructions', 'medication_instructions', 'medication_method', 'hiding_spots', 'handling_tips', 'routine_notes', 'vet_account_number', 'vet_spending_cap']);
        });
    }
};
