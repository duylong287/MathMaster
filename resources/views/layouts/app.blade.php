<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMaster - Quản trị</title>
    @vite('resources/css/app.css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50 font-[Inter] text-gray-800 flex h-screen overflow-hidden">

    <!-- Sidebar (Thanh menu bên trái) -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col hidden md:flex h-screen">
        
        <!-- Logo -->
        <div class="h-16 shrink-0 flex items-center px-6 bg-slate-950 font-bold text-xl tracking-wider">
            <span class="text-blue-400 mr-2" style="font-size:1.75rem; line-height:1;">∞</span> MathMaster
        </div>

        <!-- Menu Navigation (Có thanh cuộn nếu menu quá dài) -->
        <nav class="flex-1 px-4 py-6 overflow-y-auto custom-scrollbar">
            
            <!-- 1. Tổng quan -->
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Tổng quan
            </a>

            <!-- 2. Quản lý Chuyên đề -->
            <a href="{{ route('admin.topics.index') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('admin.topics.*') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                Quản lý Chuyên đề
            </a>

            <!-- 3. Quản lý Tài khoản -->
            <a href="{{ route('admin.users.index') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Quản lý Tài khoản
            </a>

            <!-- 4. Quản lý Lớp học -->
            <a href="{{ route('admin.classes.index') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('admin.classes.*') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Quản lý Lớp học
            </a>

            <!-- 5. Nhật ký hoạt động -->
            <a href="{{ route('admin.logs.index') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('admin.logs.*') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Nhật ký hoạt động
            </a>

            <!-- 6. Chỉ tiêu ĐGNL -->
            <a href="{{ route('admin.settings.edit') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3 opacity-90">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
                Chỉ tiêu ĐGNL
            </a>

            <!-- 7. Thông tin cá nhân -->
            <a href="{{ route('admin.profile') }}"
               class="flex items-center px-4 py-3 rounded-xl transition-all font-medium {{ request()->routeIs('admin.profile') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Thông tin cá nhân
            </a>
        </nav>

        <!-- Nút Đăng xuất (Cố định ở dưới cùng) -->
        <div class="shrink-0 p-4 border-t border-slate-800">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-4 py-3 rounded-xl text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 transition-all font-medium group">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Đăng xuất
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content (Nội dung chính) -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Header mới gọn gàng hơn -->
        <header class="h-16 shrink-0 bg-white shadow-sm flex items-center justify-between px-6 z-10">
            <h2 class="font-semibold text-gray-800 text-lg">@yield('title', 'Bảng điều khiển')</h2>

            <!-- Hiển thị Avatar & Tên User (Giống trong ảnh tham khảo) -->
            @auth
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                    {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                </div>
                <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ Auth::user()->name }}</span>
            </div>
            @endauth
        </header>

        <!-- Dynamic Content Area -->
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
            @yield('content')
        </main>
    </div>

</body>
</html>