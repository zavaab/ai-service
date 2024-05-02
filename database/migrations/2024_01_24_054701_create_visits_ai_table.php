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
        Schema::create('visits_ai', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('visit_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('ai_id');
            $table->string('name');
            $table->string('url');
            $table->string('status')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits_ai');
    }
};
