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
        Schema::table('companies', function (Blueprint $table) {
            $table->text('description')->nullable()->after('activity');
            $table->unsignedSmallInteger('founded_year')->nullable()->after('description');
            $table->string('address')->nullable()->after('founded_year');
            $table->json('services')->nullable()->after('address');
            $table->boolean('is_verified')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['description', 'founded_year', 'address', 'services', 'is_verified']);
        });
    }
};
