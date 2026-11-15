<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tài khoản đang bị khóa theo cách cũ (rejected) chuyển sang is_active = 0
        DB::table('users')
            ->where('approval_status', 'rejected')
            ->update(['is_active' => false]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved');
        });

        DB::table('users')
            ->where('is_active', false)
            ->update(['approval_status' => 'rejected']);
    }
};
