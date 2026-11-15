<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('grade'); // Khối lớp
            $table->unsignedBigInteger('teacher_id')->nullable(); // Giáo viên phụ trách (cho phép trống)
            $table->enum('status', ['active', 'closed'])->default('active'); // Trạng thái: hoạt động (active) hoặc đã đóng (closed)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
