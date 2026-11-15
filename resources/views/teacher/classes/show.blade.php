@extends('layouts.teacher')

@section('title', 'Danh sách lớp: ' . $class->name)

@section('content')
<div class="container mx-auto">
    <!-- Nút quay lại & Tiêu đề -->
    <div class="mb-6">
        <a href="{{ route('teacher.dashboard') }}" class="text-blue-600 hover:underline flex items-center text-sm font-medium mb-3">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Quay lại Tổng quan
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Lớp {{ $class->name }}</h2>
                <p class="text-gray-500 mt-1">Khối {{ $class->grade }} • Tổng số: <span class="font-bold text-blue-600">{{ $students->count() }}</span> học sinh</p>
            </div>

            <!-- Hiển thị lịch học góc phải -->
            <div class="bg-blue-50 text-blue-800 px-4 py-3 rounded-lg border border-blue-100 text-sm">
                <div class="font-semibold mb-1 flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Lịch học
                </div>
                @if($class->schedules->count() > 0)
                <ul class="space-y-1">
                    @foreach($class->schedules as $sch)
                    <li>{{ $sch->day_of_week }}: {{ \Carbon\Carbon::parse($sch->start_time)->format('H\gi') }} - {{ \Carbon\Carbon::parse($sch->end_time)->format('H\gi') }}</li>
                    @endforeach
                </ul>
                @else
                <span class="italic text-gray-500">Chưa xếp lịch</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Bảng danh sách học sinh -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-700">Danh sách học sinh</h3>
            <!-- Chỗ này sau này bạn có thể thêm nút "Xuất Excel" nếu cần -->
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold border-b w-16 text-center">STT</th>
                        <th class="p-4 font-semibold border-b">Học sinh</th>
                        <th class="p-4 font-semibold border-b">Trạng thái</th>
                        <th class="p-4 font-semibold border-b text-center">Lịch sử làm bài</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse($students as $index => $student)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="p-4 text-center font-medium text-gray-500">{{ $index + 1 }}</td>
                        <td class="p-4">
                            <div class="font-bold text-gray-800">{{ $student->name }}</div>
                            <div class="text-gray-500 text-xs mt-0.5">{{ $student->email }}</div>
                        </td>
                        <td class="p-4">
                            @if($student->is_active)
                            <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-[11px] font-bold uppercase tracking-wide">Đang học</span>
                            @else
                            <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-[11px] font-bold uppercase tracking-wide">Đã khóa</span>
                            @endif
                        </td>
                        <td class="p-4 text-center text-gray-500">
                            <!-- Nút này sau này gắn link tới trang xem điểm của học sinh -->
                            <a href="{{ route('teacher.students.history', $student->id) }}" class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                                Xem kết quả
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">
                            Lớp học này hiện tại chưa có học sinh nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection