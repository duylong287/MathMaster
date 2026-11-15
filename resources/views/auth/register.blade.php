<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký | MathMaster</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        math: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4 sm:p-8 font-sans text-slate-800 relative overflow-x-hidden">

    <!-- Các thẻ div trang trí nền -->
    <div class="fixed top-0 left-0 w-64 h-64 bg-math-100 rounded-full mix-blend-multiply filter blur-3xl opacity-70 -translate-x-1/2 -translate-y-1/2 z-0"></div>
    <div class="fixed bottom-0 right-0 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50 translate-x-1/3 translate-y-1/3 z-0"></div>

    <div class="relative z-10 w-full max-w-5xl bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden flex flex-col lg:flex-row-reverse border border-gray-100">
        
        <!-- Cột Banner (Đảo sang phải cho khác biệt chút so với Login) -->
        <div class="lg:w-1/2 p-8 lg:p-12 flex flex-col items-center justify-center relative overflow-hidden bg-gradient-to-br from-math-50 to-blue-50">
            <div class="absolute inset-0 opacity-10 pointer-events-none select-none">
                <svg class="absolute top-10 right-10 w-16 h-16 text-math-800 transform -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M6,6V8H8.5L7.13,17.65C7,18.39 7.31,19 7.91,19C8.61,19 9.17,18.42 9.32,17.66L10.59,8H14.19L12.57,17.43C12.39,18.46 12.91,19 13.62,19C14.73,19 15.65,17.9 15.93,16.59L17.2,10.6C17.37,9.75 18,9 18.73,9C19.14,9 19.5,9.15 19.78,9.44L18.37,10.85C18.25,10.7 18,10.5 17.65,10.77L16,18H13.91L15.62,8H17V6H6Z" /></svg>
                <svg class="absolute bottom-20 left-10 w-20 h-20 text-math-800 transform rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12.9 5.86L15.22 8.18L13.8 9.6L12.19 8H19.5V10H12.19L13.8 11.6L12.38 13.03L9.36 10L10.77 8.58L10.15 7.96C9.28 7.09 9.28 5.67 10.15 4.8L10.77 4.19L11.5 3.46C12.55 2.41 14.26 2.41 15.31 3.46L19 7.15V13.85L15.31 17.54C14.26 18.59 12.55 18.59 11.5 17.54L9 15.03V5.86H12.9M4 14V12H7V14H4Z" /></svg>
            </div>

            <div class="relative w-full max-w-sm mb-8 z-10">
                <div class="aspect-square rounded-3xl overflow-hidden shadow-2xl relative bg-math-800">
                    <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Education" class="w-full h-full object-cover opacity-90 mix-blend-luminosity hover:mix-blend-normal transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-math-900/50 to-transparent"></div>
                </div>
            </div>

            <div class="text-center mt-6 z-10">
                <h2 class="text-2xl font-bold text-math-900 mb-2">Bắt đầu hành trình mới</h2>
                <p class="text-sm text-gray-600 max-w-xs mx-auto leading-relaxed">
                    Tạo tài khoản ngay hôm nay để truy cập kho đề thi khổng lồ.
                </p>
            </div>
        </div>

        <!-- Cột Form Đăng ký -->
        <div class="lg:w-1/2 p-8 sm:p-12 flex flex-col justify-center bg-white z-10 relative">
            
            <div class="w-full max-w-md mx-auto">
                <div class="flex items-center justify-center lg:justify-start mb-6">
                    <div class="bg-math-800 p-2 rounded-xl mr-3 shadow-md">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14s1.5 2 4 2 4-2 4-2M8 10s1.5-2 4-2 4 2 4 2M3 12a9 9 0 1018 0 9 9 0 00-18 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-math-900 tracking-tight">MathMaster</h1>
                </div>

                <div class="text-center lg:text-left mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Tạo tài khoản mới</h2>
                    <p class="text-gray-500 text-sm">Điền thông tin bên dưới để trở thành học viên.</p>
                </div>

                <!-- Hiển thị lỗi thông báo -->
                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-medium">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register') }}" method="POST" class="space-y-4">
                    @csrf 

                    <!-- Họ và Tên -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Họ và Tên</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-math-500 focus:border-math-500 focus:bg-white transition-colors outline-none"
                            placeholder="Nguyễn Văn A">
                    </div>

                    <div class="mt-4">
                        <label for="grade" class="block text-sm font-medium text-gray-700">Khối lớp</label>
                        <select id="grade" name="grade" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Chọn khối lớp --</option>
                            <option value="10">Khối 10</option>
                            <option value="11">Khối 11</option>
                            <option value="12">Khối 12</option>
                        </select>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-math-500 focus:border-math-500 focus:bg-white transition-colors outline-none"
                            placeholder="email@example.com">
                    </div>

                    <!-- Mật khẩu -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Mật khẩu</label>
                        <input type="password" id="password" name="password" required
                            class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-math-500 focus:border-math-500 focus:bg-white transition-colors outline-none tracking-wider"
                            placeholder="Tối thiểu 8 ký tự">
                    </div>

                    <!-- Xác nhận Mật khẩu -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Xác nhận mật khẩu</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-math-500 focus:border-math-500 focus:bg-white transition-colors outline-none tracking-wider"
                            placeholder="Nhập lại mật khẩu">
                    </div>

                    <!-- Nút Đăng ký -->
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-math-800 hover:bg-math-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-math-700 transition-all duration-200 transform hover:-translate-y-0.5">
                            Hoàn tất Đăng ký
                        </button>
                    </div>
                </form>

                <!-- Trở về trang Đăng nhập -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Đã có tài khoản? 
                        <a href="{{ route('login') }}" class="font-semibold text-math-700 hover:text-math-800 transition-colors">
                            Đăng nhập ngay
                        </a>
                    </p>
                </div>

            </div>
        </div>
    </div>
</body>
</html>