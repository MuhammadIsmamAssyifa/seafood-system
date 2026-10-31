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
        Schema::table('recipes', function (Blueprint $table) {
            if (! Schema::hasColumn('recipes', 'name')) {
                $table->string('name')->nullable()->after('menu_variant_id');
            }
            if (! Schema::hasColumn('recipes', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('recipes', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('description')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            if (Schema::hasColumn('recipes', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('recipes', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('recipes', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};

