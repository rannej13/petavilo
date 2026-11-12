<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_boards', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->string('description', 240)->nullable();
            $table->string('image', 40)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('community_threads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('board_id')->constrained('community_boards')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 160);
            $table->string('slug', 170);
            $table->text('body');
            $table->string('country', 2)->nullable()->index();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_hidden')->default(false)->index();
            $table->unsignedInteger('reply_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('report_count')->default(0);
            $table->timestamp('last_post_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('community_replies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('thread_id')->constrained('community_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->boolean('is_hidden')->default(false)->index();
            $table->unsignedInteger('report_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_replies');
        Schema::dropIfExists('community_threads');
        Schema::dropIfExists('community_boards');
    }
};
