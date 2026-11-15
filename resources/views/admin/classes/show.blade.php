@extends('layouts.app')

@section('title', 'Danh sách lớp: ' . $class->name)

@section('content')
<div class="container mx-auto">
    <!-- Nút quay lại -->
    <div class="mb-4">
        <a href="{{ route('admin.classes.index') }}" class="text-blue-600 hover:underline flex items-center text-sm font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Quay lại danh sách lớp
        </a>
    </div>

    <!-- Thông tin tổng quan lớp -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-6 flex flex-wrap lg:flex-nowrap gap-6 items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-3">
                Lớp {{ $class->name }}
                @if($class->status === 'active')
                    <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold uppercase tracking-wide">Đang hoạt động</span>
                @else
                    <span class="px-2.5 py-1 bg-gray-200 text-gray-600 rounded-full text-xs font-semibold uppercase tracking-wide">Đã đóng</span>
                @endif
            </h2>
            <div class="text-sm text-gray-600 space-y-1">
                <p><span class="font-semibold w-24 inline-block">Khối:</span> {{ $class->grade }}</p>
                <p><span class="font-semibold w-24 inline-block">Giáo viên:</span> <span class="text-blue-700 font-medium">{{ $class->teacher ? $class->teacher->name : 'Chưa phân công' }}</span></p>
                <p><span class="font-semibold w-24 inline-block">Lịch học:</span> 
                    @if($class->schedules->count() > 0)
                        {{ $class->schedules->map(function($sch) { 
                            return $sch->day_of_week . ': ' . \Carbon\Carbon::parse($sch->start_time)->format('H\gi') . '-' . \Carbon\Carbon::parse($sch->end_time)->format('H\gi'); 
                        })->implode('; ') }}
                    @else
                        <span class="text-red-500 italic">Chưa xếp lịch</span>
                    @endif
                </p>
            </div>
        </div>
        
        <!-- Khối thống kê sĩ số nhanh -->
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl text-center min-w-[150px]">
            <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">Sĩ số</p>
            <p class="text-4xl font-black text-blue-700">{{ $students->total() }}</p>
            <p class="text-xs text-blue-600 mt-1 font-medium">Học sinh</p>
        </div>
    </div>

    <!-- Danh sách học sinh -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="font-bold text-gray-800">Danh sách học sinh</h3>
            <!-- Chỗ này sau này có thể thêm nút "Thêm học sinh vào lớp" nếu cần -->
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold border-b w-16 text-center">STT</th>
                        <th class="p-4 font-semibold border-b">Họ và tên</th>
                        <th class="p-4 font-semibold border-b">Email / Tài khoản</th>
                        <th class="p-4 font-semibold border-b text-center">Trạng thái</th>
                        <th class="p-4 font-semibold border-b text-center">Ngày tham gia</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-200">
                    @forelse($students as $index => $student)
                        <tr class="hover:bg-gray-50">
                            <td class="p-4 text-center text-gray-500">{{ $students->firstItem() + $index }}</td>
                            <td class="p-4 font-medium text-gray-900">{{ $student->name }}</td>
                            <td class="p-4 text-gray-600">{{ $student->email }}</td>
                            <td class="p-4 text-center">
                                @if($student->is_active)
                                    <span class="text-green-600 font-medium">Đang học</span>
                                @else
                                    <span class="text-red-500 font-medium">Đã khóa</span>
                                @endif
                            </td>
                            <td class="p-4 text-center text-gray-500">{{ $student->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">
                                Lớp học này hiện chưa có học sinh nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <div class="p-4 border-t">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection