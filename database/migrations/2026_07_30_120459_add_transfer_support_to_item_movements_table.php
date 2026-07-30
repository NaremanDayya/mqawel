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
        // Raw SQL rather than Blueprint::change() to avoid pulling in doctrine/dbal.
        DB::statement("ALTER TABLE item_movements MODIFY type ENUM('in', 'out', 'adjust', 'transfer') NULL");

        Schema::table('item_movements', function (Blueprint $table) {
            $table->foreignId('to_project_id')->nullable()->after('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_movements', function (Blueprint $table) {
            $table->dropColumn('to_project_id');
        });

        DB::statement("ALTER TABLE item_movements MODIFY type ENUM('in', 'out', 'adjust') NULL");
    }
};
