<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Allow menus type to accept string values (food, drink, snack, seafood) in PostgreSQL
        DB::statement('ALTER TABLE menus ALTER COLUMN type TYPE VARCHAR(30) USING type::VARCHAR(30)');

        // 2. Fix order_items decimal precision
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->change();
            $table->decimal('inventory_quantity', 12, 3)->default(0)->change();
            $table->index(['order_batch_id', 'status']);
        });

        // 3. Fix order_item_changes nullable columns
        Schema::table('order_item_changes', function (Blueprint $table) {
            $table->decimal('new_quantity', 10, 3)->nullable()->change();
            $table->decimal('new_price', 12, 2)->nullable()->change();
            $table->index(['order_item_id', 'status']);
        });

        // 4. Add from_status and allow nullable changed_by to order_status_histories
        DB::statement('ALTER TABLE order_status_histories ALTER COLUMN changed_by DROP NOT NULL');

        Schema::table('order_status_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('order_status_histories', 'from_status')) {
                $table->string('from_status', 50)->nullable()->after('order_id');
            }
            $table->index(['order_id', 'status']);
        });

        // 5. Add performance indexes for POS & KDS queries
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['outlet_id', 'status']);
            $table->index('created_at');
        });

        Schema::table('order_batches', function (Blueprint $table) {
            $table->index(['order_id', 'status']);
            $table->index('sent_to_kitchen_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['order_id', 'status']);
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->index(['ingredient_id', 'remaining_quantity']);
            $table->index('received_at');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['outlet_id', 'ingredient_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['outlet_id', 'ingredient_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropIndex(['ingredient_id', 'remaining_quantity']);
            $table->dropIndex(['received_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'status']);
        });

        Schema::table('order_batches', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'status']);
            $table->dropIndex(['sent_to_kitchen_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['outlet_id', 'status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'status']);
            $table->dropColumn('from_status');
        });

        Schema::table('order_item_changes', function (Blueprint $table) {
            $table->dropIndex(['order_item_id', 'status']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_batch_id', 'status']);
        });
    }
};
