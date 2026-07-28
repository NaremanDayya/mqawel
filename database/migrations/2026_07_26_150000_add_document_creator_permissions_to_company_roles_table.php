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
        Schema::table('company_roles', function (Blueprint $table) {
            $table->boolean('can_read_document_creator')->default(false);
            $table->boolean('can_write_document_creator')->default(false);
            $table->boolean('can_edit_document_creator')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_roles', function (Blueprint $table) {
            $table->dropColumn([
                'can_read_document_creator',
                'can_write_document_creator',
                'can_edit_document_creator',
            ]);
        });
    }
};
