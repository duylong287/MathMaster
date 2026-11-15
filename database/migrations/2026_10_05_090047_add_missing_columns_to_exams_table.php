<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Thêm cột exam_type nếu chưa có
            if (!Schema::hasColumn('exams', 'exam_type')) {
                $table->string('exam_type')->after('grade')->nullable();
            }
            
            // Tiện thể thêm luôn cột pdf_path để lát nữa làm Bước 2.2 không bị lỗi
            if (!Schema::hasColumn('exams', 'pdf_path')) {
                $table->string('pdf_path')->after('status')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['exam_type', 'pdf_path']);
        });
    }
};