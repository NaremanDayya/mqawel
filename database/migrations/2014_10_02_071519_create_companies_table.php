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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('picture')->nullable();
            $table->string('name')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('business_number')->unique()->nullable();
            $table->string('tax_number')->unique()->nullable();
            $table->string('activity')->nullable();
            $table->integer('about_to_expire_days')->default(7);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreignId('master_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
