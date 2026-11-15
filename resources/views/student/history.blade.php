@extends('layouts.student')

@section('title', 'Lịch sử làm bài')

@section('content')
@php
    $hasFilter = request()->filled('search') || request()->filled('type');
    $inProgressCount = $attempts->getCollection()->where('status', 'in_progress')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Tiêu đề -->
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Lịch sử làm bài</h2>
        <p class="text-sm text-gray-500 mt-1">
            Xem lại kết quả và tiếp tục các bài thi đang làm dở.
            @if($attempts->total() > 0)
                Tổng cộng {{ $attempts->total() }} lượt làm{{ $hasFilter ? ' theo bộ lọc hiện tại' : '' }}.
            @endif
        </p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm text-sm">{{ session('error') }}</div>
    @endif

    <!-- Bộ lọc -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <form action="{{ route('student.history') }}" method="GET" class="flex flex-wrap gap-3 items-center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên đề..."
                   class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-64 text-sm outline-none focus:ring-1 focus:ring-indigo-500">

            <select name="type"
                    class="border border-gray-300 text-gray-700 rounded-lg px-4 py-2 text-sm outline-none focus:ring-1 focus:ring-indigo-500 min-w-[150px]"
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

            <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors">Lọc</button>

            @if($hasFilter)
                <a href="{{ route('student.history') }}" class="text-sm text-red-500 hover:underline">Xóa bộ lọc</a>
            @endif
        </form>
    </div>

    <!-- Danh sách lịch sử -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50">
                    <tr class="text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold border-b">Đề thi</th>
                        <th class="p-4 font-semibold border-b text-center">Trạng thái</th>
                        <th class="p-4 font-semibold border-b text-center">Thời gian làm</th>
                        <th class="p-4 font-semibold border-b text-center">Ngày nộp</th>
                        <th class="p-4 font-semibold border-b text-center">Điểm</th>
                        <th class="p-4 font-semibold border-b text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse($attempts as $attempt)
                        @php
                            $isDone = $attempt->status === 'submitted';

                            // Thời gian làm = giờ nộp - giờ bắt đầu
                            $timeTaken = '—';
                            if ($isDone && $attempt->submitted_at && $attempt->started_at) {
                                $secs = (int) round(abs(
                                    \Carbon\Carbon::parse($attempt->submitted_at)->diffInSeconds(\Carbon\Carbon::parse($attempt->started_at))
                                ));
                                $timeTaken = $secs >= 60
                                    ? intdiv($secs, 60) . ' phút ' . ($secs % 60) . ' giây'
                                    : $secs . ' giây';
                            }

                            $scoreText = $attempt->total_score !== null
                                ? rtrim(rtrim(number_format((float) $attempt->total_score, 2), '0'), '.')
                                : '0';
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ $isDone ? '' : 'bg-amber-50/40' }}">
                            <td class="p-4">
                                <div class="font-medium text-gray-800">{{ $attempt->exam->title ?? 'Đề đã bị xóa' }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $attempt->exam->type ?? '' }}</div>
                            </td>

                            <td class="p-4 text-center">
                                @if($isDone)
                                    <span class="inline-block px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Đã nộp</span>
                                @else
                                    <span class="inline-block px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">Đang làm dở</span>
                                @endif
                            </td>

                            <td class="p-4 text-center text-gray-600">{{ $timeTaken }}</td>

                            <td class="p-4 text-center text-gray-600">
                                {{ $isDone && $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('d/m/Y H:i') : '—' }}
                            </td>

                            <td class="p-4 text-right">
                                @if($isDone)
                                    <span class="inline-block min-w-[3rem] text-center px-3 py-1 rounded-full bg-indigo-50 text-gray-800 font-bold">{{ $scoreText }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="p-4 text-center">
                                @if($isDone)
                                    <a href="{{ route('student.exams.result', ['exam' => $attempt->exam_id, 'attempt' => $attempt->id]) }}"
                                       class="inline-block px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100 transition-colors whitespace-nowrap">
                                        Xem kết quả
                                    </a>
                                @else
                                    <form action="{{ route('student.exams.start', $attempt->exam_id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="inline-block px-3 py-1.5 rounded-lg bg-amber-100 text-amber-800 text-xs font-semibold hover:bg-amber-200 transition-colors whitespace-nowrap">
                                            Tiếp tục
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-gray-500">
                                @if($hasFilter)
                                    Không có lượt làm nào phù hợp bộ lọc.
                                @else
                                    Bạn chưa có lịch sử làm bài nào.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attempts->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $attempts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection