<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('cost_per_unit', 12, 2)->default(0)->after('quantity');
            $table->decimal('total_cost', 12, 2)->default(0)->after('cost_per_unit');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('cogs', 12, 2)->default(0)->after('subtotal');
            $table->decimal('gross_profit', 12, 2)->default(0)->after('cogs');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_cogs', 12, 2)->default(0)->after('grand_total');
            $table->decimal('gross_profit', 12, 2)->default(0)->after('total_cogs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['total_cogs', 'gross_profit']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['cogs', 'gross_profit']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['cost_per_unit', 'total_cost']);
        });
    }
};

