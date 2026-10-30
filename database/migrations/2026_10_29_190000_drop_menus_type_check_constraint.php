<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE menus DROP CONSTRAINT IF EXISTS menus_type_check');
    }

    public function down(): void
    {
        //
    }
};

