@extends('layouts.app')

@section('title', 'Thông tin cá nhân')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Thông tin cá nhân</h2>
        <p class="text-sm text-gray-500 mt-1">Quản lý thông tin tài khoản và bảo mật của quản trị viên.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm">
            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Cột thông tin cơ bản -->
        <div class="md:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-center">
                <div class="w-24 h-24 bg-blue-600 text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-4 shadow-md">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
                <h3 class="text-xl font-bold text-gray-800 break-words">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 mt-1 break-all">Email: {{ $user->email }}</p>

                <div class="mt-6 text-left border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Vai trò</p>
                    <p class="text-sm font-bold text-blue-600 mt-0.5">Quản trị viên (Admin)</p>
                </div>
            </div>
        </div>

        <!-- Cột đổi mật khẩu -->
        <div class="md:col-span-2">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3">Đổi mật khẩu</h3>
                <p class="text-sm text-gray-500 mt-3 mb-5">Mật khẩu mới cần có ít nhất 8 ký tự.</p>

                <form id="passwordForm" action="{{ route('admin.update_password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5">
                        <div>
                            <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">Mật khẩu hiện tại</label>
                            <div class="relative">
                                <input type="password" id="current_password" name="current_password" autocomplete="current-password" required
                                       class="w-full p-2.5 pr-11 border {{ $errors->has('current_password') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500">
                                <button type="button" data-toggle="current_password" aria-label="Hiện hoặc ẩn mật khẩu" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-blue-600 focus:outline-none">
                                    <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <svg class="eye-shut w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Mật khẩu mới</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" autocomplete="new-password" required
                                       class="w-full p-2.5 pr-11 border {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500">
                                <button type="button" data-toggle="password" aria-label="Hiện hoặc ẩn mật khẩu" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-blue-600 focus:outline-none">
                                    <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <svg class="eye-shut w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Xác nhận mật khẩu mới</label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required
                                       class="w-full p-2.5 pr-11 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500">
                                <button type="button" data-toggle="password_confirmation" aria-label="Hiện hoặc ẩn mật khẩu" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-blue-600 focus:outline-none">
                                    <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <svg class="eye-shut w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" id="passwordSubmit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                            Cập nhật mật khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Hiện/ẩn mật khẩu: mỗi nút điều khiển đúng ô của nó và đổi biểu tượng mắt
    document.querySelectorAll('[data-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.toggle);
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('.eye-open').classList.toggle('hidden', show);
            btn.querySelector('.eye-shut').classList.toggle('hidden', !show);
        });
    });

    // Chặn bấm gửi hai lần
    var form = document.getElementById('passwordForm');
    form.addEventListener('submit', function () {
        var btn = document.getElementById('passwordSubmit');
        btn.disabled = true;
        btn.textContent = 'Đang cập nhật...';
    });
</script>
@endsection