<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sử dụng lệnh SQL thuần để đổi cột type sang VARCHAR(255), cho phép lưu chuỗi tự do
        DB::statement("ALTER TABLE exams MODIFY type VARCHAR(255) NULL");
    }

    public function down(): void
    {
        // Không bắt buộc phải viết lệnh rollback cho thao tác này
    }
};