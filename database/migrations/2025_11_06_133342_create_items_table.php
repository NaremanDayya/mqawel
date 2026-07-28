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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('storage_id')->nullable()->constrained('storages')->cascadeOnDelete();
            $table->integer('category_id')->nullable();
            $table->string('picture')->nullable();
            $table->string('name')->default('');
            $table->longText('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->enum('status', ['new', 'used', 'damaged'])->default('new');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreignId('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
