<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Thêm cột thời gian làm bài (duration)
            if (!Schema::hasColumn('exams', 'duration')) {
                $table->integer('duration')->after('exam_type')->default(90);
            }
            
            // Dự phòng thêm cột trạng thái (status) nếu chưa có
            if (!Schema::hasColumn('exams', 'status')) {
                $table->string('status')->after('duration')->default('draft');
            }

            // Dự phòng thêm cột ID người tạo (created_by) nếu chưa có
            if (!Schema::hasColumn('exams', 'created_by')) {
                $table->unsignedBigInteger('created_by')->after('status')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Chỉ xóa cột duration khi rollback
            if (Schema::hasColumn('exams', 'duration')) {
                $table->dropColumn('duration');
            }
        });
    }
};