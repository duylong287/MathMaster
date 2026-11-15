<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    // Các cột cho phép ghi dữ liệu
    protected $fillable = [
        'name',       // Tên chuyên đề
        'grade',      // Khối lớp (10, 11, 12)
        'is_active',  // Trạng thái (1: Hoạt động, 0: Vô hiệu hóa)
    ];
}