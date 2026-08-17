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
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('is_transferred')->default(false)->after('status');
            $table->string('transferred_from')->nullable()->after('is_transferred');
            $table->date('transfer_or_purchase_date')->nullable()->after('transferred_from');
            $table->string('performed_by')->nullable()->after('transfer_or_purchase_date');
            $table->string('usage_purpose')->nullable()->after('performed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['is_transferred', 'transferred_from', 'transfer_or_purchase_date', 'performed_by', 'usage_purpose']);
        });
    }
};
