<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'email_notifications')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('email_notifications')->default(true);
            });
        }

        Schema::table('user_notifications', function (Blueprint $table): void {
            $table->timestamp('emailed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_notifications', function (Blueprint $table): void {
            $table->dropColumn('emailed_at');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('email_notifications');
        });
    }
};
