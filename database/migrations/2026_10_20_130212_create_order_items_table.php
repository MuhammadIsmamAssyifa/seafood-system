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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_variant_id')->constrained()->restrictOnDelete();
            $table->string('menu_name_snapshot');
            $table->string('variant_name_snapshot')->nullable();
            $table->decimal('requested_quantity', 10, 3)->nullable();
            $table->decimal('confirmed_quantity', 10, 3)->nullable();
            $table->decimal('price_snapshot', 12, 2);
            $table->string('sales_unit', 20);
            $table->integer('quantity');
            $table->integer('inventory_quantity')->default(0);
            $table->string('inventory_unit', 20)->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->string('status', 30)->default('waiting');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
