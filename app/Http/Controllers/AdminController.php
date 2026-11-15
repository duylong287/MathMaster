<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Hiển thị giao diện Dashboard với các số liệu thống kê
    public function index()
    {
        // 1. Tổng số học sinh & Giáo viên
        $totalStudents = User::where('role', 'student')->count();
        $totalTeachers = User::where('role', 'teacher')->count();

        // 2. Tổng số chuyên đề chia theo khối
        $topics = Topic::select('grade', DB::raw('count(*) as count'))
                       ->groupBy('grade')
                       ->pluck('count', 'grade');
        $topics10 = $topics['10'] ?? 0;
        $topics11 = $topics['11'] ?? 0;
        $topics12 = $topics['12'] ?? 0;

        // 3. Lớp học: đang hoạt động / đã đóng
        $classesByStatus = DB::table('classes')
                             ->select('status', DB::raw('count(*) as count'))
                             ->groupBy('status')
                             ->pluck('count', 'status');
        $activeClasses = $classesByStatus['active'] ?? 0;
        $closedClasses = $classesByStatus['closed'] ?? 0;

        // 4. Các mục cần chú ý
        // - Học sinh đang hoạt động nhưng chưa có lớp (không thấy đề thi nào)
        $studentsNoClass = User::where('role', 'student')
                               ->where('is_active', 1)
                               ->whereNull('class_id')
                               ->count();

        // - Lớp đang hoạt động nhưng chưa có giáo viên phụ trách
        $classesNoTeacher = DB::table('classes')
                              ->where('status', 'active')
                              ->whereNull('teacher_id')
                              ->count();

        // - Tài khoản đang bị khóa tạm (nhập sai mật khẩu) hoặc đã ngừng hoạt động
        $lockedAccounts = User::where('is_active', 0)
                              ->orWhere(function ($q) {
                                  $q->whereNotNull('locked_until')->where('locked_until', '>', now());
                              })
                              ->count();

        // Xử lý an toàn cho bảng exams và attempts (tránh lỗi nếu bạn chưa chạy migration)
        try {
            // 5. Tổng số đề thi (đang mở & bản nháp)
            $examsCount = DB::table('exams')
                            ->select('status', DB::raw('count(*) as count'))
                            ->groupBy('status')
                            ->pluck('count', 'status');
            $openExams = $examsCount['published'] ?? 0;
            $draftExams = $examsCount['draft'] ?? 0;
            $totalExams = $openExams + $draftExams;

            // 6. Tổng số lượt làm bài đã nộp
            $submittedAttempts = DB::table('attempts')->where('status', 'submitted')->count();
        } catch (\Exception $e) {
            // Nếu chưa có bảng trong Database thì tạm gán bằng 0
            $openExams = $draftExams = $totalExams = $submittedAttempts = 0;
        }

        return view('admin.dashboard', compact(
            'totalStudents', 'totalTeachers',
            'topics10', 'topics11', 'topics12',
            'activeClasses', 'closedClasses',
            'studentsNoClass', 'classesNoTeacher', 'lockedAccounts',
            'totalExams', 'openExams', 'draftExams',
            'submittedAttempts'
        ));
    }

    public function profile()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function updatePassword(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.'
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password)
        ]);

        return back()->with('success', 'Đã cập nhật mật khẩu thành công!');
    }
}