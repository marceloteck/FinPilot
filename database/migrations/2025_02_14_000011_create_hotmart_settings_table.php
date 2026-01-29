<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotmart_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->string('product_id')->nullable();
            $table->string('basic_key')->nullable();
            $table->string('basic_secret')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->string('webhook_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotmart_settings');
    }
};
