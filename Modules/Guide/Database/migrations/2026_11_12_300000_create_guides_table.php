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
        Schema::create('guides', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('type', 24)->index();
            $table->string('excerpt', 320)->nullable();
            $table->longText('body')->nullable();
            $table->boolean('has_affiliate_links')->default(false);
            $table->string('sponsor_name')->nullable();
            $table->string('author_name')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->date('reviewed_on')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'published_at']);
        });

        DB::statement('CREATE UNIQUE INDEX guides_slug_unique ON guides (slug) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('guides');
    }
};
