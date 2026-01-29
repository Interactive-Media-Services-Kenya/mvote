<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('performance_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('voting_questions')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('rating')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'performance_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
