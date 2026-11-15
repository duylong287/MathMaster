@extends('layouts.student')

@section('title', 'Trang chủ')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Lời chào -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-800 mb-1">Xin chào, {{ Auth::user()->name }}!</h2>
        <p class="text-gray-600">
            @if(!empty($class))
                Lớp {{ $class->name }} &middot; Chúc bạn có một ngày học tập thật hiệu quả.
            @else
                Chúc bạn có một ngày học tập thật hiệu quả.
            @endif
        </p>
    </div>

    @if(empty($class))
        <!-- Chưa xếp lớp -->
        <div class="bg-white rounded-xl p-10 text-center shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-700 mb-2">Tài khoản của bạn chưa được xếp lớp</h3>
            <p class="text-gray-500 max-w-md mx-auto">Khi được xếp lớp, các đề thi giáo viên mở cho lớp sẽ hiện ở đây. Hãy liên hệ giáo viên hoặc quản trị viên để được xếp lớp.</p>
        </div>
    @else

        <!-- 1. Bài đang làm dở -->
        @if($inProgress->count() > 0)
        <div class="space-y-3">
            @foreach($inProgress as $item)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 mb-1">Bài đang làm dở</p>
                        <h3 class="text-lg font-bold text-gray-800 leading-snug">{{ $item->exam->title }}</h3>
                        <p class="text-sm text-amber-800 mt-1">
                            @if($item->remaining_minutes > 0)
                                Còn khoảng {{ $item->remaining_minutes }} phút. Đồng hồ vẫn chạy kể cả khi bạn rời trang.
                            @else
                                Đã hết giờ. Bấm tiếp tục để xem kết quả của lượt này.
                            @endif
                        </p>
                    </div>
                    <form action="{{ route('student.exams.start', $item->exam->id) }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition-colors">
                            Tiếp tục làm bài
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
        @endif

        <!-- 3. Đề mới chưa làm -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Đề thi mới chưa làm</h3>
                @if($newExamsTotal > 0)
                    <a href="{{ route('student.exams.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">Xem tất cả đề thi</a>
                @endif
            </div>

            @if($newExams->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($newExams as $exam)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                            <div class="p-5 flex-1">
                                <span class="inline-block px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold mb-3">{{ $exam->type }}</span>
                                <h4 class="font-bold text-gray-800 leading-snug mb-4 line-clamp-2" title="{{ $exam->title }}">{{ $exam->title }}</h4>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Thời gian làm bài</span>
                                    <span class="font-semibold text-gray-800">{{ $exam->duration_minutes }} phút</span>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50/60 border-t border-gray-100">
                                <form action="{{ route('student.exams.start', $exam->id) }}" method="POST"
                                      onsubmit="return confirm('Bắt đầu làm bài? Đồng hồ ({{ (int) $exam->duration_minutes }} phút) chạy ngay khi bạn đồng ý.');">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                                        Bắt đầu làm bài
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($newExamsTotal > $newExams->count())
                    <p class="text-sm text-gray-500">Còn {{ $newExamsTotal - $newExams->count() }} đề khác chưa làm.</p>
                @endif
            @else
                <div class="bg-white rounded-xl p-10 text-center shadow-sm border border-gray-100">
                    <h4 class="text-lg font-bold text-gray-700 mb-2">Bạn không còn đề nào chưa làm</h4>
                    <p class="text-gray-500 mb-5">Đề mới sẽ xuất hiện tại đây khi giáo viên công bố. Bạn cũng có thể làm lại các đề cũ để luyện thêm.</p>
                    <a href="{{ route('student.exams.index') }}" class="inline-block px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors">Xem danh sách đề thi</a>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection