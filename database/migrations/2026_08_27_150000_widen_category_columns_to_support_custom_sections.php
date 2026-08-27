<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * generated_documents.category and document_templates.category were a hard
 * enum('contracts','quotes','letters','correspondence','minutes') — but
 * document sections are now company-managed and can be any custom key
 * (see GeneratedDocumentResource::companyCategories()), so a value outside
 * that fixed list would be rejected by the database. Widen both to a plain
 * string column (raw SQL, since this project doesn't have doctrine/dbal
 * installed for Schema::table()->change()).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE generated_documents MODIFY category VARCHAR(255) NOT NULL DEFAULT 'contracts'");
        DB::statement("ALTER TABLE document_templates MODIFY category VARCHAR(255) NOT NULL DEFAULT 'contracts'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE generated_documents MODIFY category ENUM('contracts','quotes','letters','correspondence','minutes') NOT NULL DEFAULT 'contracts'");
        DB::statement("ALTER TABLE document_templates MODIFY category ENUM('contracts','quotes','letters','correspondence','minutes') NOT NULL DEFAULT 'contracts'");
    }
};
