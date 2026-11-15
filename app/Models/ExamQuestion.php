<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    // Khai báo tên bảng (tùy chọn nếu tên bảng chuẩn Laravel, nhưng nên ghi rõ cho an toàn)
    protected $table = 'exam_questions';

    // Cho phép lưu các cột này vào database (Bao gồm cả difficulty)
    protected $fillable = [
        'exam_id',
        'part',
        'number',
        'topic_id',
        'difficulty',
        'correct_answer',
        'decimal_places'
    ];

    // Quan hệ với bảng Exams
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    // Quan hệ với bảng Topics
    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }
}