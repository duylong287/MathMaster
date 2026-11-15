<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes'; // Trỏ đúng vào bảng classes
    protected $fillable = ['name', 'grade', 'teacher_id', 'status'];

    // Quan hệ với Giáo viên phụ trách
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Quan hệ với Lịch học
    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class, 'class_id');
    }

    // Quan hệ với Học sinh trong lớp
    public function students()
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'student');
    }

   
}