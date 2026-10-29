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
        // 1. Composite indexes for Orders analytics and dashboard
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['outlet_id', 'status', 'created_at'], 'orders_outlet_status_created_idx');
        });

        // 2. Composite indexes for Order Batches (Kitchen Display Queue)
        Schema::table('order_batches', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'order_batches_status_created_idx');
        });

        // 3. Composite indexes for FIFO Inventory Batch selection
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->index(['ingredient_id', 'remaining_quantity', 'received_at'], 'inv_batches_fifo_lookup_idx');
        });

        // 4. Composite indexes for Stock Movements reporting
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['outlet_id', 'type', 'created_at'], 'stock_movements_outlet_type_created_idx');
            $table->index(['ingredient_id', 'created_at'], 'stock_movements_ingredient_created_idx');
        });

        // 5. Composite indexes for Audit Logs filtering
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['outlet_id', 'action', 'created_at'], 'audit_logs_outlet_action_created_idx');
            $table->index(['user_id', 'created_at'], 'audit_logs_user_created_idx');
        });

        // 6. Composite indexes for Menu Variants lookup
        Schema::table('menu_variants', function (Blueprint $table) {
            $table->index(['menu_id', 'status'], 'menu_variants_menu_status_idx');
            $table->index(['outlet_id', 'status'], 'menu_variants_outlet_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_outlet_status_created_idx');
        });

        Schema::table('order_batches', function (Blueprint $table) {
            $table->dropIndex('order_batches_status_created_idx');
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropIndex('inv_batches_fifo_lookup_idx');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_outlet_type_created_idx');
            $table->dropIndex('stock_movements_ingredient_created_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_outlet_action_created_idx');
            $table->dropIndex('audit_logs_user_created_idx');
        });

        Schema::table('menu_variants', function (Blueprint $table) {
            $table->dropIndex('menu_variants_menu_status_idx');
            $table->dropIndex('menu_variants_outlet_status_idx');
        });
    }
};
