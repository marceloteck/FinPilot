<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_plan_id')->constrained('ai_plans')->cascadeOnDelete();
            $table->foreignId('debt_id')->nullable()->constrained('debts')->nullOnDelete();
            $table->string('bucket');
            $table->decimal('suggested_amount', 12, 2);
            $table->decimal('confirmed_amount', 12, 2)->nullable();
            $table->json('reason_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_plan_items');
    }
};
