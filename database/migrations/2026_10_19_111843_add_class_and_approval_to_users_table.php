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
        Schema::table('users', function (Blueprint $table) {
            // Lớp của học sinh (cho phép trống vì admin/giáo viên không có)
            $table->unsignedBigInteger('class_id')->nullable()->after('id');

            // Trạng thái duyệt: chờ duyệt (pending), đã duyệt (approved), bị từ chối (rejected).
            // Mặc định là 'approved' để các tài khoản đang có vẫn đăng nhập bình thường.
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('approved')
                ->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['class_id', 'approval_status']);
        });
    }
};
