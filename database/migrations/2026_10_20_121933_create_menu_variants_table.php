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
        Schema::create('menu_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cooking_method_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('main_ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->foreignId('kitchen_station_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence')->default(1);
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->string('sales_unit');
            $table->string('inventory_unit')->nullable();
            $table->decimal('conversion_rate', 10, 3)->default(1);
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->index('menu_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_variants');
    }
};
