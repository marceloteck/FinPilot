<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('debt_type_id')->constrained('debt_types')->cascadeOnDelete();
            $table->string('name');
            $table->string('creditor')->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->decimal('monthly_minimum', 12, 2);
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->unsignedInteger('due_day');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('due_day');
            $table->index('status');
            $table->index('debt_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
