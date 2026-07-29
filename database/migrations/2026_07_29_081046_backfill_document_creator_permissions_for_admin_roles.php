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
        DB::table('company_roles')
            ->where('can_edit_users', true)
            ->update([
                'can_read_document_creator' => true,
                'can_write_document_creator' => true,
                'can_edit_document_creator' => true,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left as a no-op: reversing would strip permissions
        // that may have since been set deliberately via the Roles UI.
    }
};
