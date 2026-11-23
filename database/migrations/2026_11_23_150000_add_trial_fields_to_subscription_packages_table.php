<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A free trial package: no price, and a length in days instead of months.
     */
    public function up(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_packages', 'is_trial')) {
                $table->boolean('is_trial')->default(false)->after('is_active');
            }

            if (! Schema::hasColumn('subscription_packages', 'trial_days')) {
                $table->unsignedSmallInteger('trial_days')->nullable()->after('is_trial');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_packages', 'trial_days')) {
                $table->dropColumn('trial_days');
            }

            if (Schema::hasColumn('subscription_packages', 'is_trial')) {
                $table->dropColumn('is_trial');
            }
        });
    }
};
