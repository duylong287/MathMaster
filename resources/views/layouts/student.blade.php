<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Học sinh MathMaster</title>
    @vite('resources/css/app.css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50 font-[Inter] text-gray-800 antialiased">

    <!-- Sidebar Học sinh: cùng kiểu với thanh menu của Giáo viên và Quản trị viên -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col fixed inset-y-0 left-0 z-20">

        <!-- Logo -->
        <div class="h-16 shrink-0 flex items-center px-6 bg-slate-950 font-bold text-xl tracking-wider">
            <span class="text-blue-400 mr-2">∞</span> MathMaster
        </div>

        <!-- Menu Navigation -->
        <nav class="flex-1 px-4 py-6 overflow-y-auto custom-scrollbar">

            <!-- 1. Trang chủ -->
            <a href="{{ route('student.dashboard') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('student.dashboard') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Trang chủ
            </a>

            <!-- 2. Đề thi -->
            <a href="{{ route('student.exams.index') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('student.exams.*') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Đề thi
            </a>

            <!-- 3. Lịch sử làm bài -->
            <a href="{{ route('student.history') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('student.history') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Lịch sử làm bài
            </a>

            <!-- 4. Phân tích năng lực -->
            <a href="{{ route('student.analytics') }}"
               class="flex items-center px-4 py-3 mb-1.5 rounded-xl transition-all font-medium {{ request()->routeIs('student.analytics') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Phân tích năng lực
            </a>

            <!-- 5. Thông tin cá nhân -->
            <a href="{{ route('student.profile') }}"
               class="flex items-center px-4 py-3 rounded-xl transition-all font-medium {{ request()->routeIs('student.profile') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
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

    <!-- Nội dung chính -->
    <div class="ml-64 flex flex-col min-h-screen bg-gray-50">

        <!-- Header (cùng kiểu với Giáo viên / Quản trị viên, dính trên cùng khi cuộn) -->
        <header class="h-16 shrink-0 bg-white shadow-sm flex items-center justify-between px-6 z-10 sticky top-0">
            <h2 class="font-semibold text-gray-800 text-lg">@yield('title', 'Bảng điều khiển')</h2>

            <!-- Avatar và tên người dùng -->
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                    {{ mb_substr(Auth::user()->name, 0, 1, 'UTF-8') }}
                </div>
                <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ Auth::user()->name }}</span>
            </div>
        </header>

        <main class="flex-1 p-6 bg-gray-50">
            @yield('content')
        </main>
    </div>
</body>

</html>