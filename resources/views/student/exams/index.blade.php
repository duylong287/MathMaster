@extends('layouts.student')

@section('title', 'Danh sách Đề thi')

@section('content')
@php
    $hasFilter = request()->filled('type');
    // Số điểm hiển thị gọn: 5,25 thay vì 5.25 / 5.00
    $fmtScore = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.') ?: '0';
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Tiêu đề và bộ lọc -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Đề thi{{ !empty($grade) ? ' khối ' . $grade : '' }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                @if(!empty($class))
                    Các đề thi đã được giáo viên mở cho lớp {{ $class->name }}.
                @else
                    Các đề thi giáo viên mở cho lớp của bạn sẽ hiện ở đây.
                @endif
            </p>
        </div>

        @if(!empty($class))
        <form method="GET" action="{{ route('student.exams.index') }}"
              class="flex items-center gap-3 w-full md:w-auto bg-white p-2 rounded-lg shadow-sm border border-gray-100">
            <select name="type"
                    class="w-full md:w-56 border border-gray-300 text-gray-700 rounded-md text-sm px-3 py-1.5 outline-none focus:ring-1 focus:ring-indigo-500"
                    onchange="this.form.submit()">
                <option value="">-- Tất cả loại đề --</option>
                <option value="Giữa kỳ I" {{ request('type') == 'Giữa kỳ I' ? 'selected' : '' }}>Giữa kỳ I</option>
                <option value="Cuối kỳ I" {{ request('type') == 'Cuối kỳ I' ? 'selected' : '' }}>Cuối kỳ I</option>
                <option value="Giữa kỳ II" {{ request('type') == 'Giữa kỳ II' ? 'selected' : '' }}>Giữa kỳ II</option>
                <option value="Cuối kỳ II" {{ request('type') == 'Cuối kỳ II' ? 'selected' : '' }}>Cuối kỳ II</option>
                @if(($grade ?? null) == 12)
                    <option value="Thi thử THPT QG" {{ request('type') == 'Thi thử THPT QG' ? 'selected' : '' }}>Thi thử THPT QG</option>
                @endif
            </select>

            @if($hasFilter)
                <a href="{{ route('student.exams.index') }}" class="text-sm text-red-500 hover:underline whitespace-nowrap pr-2">Xóa bộ lọc</a>
            @endif
        </form>
        @endif
    </div>

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm text-sm">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm text-sm">{{ session('success') }}</div>
    @endif

    @if($exams->count() > 0)
        <!-- Lưới đề thi -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($exams as $exam)
                @php
                    $examAttempts  = $attempts->get($exam->id, collect());
                    $submitted     = $examAttempts->where('status', 'submitted');
                    $timesTaken    = $submitted->count();
                    $inProgress    = $examAttempts->where('status', 'in_progress')->first();
                    $latestAttempt = $submitted->sortByDesc('submitted_at')->first();
                @endphp

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow flex flex-col h-full">

                    <div class="p-5 flex-1 flex flex-col">
                        <!-- Loại đề -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-block px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">{{ $exam->type }}</span>
                            @if($inProgress)
                                <span class="inline-block px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Đang làm dở</span>
                            @endif
                        </div>

                        <!-- Tên đề -->
                        <h3 class="font-bold text-lg text-gray-800 leading-snug mb-4 line-clamp-2" title="{{ $exam->title }}">{{ $exam->title }}</h3>

                        <!-- Thông tin -->
                        <div class="space-y-2 mt-auto text-sm text-gray-600">
                            <div class="flex justify-between">
                                <span>Thời gian làm bài</span>
                                <span class="font-semibold text-gray-800">{{ $exam->duration_minutes }} phút</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Số lượt đã nộp</span>
                                <span class="font-semibold text-gray-800">{{ $timesTaken }}</span>
                            </div>
                        </div>

                        <!-- Điểm lần nộp gần nhất -->
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100 text-sm">
                            <span class="text-gray-500">Điểm lần gần nhất</span>
                            @if($latestAttempt)
                                <span class="inline-block min-w-[3rem] text-center px-3 py-1 rounded-full bg-indigo-50 text-gray-800 font-bold">{{ $fmtScore($latestAttempt->total_score) }}</span>
                            @else
                                <span class="text-gray-400">Chưa làm</span>
                            @endif
                        </div>
                    </div>

                    <!-- Nút theo tiến độ -->
                    <div class="p-4 bg-gray-50/60 border-t border-gray-100">
                        @if($inProgress)
                            <form action="{{ route('student.exams.start', $exam->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition-colors">
                                    Tiếp tục làm bài
                                </button>
                            </form>
                        @elseif($timesTaken > 0)
                            <form action="{{ route('student.exams.start', $exam->id) }}" method="POST"
                                  onsubmit="return confirm('Bắt đầu một lượt làm mới? Đồng hồ ({{ (int) $exam->duration_minutes }} phút) chạy ngay khi bạn đồng ý.');">
                                @csrf
                                <button type="submit" class="w-full py-2.5 bg-indigo-50 text-indigo-700 border border-indigo-200 text-sm font-semibold rounded-lg hover:bg-indigo-100 transition-colors">
                                    Làm lại bài này
                                </button>
                            </form>
                        @else
                            <form action="{{ route('student.exams.start', $exam->id) }}" method="POST"
                                  onsubmit="return confirm('Bắt đầu làm bài? Đồng hồ ({{ (int) $exam->duration_minutes }} phút) chạy ngay khi bạn đồng ý.');">
                                @csrf
                                <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                                    Bắt đầu làm bài
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($exams->hasPages())
            <div>{{ $exams->links() }}</div>
        @endif

    @else
        <!-- Không có đề -->
        <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-gray-100">
            @if(empty($class))
                <h3 class="text-xl font-bold text-gray-700 mb-2">Tài khoản của bạn chưa được xếp lớp</h3>
                <p class="text-gray-500 max-w-md mx-auto">Bạn cần được xếp vào một lớp thì mới thấy đề thi. Hãy liên hệ giáo viên hoặc quản trị viên để được xếp lớp.</p>
            @elseif($hasFilter)
                <h3 class="text-xl font-bold text-gray-700 mb-2">Không có đề nào thuộc loại này</h3>
                <p class="text-gray-500 mb-6">Hãy chọn loại đề khác hoặc xem tất cả đề.</p>
                <a href="{{ route('student.exams.index') }}"
                   class="inline-block px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">Xóa bộ lọc</a>
            @else
                <h3 class="text-xl font-bold text-gray-700 mb-2">Chưa có đề thi nào dành cho lớp của bạn</h3>
                <p class="text-gray-500 max-w-md mx-auto">Đề mới sẽ xuất hiện tại đây khi giáo viên công bố. Bạn quay lại kiểm tra sau nhé.</p>
            @endif
        </div>
    @endif
</div>
@endsection