<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * English versions of the texts the admin types on a package, shown when the website is read in
     * English. The cost items and feature bullets keep theirs inside their own JSON (label_en / text_en).
     */
    public function up(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            foreach (['title_en' => 'string', 'description_en' => 'text', 'savings_note_en' => 'string', 'cta_label_en' => 'string'] as $column => $type) {
                if (! Schema::hasColumn('subscription_packages', $column)) {
                    $table->{$type}($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            foreach (['title_en', 'description_en', 'savings_note_en', 'cta_label_en'] as $column) {
                if (Schema::hasColumn('subscription_packages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
