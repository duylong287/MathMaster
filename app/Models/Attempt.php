<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory;

    // Khai báo các cột được phép lưu dữ liệu
    protected $fillable = [
        'student_id',
        'exam_id',
        'started_at',
        'status',
    ];
    protected $casts = [
        'started_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function user()
    {
        // Đảm bảo bạn đã use App\Models\User; ở đầu file hoặc viết đầy đủ đường dẫn như sau:
        return $this->belongsTo(\App\Models\User::class, 'student_id'); 
    }
}