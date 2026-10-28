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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('ingredient_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal(
                'quantity',
                12,
                3
            );

            $table->decimal(
                'remaining_quantity',
                12,
                3
            );

            $table->decimal(
                'cost_per_unit',
                12,
                2
            );

            $table->string(
                'source'
            )->nullable();

            $table->timestampTz(
                'received_at'
            );

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
