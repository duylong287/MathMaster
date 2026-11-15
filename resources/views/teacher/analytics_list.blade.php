@extends('layouts.teacher')

@section('title', 'Danh sách học sinh')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-800">Phân tích năng lực học sinh</h2>
        <p class="text-sm text-gray-500 mt-1">Chọn một học sinh trong các lớp bạn phụ trách để xem biểu đồ thống kê chi tiết.</p>
    </div>

    <!-- Thanh Bộ lọc (Filter Toolbar) -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
        <form action="{{ route('teacher.analytics.list') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            
            <!-- Tìm kiếm theo tên/email -->
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ $searchKeyword ?? '' }}" placeholder="Tìm tên hoặc email học sinh..." 
                       class="pl-10 w-full border border-gray-300 rounded-xl text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors">
            </div>

            <!-- Lọc theo lớp -->
            <div class="sm:w-64">
                <select name="class_id" class="w-full border border-gray-300 rounded-xl text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors cursor-pointer" onchange="this.form.submit()">
                    <option value="">-- Tất cả các lớp --</option>
                    @foreach($teacherClasses as $class)
                        <option value="{{ $class->id }}" {{ ($classFilter == $class->id) ? 'selected' : '' }}>
                            Lớp {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Nút Tìm kiếm -->
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-xl transition-colors text-sm flex items-center justify-center">
                Lọc dữ liệu
            </button>
            
            <!-- Nút Xóa lọc -->
            @if($searchKeyword || $classFilter)
                <a href="{{ route('teacher.analytics.list') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-xl transition-colors text-sm flex items-center justify-center">
                    Bỏ lọc
                </a>
            @endif
        </form>
    </div>

    <!-- Bảng Danh sách học sinh -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-bold border-b border-gray-200 w-16 text-center">STT</th>
                        <th class="p-4 font-bold border-b border-gray-200">Thông tin học sinh</th>
                        <th class="p-4 font-bold border-b border-gray-200 text-center">Lớp</th>
                        <th class="p-4 font-bold border-b border-gray-200 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                    @forelse($students as $index => $student)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 font-medium text-center text-gray-500">{{ $index + 1 }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ mb_substr($student->name, 0, 1, 'UTF-8') }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">{{ $student->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $student->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 text-center font-medium">
                            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs border border-gray-200 font-bold">
                                {{ $student->class->name ?? 'Chưa xếp lớp' }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('teacher.students.analytics', $student->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg font-medium transition-colors text-xs group">
                                <svg class="w-4 h-4 mr-1.5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                Xem phân tích
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            <p class="text-gray-500 font-medium">Không tìm thấy học sinh nào phù hợp với bộ lọc.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection