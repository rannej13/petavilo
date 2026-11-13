<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vet_leads', function (Blueprint $table): void {
            $table->id();
            $table->string('kind', 30)->index();
            $table->string('country', 2)->index();
            $table->string('town', 80);
            $table->string('name', 80);
            $table->string('email', 160);
            $table->string('phone', 40)->nullable();
            $table->string('pet_name', 60)->nullable();
            $table->json('answers')->nullable();
            $table->string('summary', 300)->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vet_leads');
    }
};
