<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    // Bỏ qua timestamp nếu bảng của bạn không có cột created_at, updated_at
    // public $timestamps = false; 


    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $guarded = []; // Cho phép điền mọi cột (key, value)
}