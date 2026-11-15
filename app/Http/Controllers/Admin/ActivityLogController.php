<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Khởi tạo query và gọi sẵn liên kết bảng user để tối ưu hiệu suất
        $query = ActivityLog::with('user');

        // Lọc theo Loại hành động
        if ($request->has('action_type') && $request->action_type != '') {
            $query->where('action', $request->action_type);
        }

        // Lọc / Tìm kiếm theo tên hoặc email người dùng
        if ($request->has('user_search') && $request->user_search != '') {
            $search = $request->user_search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Lấy danh sách các loại hành động độc nhất để đưa vào Dropdown bộ lọc
        $actionTypes = ActivityLog::select('action')->distinct()->pluck('action');

        // Sắp xếp mới nhất và phân trang
        $logs = $query->latest()->paginate(20);

        return view('admin.logs.index', compact('logs', 'actionTypes'));
    }
}