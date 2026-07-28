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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->enum('category', ['contracts', 'quotes', 'letters', 'correspondence', 'minutes'])->default('contracts');
            $table->timestamp('last_used_at')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('document_templates')->insert([
            ['name' => 'عقد مقاول باطن', 'category' => 'contracts', 'last_used_at' => $now->copy()->subDays(3), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'عقد مقاول رئيسي', 'category' => 'contracts', 'last_used_at' => $now->copy()->subWeek(), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'عقد توريد مواد', 'category' => 'contracts', 'last_used_at' => $now->copy()->subWeeks(2), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'عرض سعر', 'category' => 'quotes', 'last_used_at' => $now->copy()->subDay(), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'طلب شراء', 'category' => 'quotes', 'last_used_at' => $now->copy()->subWeek(), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'خطاب مطالبة مالية', 'category' => 'letters', 'last_used_at' => $now->copy()->subMonth(), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'خطاب ترسية', 'category' => 'letters', 'last_used_at' => $now->copy()->subWeek(), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'خطاب تسليم واستلام', 'category' => 'letters', 'last_used_at' => $now->copy()->subWeeks(2), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'محضر استلام', 'category' => 'minutes', 'last_used_at' => $now->copy()->subDays(3), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'محضر اجتماع', 'category' => 'minutes', 'last_used_at' => $now->copy()->subMonth(), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'مراسلة عميل / مورد', 'category' => 'correspondence', 'last_used_at' => $now->copy()->subDay(), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'خطاب رسمي', 'category' => 'letters', 'last_used_at' => $now->copy()->subMonth(), 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
