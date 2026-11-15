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
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->enum('part', ['I', 'II', 'III']);
            $table->unsignedTinyInteger('number');
            $table->foreignId('topic_id')->nullable()->constrained('topics');
            $table->enum('difficulty', ['recognize', 'understand', 'apply', 'advanced'])->nullable();
            $table->string('correct_answer')->nullable();
            $table->unsignedTinyInteger('decimal_places')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'part', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
