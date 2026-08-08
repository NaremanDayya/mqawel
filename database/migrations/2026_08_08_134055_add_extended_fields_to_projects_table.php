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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('number')->nullable()->after('name');
            $table->string('location')->nullable()->after('address');
            $table->string('building_system')->nullable()->after('location');
            $table->string('phase')->nullable()->after('building_system');
            $table->string('owner_name')->nullable()->after('phase');
            $table->string('owner_phone')->nullable()->after('owner_name');
            $table->json('photos')->nullable()->after('owner_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['number', 'location', 'building_system', 'phase', 'owner_name', 'owner_phone', 'photos']);
        });
    }
};
