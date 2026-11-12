<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            $table->boolean('is_urgent')->default(false)->after('is_featured');
            $table->timestamp('bumped_at')->nullable()->after('is_urgent');
            $table->index(['status', 'is_featured', 'bumped_at']);
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            $table->dropIndex(['status', 'is_featured', 'bumped_at']);
            $table->dropColumn(['is_urgent', 'bumped_at']);
        });
    }
};
