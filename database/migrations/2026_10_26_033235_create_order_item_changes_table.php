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
        Schema::create('order_item_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_quantity', 10, 3);
            $table->decimal('old_price', 12, 2);
            $table->decimal('new_quantity', 10, 3);
            $table->decimal('new_price', 12, 2);
            $table->foreignId('replacement_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->string('reason');
            $table->string('status', 30)->default('pending_customer');
            $table->string('type', 30);
            $table->foreignId('created_by')->constrained('users');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_changes');
    }
};
