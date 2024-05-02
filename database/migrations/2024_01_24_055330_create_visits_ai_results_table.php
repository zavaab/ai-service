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
        Schema::create('visits_ai_results', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('visits_ai_id')->unsigned()->index()->nullable();
            $table->foreign('visits_ai_id')->references('id')->on('visits_ai')->onDelete('cascade');
            // $table->unsignedBigInteger('ai_id');
            $table->string('code')->nullable();
            $table->string('count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits_ai_results');
    }
};
