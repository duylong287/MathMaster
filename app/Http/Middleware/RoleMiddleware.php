<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Chỉ cho phép người dùng có vai trò nằm trong danh sách $roles.
     * Cách dùng trong routes: ->middleware(['auth', 'role:teacher'])
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Tài khoản bị khóa sau khi đã đăng nhập: đăng xuất ngay, không chờ hết phiên
        if ($user && !$user->is_active) {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Tài khoản đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.']);
        }

        if (!$user || !in_array($user->role, $roles, true)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
