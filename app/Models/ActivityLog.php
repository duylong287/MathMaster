<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $guarded = []; // Cho phép điền mọi cột

    // Liên kết với bảng users
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}