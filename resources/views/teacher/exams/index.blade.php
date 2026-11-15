@extends('layouts.teacher')
@section('title', 'Quản lý Đề thi')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold">Quản lý danh sách đề thi</h2>

        <!-- Nút tạo đề thi mới -->
        <a href="{{ route('teacher.exams.create') }}" class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tạo đề thi mới
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium rounded-lg px-4 py-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm font-medium rounded-lg px-4 py-3">{{ session('error') }}</div>
    @endif

    <!-- Bộ lọc (Filter) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form method="GET" action="{{ route('teacher.exams.index') }}" class="flex flex-col md:flex-row gap-3 items-center">

            <!-- Select Trạng thái -->
            <div class="w-full md:w-1/4 relative">
                <select name="status" class="w-full appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg pl-4 pr-10 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition-all cursor-pointer">
                    <option value="">Tất cả trạng thái</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                </select>
                <!-- Icon Mũi tên tùy chỉnh -->
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>

            <!-- Select Khối lớp -->
            <div class="w-full md:w-1/4 relative">
                <select name="grade" class="w-full appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg pl-4 pr-10 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition-all cursor-pointer">
                    <option value="">Tất cả khối lớp</option>
                    <option value="10" {{ request('grade') == '10' ? 'selected' : '' }}>Khối 10</option>
                    <option value="11" {{ request('grade') == '11' ? 'selected' : '' }}>Khối 11</option>
                    <option value="12" {{ request('grade') == '12' ? 'selected' : '' }}>Khối 12</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>

            <!-- Select Loại đề -->
            <div class="w-full md:w-1/4 relative">
                <select name="type" class="w-full appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg pl-4 pr-10 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition-all cursor-pointer">
                    <option value="">Tất cả loại đề</option>
                    <option value="Giữa kỳ I" {{ request('type') == 'Giữa kỳ I' ? 'selected' : '' }}>Giữa kỳ I</option>
                    <option value="Cuối kỳ I" {{ request('type') == 'Cuối kỳ I' ? 'selected' : '' }}>Cuối kỳ I</option>
                    <option value="Giữa kỳ II" {{ request('type') == 'Giữa kỳ II' ? 'selected' : '' }}>Giữa kỳ II</option>
                    <option value="Cuối kỳ II" {{ request('type') == 'Cuối kỳ II' ? 'selected' : '' }}>Cuối kỳ II</option>
                    <option value="Thi thử THPT QG" {{ request('type') == 'Thi thử THPT QG' ? 'selected' : '' }}>Thi thử THPT QG</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>

            <!-- Cụm Nút bấm -->
            <div class="w-full md:w-auto flex gap-2 shrink-0">
                <button type="submit" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Lọc
                </button>
                <a href="{{ route('teacher.exams.index') }}" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-4 py-2.5 rounded-lg text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-gray-200 whitespace-nowrap shadow-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Xóa lọc
                </a>
            </div>

        </form>
    </div>
    <!-- Bảng danh sách -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50/75 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên đề</th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Khối</th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại đề</th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($exams as $exam)
                    <tr class="hover:bg-gray-50 transition-colors group">

                        <!-- Cột Tên đề -->
                        <td class="px-4 py-4" style="min-width: 13rem;">
                            <div class="font-medium text-gray-900">{{ $exam->title }}</div>
                        </td>

                        <!-- Cột Khối -->
                        <td class="px-4 py-4 whitespace-nowrap text-gray-600 text-sm">
                            Khối {{ $exam->grade }}
                        </td>

                        <!-- Cột Loại đề -->
                        <td class="px-4 py-4 whitespace-nowrap text-gray-600 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 text-xs font-medium border border-blue-100">
                                {{ $exam->type }}
                            </span>
                        </td>

                        <!-- Cột Trạng thái -->
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if(strtolower($exam->status) == 'published' || strtolower($exam->status) == 'đã xuất bản')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Đã xuất bản
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                Nháp
                            </span>
                            @endif
                        </td>

                        <!-- Cột Ngày tạo -->
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $exam->created_at->format('d/m/Y') }}
                        </td>

                        <!-- Cột Hành động -->
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <!-- Nút Tiếp tục cấu hình (chỉ đề nháp) -->
                                @if(strtolower($exam->status) === 'draft')
                                <a href="{{ $exam->pdf_path ? route('teacher.exams.config-answers', $exam->id) : route('teacher.exams.upload-pdf', $exam->id) }}"
                                    title="{{ $exam->pdf_path ? 'Tiếp tục cấu hình đáp án' : 'Tải file PDF đề thi' }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-amber-700 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap">
                                    {{ $exam->pdf_path ? 'Tiếp tục cấu hình' : 'Tải PDF' }}
                                </a>
                                @endif

                                <!-- Nút Sửa -->
                                <a href="{{ route('teacher.exams.edit', $exam->id) }}" title="Sửa" class="p-2 text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>

                                <!-- Nút Xem thống kê -->
                                <a href="{{ route('teacher.exams.statistics', $exam->id) }}" title="Xem thống kê" class="p-2 text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </a>

                                <!-- Nút Xóa -->
                                <form action="{{ route('teacher.exams.destroy', $exam->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đề thi này? Chỉ xóa được đề chưa có học sinh nào làm bài; thao tác này không thể hoàn tác.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Xóa" class="p-2 text-rose-600 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                    @endforeach

                    @if($exams->isEmpty())
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 bg-gray-50/50">
                            Không tìm thấy đề thi nào phù hợp với bộ lọc.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $exams->appends(request()->query())->links() }}</div>
</div>
@endsection