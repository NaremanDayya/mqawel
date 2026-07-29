<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Uses raw SQL rather than Blueprint::change() to avoid pulling in
     * doctrine/dbal just for this one nullability change.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE files MODIFY expiry_date DATE NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE files MODIFY expiry_date DATE NOT NULL');
    }
};
