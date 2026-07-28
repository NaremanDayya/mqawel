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
        Schema::create('company_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('can_read_users')->default(false);
            $table->boolean('can_write_users')->default(false);
            $table->boolean('can_edit_users')->default(false);
            $table->boolean('can_read_workers')->default(false);
            $table->boolean('can_write_workers')->default(false);
            $table->boolean('can_edit_workers')->default(false);
            $table->boolean('can_read_projects')->default(false);
            $table->boolean('can_write_projects')->default(false);
            $table->boolean('can_edit_projects')->default(false);
            $table->boolean('can_read_storages')->default(false);
            $table->boolean('can_write_storages')->default(false);
            $table->boolean('can_edit_storages')->default(false);
            $table->boolean('can_read_items')->default(false);
            $table->boolean('can_write_items')->default(false);
            $table->boolean('can_edit_items')->default(false);
            $table->boolean('can_read_item_categories')->default(false);
            $table->boolean('can_write_item_categories')->default(false);
            $table->boolean('can_edit_item_categories')->default(false);
            $table->boolean('can_read_item_movements')->default(false);
            $table->boolean('can_write_item_movements')->default(false);
            $table->boolean('can_edit_item_movements')->default(false);
            /*$table->boolean('can_read_contractors')->default(false);
            $table->boolean('can_write_contractors')->default(false);
            $table->boolean('can_edit_contractors')->default(false);*/
            $table->boolean('can_read_workers_report')->default(false);
            $table->boolean('can_write_workers_report')->default(false);
            $table->boolean('can_edit_workers_report')->default(false);
            $table->boolean('can_read_company_files')->default(false);
            $table->boolean('can_write_company_files')->default(false);
            $table->boolean('can_edit_company_files')->default(false);
            $table->boolean('can_read_worker_expenses_report')->default(false);
            $table->boolean('can_write_worker_expenses_report')->default(false);
            $table->boolean('can_edit_worker_expenses_report')->default(false);
            $table->boolean('can_read_expired_files_report')->default(false);
            $table->boolean('can_write_expired_files_report')->default(false);
            $table->boolean('can_edit_expired_files_report')->default(false);
            $table->boolean('can_read_project_expenses_report')->default(false);
            $table->boolean('can_write_project_expenses_report')->default(false);
            $table->boolean('can_edit_project_expenses_report')->default(false);
            $table->boolean('can_read_roles')->default(false);
            $table->boolean('can_write_roles')->default(false);
            $table->boolean('can_edit_roles')->default(false);
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_roles');
    }
};
