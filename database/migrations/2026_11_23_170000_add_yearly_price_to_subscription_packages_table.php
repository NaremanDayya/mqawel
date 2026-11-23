<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What a 12-month subscription costs, set by the admin per package. Empty means "the default":
     * 10 months' price (two months free), as the pricing page promises.
     */
    public function up(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_packages', 'yearly_price')) {
                $table->decimal('yearly_price', 10, 2)->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_packages', 'yearly_price')) {
                $table->dropColumn('yearly_price');
            }
        });
    }
};
