<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity');
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->json('config_json');
            $table->timestamps();

            $table->index('entity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('views');
    }
};
