<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 0. An toàn: không xóa cột exam_type nếu còn đề chỉ có dữ liệu ở cột cũ
        $lost = DB::table('exams')->whereNull('type')->whereNotNull('exam_type')->count();
        if ($lost > 0) {
            throw new RuntimeException(
                "Có {$lost} đề thi có exam_type nhưng type để trống. "
                . 'Hãy chuyển dữ liệu sang cột type (đúng giá trị mà code đang dùng) rồi chạy lại.'
            );
        }

        // 1. Bỏ cột thừa
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['exam_type', 'duration']);
        });
        // Cột users.approval_status chưa xóa ở đây: code quản lý tài khoản còn dùng nó
        // để khóa/mở khóa. Xóa ở migration riêng sau khi đã chuyển sang is_active.

        // 2. Dọn dữ liệu mồ côi để khóa ngoại thêm vào không bị lỗi
        DB::table('classes')
            ->whereNotNull('teacher_id')
            ->whereNotIn('teacher_id', DB::table('users')->select('id'))
            ->update(['teacher_id' => null]);

        DB::table('users')
            ->whereNotNull('class_id')
            ->whereNotIn('class_id', DB::table('classes')->select('id'))
            ->update(['class_id' => null]);

        DB::table('class_exam')
            ->where(function ($q) {
                $q->whereNotIn('class_id', DB::table('classes')->select('id'))
                  ->orWhereNotIn('exam_id', DB::table('exams')->select('id'));
            })
            ->delete();

        // Xóa dòng giao đề trùng, giữ dòng có id nhỏ nhất
        DB::statement(
            'DELETE ce1 FROM class_exam ce1
             JOIN class_exam ce2
               ON ce1.class_id = ce2.class_id
              AND ce1.exam_id = ce2.exam_id
              AND ce1.id > ce2.id'
        );

        // 3. Thêm khóa ngoại và ràng buộc duy nhất
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
        });
        Schema::table('class_exam', function (Blueprint $table) {
            $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->unique(['class_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::table('class_exam', function (Blueprint $table) {
            $table->dropUnique(['class_id', 'exam_id']);
            $table->dropForeign(['class_id']);
            $table->dropForeign(['exam_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
        });
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });
        Schema::table('exams', function (Blueprint $table) {
            $table->string('exam_type')->nullable();
            $table->integer('duration')->default(90);
        });
    }
};
