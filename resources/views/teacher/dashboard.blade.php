@extends('layouts.teacher')

@section('title', 'Tổng quan')

@section('content')
    <!-- ================= PHẦN 1: TỔNG QUAN ĐỀ THI ================= -->
    <div class="mb-6 flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Đề thi của bạn</h2>
            <p class="text-sm text-gray-500 mt-1">Theo dõi tiến độ ra đề và quản lý kho học liệu của bạn.</p>
        </div>
        
        <a href="{{ route('teacher.exams.create') }}" class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-sm transform hover:-translate-y-0.5">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tạo đề mới
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Số đề đang mở (Click để chuyển tới trang Quản lý kèm bộ lọc published) -->
        <a href="{{ route('teacher.exams.index', ['status' => 'published']) }}" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md hover:-translate-y-1 transition duration-200 cursor-pointer group">
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1 group-hover:text-green-600 transition-colors">Đề thi đang mở</p>
                <h3 class="text-3xl font-bold text-green-600">{{ $openExams }}</h3>
                <p class="text-xs text-gray-400 mt-2">Học sinh đang có thể vào thi</p>
            </div>
            <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center text-green-500 group-hover:bg-green-100 transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </a>

        <!-- Số đề nháp (Click để chuyển tới trang Quản lý kèm bộ lọc draft) -->
        <a href="{{ route('teacher.exams.index', ['status' => 'draft']) }}" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md hover:-translate-y-1 transition duration-200 cursor-pointer group">
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1 group-hover:text-blue-600 transition-colors">Đề thi bản nháp</p>
                <h3 class="text-3xl font-bold text-blue-600">{{ $draftExams }}</h3>
                <p class="text-xs text-gray-400 mt-2">Đang soạn thảo, chưa công bố</p>
            </div>
            <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-100 transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
        </a>
    </div>

    <!-- ================= PHẦN 2: QUẢN LÝ LỚP HỌC (Giữ nguyên) ================= -->
    <div class="mt-10">
        <div class="mb-6">
            <h3 class="text-xl font-bold text-gray-800">Các lớp đang phụ trách</h3>
            <p class="text-gray-500 text-sm mt-1">Thông tin sĩ số và lịch học các lớp của bạn</p>
        </div>

        @if($classes->isEmpty())
            <!-- Cảnh báo khi chưa có lớp -->
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-8 rounded-xl text-center shadow-sm">
                <svg class="w-12 h-12 mx-auto text-red-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <p class="text-lg font-medium">Bạn chưa được phân công giảng dạy lớp nào.</p>
                <p class="text-sm mt-1">Liên hệ quản trị viên để được gán lớp.</p>
            </div>
        @else
            <!-- Lưới hiển thị danh sách lớp -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($classes as $class)
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-4">
                            <h4 class="text-lg font-bold text-blue-700">{{ $class->name }}</h4>
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md text-xs font-bold uppercase tracking-wider">Khối {{ $class->grade }}</span>
                        </div>

                        <div class="text-sm text-gray-600 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="font-semibold text-gray-700 mr-1">Sĩ số:</span> {{ $class->students_count }} học sinh
                        </div>

                        <!-- Thêm mb-5 và flex-1 để đẩy nút bấm xuống đáy -->
                        <div class="text-sm text-gray-600 flex-1 mb-5">
                            <div class="flex items-start">
                                <svg class="w-4 h-4 mr-2 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="font-semibold text-gray-700">Lịch học:</span>
                            </div>
                            @if($class->schedules->count() > 0)
                                <ul class="mt-2 space-y-1 ml-6 text-gray-500">
                                    @foreach($class->schedules as $sch)
                                        <li>{{ $sch->day_of_week }}: {{ \Carbon\Carbon::parse($sch->start_time)->format('H\gi') }} - {{ \Carbon\Carbon::parse($sch->end_time)->format('H\gi') }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="italic text-gray-400 ml-6 block mt-1">Chưa xếp lịch</span>
                            @endif
                        </div>
                        
                        <!-- Nút bấm chức năng -->
                        <a href="{{ route('teacher.classes.show', $class->id) }}" class="block w-full text-center px-4 py-2.5 bg-blue-50 text-blue-700 font-semibold rounded-lg hover:bg-blue-600 hover:text-white transition-colors border border-blue-100 hover:border-blue-600">
                            Xem danh sách lớp
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection