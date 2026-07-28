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
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->id();
            $table->string('picture')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('period');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency')->nullable();
            $table->boolean('has_workers')->default(false);
            $table->boolean('has_projects')->default(false);
            $table->boolean('has_storages')->default(false);
            $table->boolean('has_items')->default(false);
            $table->boolean('has_item_categories')->default(false);
            $table->boolean('has_item_movements')->default(false);
            $table->boolean('has_workers_report')->default(false);
            $table->boolean('has_worker_expenses_report')->default(false);
            $table->boolean('has_expired_files_report')->default(false);
            $table->boolean('has_project_expenses_report')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreignId('master_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_packages');
    }
};
