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
        Schema::create('item_damages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('storage_id')->nullable();
            $table->foreignId('item_id')->nullable();
            $table->foreignId('responsible_id')->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->date('damage_date')->nullable();
            $table->text('notes');
            $table->timestamps();
            $table->foreignId('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_damages');
    }
};
