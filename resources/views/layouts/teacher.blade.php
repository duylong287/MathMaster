<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMaster - Giáo viên</title>
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

        <!-- Menu Navigation -->
        <nav class="flex-1 px-4 py-6 overflow-y-auto custom-scrollbar">
            
            <!-- 1. Tổng quan -->
            <a href="{{ route('teacher.dashboard') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('teacher.dashboard') || request()->routeIs('teacher.classes.*') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Tổng quan
            </a>

            <!-- 2. Quản lý Đề thi -->
            <a href="{{ route('teacher.exams.index') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('teacher.exams.*') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Quản lý Đề thi
            </a>

            <!-- Phân tích năng lực -->
            <a href="{{ route('teacher.analytics.list') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('teacher.analytics.*') || request()->routeIs('teacher.students.analytics') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Phân tích năng lực
            </a>

            <!-- 3. Thông tin cá nhân -->
            <a href="{{ route('teacher.profile') }}"
               class="flex items-center px-4 py-3 rounded-xl transition-all font-medium {{ request()->routeIs('teacher.profile') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
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
        
        <!-- Header -->
        <header class="h-16 shrink-0 bg-white shadow-sm flex items-center justify-between px-6 z-10">
            <h2 class="font-semibold text-gray-800 text-lg">@yield('title', 'Bảng điều khiển')</h2>

            <!-- Hiển thị Avatar & Tên User -->
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                    {{ mb_substr(Auth::user()->name, 0, 1, 'UTF-8') }}
                </div>
                <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ Auth::user()->name }}</span>
            </div>
        </header>

        <!-- Dynamic Content Area -->
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
            @yield('content')
        </main>
    </div>

</body>
</html>