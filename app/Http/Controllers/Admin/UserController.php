<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\AccountCreatedMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // 1. Trang danh sách: Hiển thị, lọc và tìm kiếm
    public function index(Request $request)
    {
        // Lấy danh sách user kèm thông tin lớp học
        $query = \App\Models\User::with('schoolClass');

        // Bộ lọc theo vai trò (role)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Bộ lọc theo lớp học
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Tìm kiếm theo tên hoặc email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Lấy danh sách các lớp đang hoạt động để thả vào bộ lọc
        $classes = \App\Models\SchoolClass::where('status', 'active')->orderBy('grade')->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'classes'));
    }

    // --- HIỂN THỊ FORM TẠO TÀI KHOẢN MỚI ---
    public function create()
    {
        // Lấy danh sách các lớp đang hoạt động để gán cho học sinh (nếu cần)
        $classes = \App\Models\SchoolClass::where('status', 'active')->orderBy('grade')->orderBy('name')->get();

        return view('admin.users.create', compact('classes'));
    }

    // --- XỬ LÝ LƯU TÀI KHOẢN MỚI ---
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,teacher,student',
            'class_id' => 'nullable|exists:classes,id',
        ], $this->validationMessages());

        // 1. Sinh mật khẩu ngẫu nhiên 8 ký tự
        $plainPassword = \Illuminate\Support\Str::random(8);

        // Nếu không phải học sinh thì không được gán lớp
        $classId = $request->role === 'student' ? $request->class_id : null;

        // 2 + 3. Tạo tài khoản rồi gửi email. Gửi mail lỗi thì hoàn tác việc tạo tài khoản
        // để không có tài khoản mà không ai biết mật khẩu.
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $plainPassword, $classId) {
                $user = \App\Models\User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => \Illuminate\Support\Facades\Hash::make($plainPassword),
                    'role' => $request->role,
                    'is_active' => true,
                    'class_id' => $classId,
                ]);

                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\AccountCreatedMail($user->name, $user->email, $plainPassword));
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Không gửi được email thông tin đăng nhập nên chưa tạo tài khoản. Hãy kiểm tra cấu hình email rồi thử lại.');
        }

        return redirect()->route('admin.users.index')->with('success', 'Đã tạo tài khoản và gửi thông tin đăng nhập qua email thành công!');
    }

    // --- XỬ LÝ KHÓA / MỞ KHÓA TÀI KHOẢN ---
    public function toggleActive(\App\Models\User $user)
    {
        // Chống việc Admin tự khóa chính mình
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình!');
        }

        // Đổi trạng thái: đang hoạt động thì khóa, đang bị khóa thì mở
        $newActive = !$user->is_active;

        // NGHIỆP VỤ: Nếu khóa Giáo viên, phải kiểm tra xem có đang dạy lớp nào không
        if (!$newActive && $user->role === 'teacher') {
            $affectedClasses = \App\Models\SchoolClass::where('teacher_id', $user->id)
                                ->where('status', 'active')
                                ->count();

            if ($affectedClasses > 0) {
                return back()->with('error', "Không thể khóa! Giáo viên này đang phụ trách $affectedClasses lớp đang hoạt động. Vui lòng chuyển lớp cho giáo viên khác trước.");
            }
        }

        // Cập nhật trạng thái
        $user->update(['is_active' => $newActive]);

        $msg = $newActive ? "Đã MỞ KHÓA tài khoản {$user->name}." : "Đã KHÓA tài khoản {$user->name}.";
        return back()->with('success', $msg);
    }

    // 5. Cập nhật vai trò (Đổi quyền)
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:student,teacher,admin'
        ]);

        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return back()->with('error', 'Bạn không thể tự tước quyền Admin của chính mình!');
        }

        $user->update(['role' => $request->role]);
        return back()->with('success', 'Đã cập nhật phân quyền thành công!');
    }


    // --- HIỂN THỊ FORM SỬA TÀI KHOẢN ---
    public function edit(\App\Models\User $user)
    {
        // Lấy danh sách các lớp đang hoạt động để cho phép học sinh đổi lớp
        $classes = \App\Models\SchoolClass::where('status', 'active')->orderBy('grade')->orderBy('name')->get();

        // Giáo viên đang phụ trách lớp nào (để cảnh báo trước khi đổi vai trò)
        $teachingClasses = $user->role === 'teacher'
            ? \App\Models\SchoolClass::where('teacher_id', $user->id)->where('status', 'active')->orderBy('name')->pluck('name')
            : collect();

        return view('admin.users.edit', compact('user', 'classes', 'teachingClasses'));
    }

    /** Thông báo lỗi kiểm tra dữ liệu (dùng chung cho tạo và sửa tài khoản). */
    private function validationMessages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.max' => 'Họ và tên không được dài quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Vai trò không hợp lệ.',
            'class_id.exists' => 'Lớp được chọn không tồn tại.',
        ];
    }


    // --- XỬ LÝ CẬP NHẬT TÀI KHOẢN ---
    public function update(Request $request, \App\Models\User $user)
    {
        // 1. Kiểm tra dữ liệu
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,teacher,student',
            'class_id' => 'nullable|exists:classes,id',
        ], $this->validationMessages());

        // Admin không được tự hạ quyền của chính mình qua form Sửa
        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return back()->with('error', 'Bạn không thể tự tước quyền Admin của chính mình!')->withInput();
        }

        $warnings = [];
        $classId = $request->role === 'student' ? $request->class_id : null;

        // 2. Nếu đổi giáo viên sang vai trò khác, cảnh báo nếu thầy đang dạy
        if ($user->role === 'teacher' && $request->role !== 'teacher') {
            $affectedClasses = \App\Models\SchoolClass::where('teacher_id', $user->id)
                                ->where('status', 'active')->get();
            if ($affectedClasses->count() > 0) {
                $classNames = $affectedClasses->pluck('name')->implode(', ');
                $warnings[] = "Cảnh báo: Người này từng là Giáo viên và đang phụ trách các lớp: $classNames. Hãy gán giáo viên khác cho lớp!";
            }
        }

        // 3. Cập nhật dữ liệu (không đổi trạng thái khóa/mở khóa, việc đó có nút riêng)
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'class_id' => $classId,
        ]);

        $successMsg = 'Cập nhật tài khoản thành công!';
        if (count($warnings) > 0) {
            $request->session()->flash('warning_list', $warnings);
        }

        return redirect()->route('admin.users.index')->with('success', $successMsg);
    }
}