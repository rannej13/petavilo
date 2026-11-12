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
        Schema::table('promotion_orders', function (Blueprint $table): void {
            $table->string('payment_reference')->nullable()->after('currency');
        });

        DB::statement('CREATE UNIQUE INDEX promotion_orders_payment_reference_unique ON promotion_orders (payment_reference) WHERE payment_reference IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS promotion_orders_payment_reference_unique');

        Schema::table('promotion_orders', function (Blueprint $table): void {
            $table->dropColumn('payment_reference');
        });
    }
};
