<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewPasswordMail;

class AuthController extends Controller
{
    private const MAX_FAILED_LOGINS = 5;
    private const LOCK_MINUTES = 15;

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return $this->fail('Sai email hoặc mật khẩu.');
        }

        // Tài khoản đang bị khóa tạm thời do nhập sai nhiều lần
        if ($user->locked_until) {
            $lockedUntil = Carbon::parse($user->locked_until);
            if ($lockedUntil->isFuture()) {
                $minutes = max(1, (int) ceil(($lockedUntil->getTimestamp() - now()->getTimestamp()) / 60));
                return $this->fail("Tài khoản đang bị khóa tạm thời. Vui lòng thử lại sau {$minutes} phút.");
            }
        }

        // Tài khoản bị vô hiệu hóa bởi quản trị viên
        if (!$user->is_active) {
            return $this->fail('Tài khoản đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.');
        }

        // Sai mật khẩu: tăng bộ đếm, đủ 5 lần thì khóa 15 phút
        if (!Hash::check($data['password'], $user->password)) {
            $user->failed_logins = $user->failed_logins + 1;

            if ($user->failed_logins >= self::MAX_FAILED_LOGINS) {
                $user->locked_until = now()->addMinutes(self::LOCK_MINUTES);
                $user->failed_logins = 0;
                $this->log(
                    $user->id,
                    'account_locked',
                    'Khóa tạm thời ' . self::LOCK_MINUTES . ' phút do nhập sai mật khẩu ' . self::MAX_FAILED_LOGINS . ' lần liên tiếp'
                );
            }

            $user->save();

            return $this->fail('Sai email hoặc mật khẩu.');
        }

        // Đăng nhập thành công: xóa bộ đếm lỗi
        $user->failed_logins = 0;
        $user->locked_until = null;
        $user->save();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $this->log($user->id, 'login', 'Đăng nhập thành công');

        return redirect()->route('home');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'grade' => ['required', 'in:10,11,12'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được đăng ký.',
            'grade.required' => 'Vui lòng chọn khối lớp.',
            'grade.in' => 'Khối lớp phải là 10, 11 hoặc 12.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
        ]);

        // Chỉ học sinh mới tự đăng ký; giáo viên và admin do quản trị viên tạo
        $id = DB::table('users')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'student',
            'grade' => (int) $data['grade'],
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Auth::loginUsingId($id);
        $request->session()->regenerate();
        $this->log($id, 'register', 'Học sinh đăng ký tài khoản mới, khối ' . $data['grade']);

        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($userId) {
            $this->log($userId, 'logout', 'Đăng xuất');
        }

        return redirect()->route('login');
    }

    private function fail(string $message)
    {
        return back()
            ->withErrors(['email' => $message])
            ->onlyInput('email');
    }

    private function log(int $userId, string $action, string $detail): void
    {
        DB::table('activity_logs')->insert([
            'user_id' => $userId,
            'action' => $action,
            'detail' => $detail,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendNewPassword(\Illuminate\Http\Request $request)
    {
        // 1. Kiểm tra email có tồn tại không
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'Email này không tồn tại trong hệ thống.'
        ]);

        $user = User::where('email', $request->email)->first();

        // 2. Sinh mật khẩu mới ngẫu nhiên (ví dụ: 8 ký tự chữ và số)
        $newPassword = Str::random(8);

        // 3. Mã hóa và lưu vào CSDL (giữ lại mật khẩu cũ để hoàn tác nếu gửi mail lỗi)
        $oldHash = $user->getRawOriginal('password');
        $user->password = Hash::make($newPassword);
        $user->save();

        // 4. Gửi email chứa mật khẩu mới. Gửi lỗi thì trả lại mật khẩu cũ,
        //    tránh để người dùng mất mật khẩu mà không nhận được mật khẩu mới.
        try {
            Mail::to($user->email)->send(new NewPasswordMail($user->name, $newPassword));
        } catch (\Throwable $e) {
            DB::table('users')->where('id', $user->id)->update(['password' => $oldHash]);
            report($e);

            return back()->withErrors(['email' => 'Không gửi được email lúc này. Mật khẩu của bạn chưa bị thay đổi, vui lòng thử lại sau.'])->onlyInput('email');
        }

        $this->log($user->id, 'password_reset', 'Cấp lại mật khẩu mới qua email');

        // 5. Trả về thông báo thành công
        return redirect()->route('login')->with('success', 'Mật khẩu mới đã được gửi. Vui lòng kiểm tra hộp thư (bao gồm cả thư rác).');
    }
}
