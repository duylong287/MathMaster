<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cho phép cột pdf_path được phép trống (NULL)
        DB::statement("ALTER TABLE exams MODIFY pdf_path VARCHAR(255) NULL");
    }

    public function down(): void
    {
        // Không bắt buộc phải viết lệnh rollback
    }
};