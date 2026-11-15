<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// Nếu dùng Attribute giống User, bạn import thư viện tương ứng

class Exam extends Model
{
    // Cho phép thêm dữ liệu vào mọi cột
    protected $guarded = [];

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_exam', 'exam_id', 'class_id');
    }
}