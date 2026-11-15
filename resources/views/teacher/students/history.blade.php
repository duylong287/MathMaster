@extends('layouts.teacher')

@section('title', 'Lịch sử làm bài - ' . $student->name)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Lịch sử làm bài: <span class="text-blue-600">{{ $student->name }}</span></h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $student->email }}@if($student->schoolClass) · Lớp {{ $student->schoolClass->name }}@endif
            </p>
        </div>
        <a href="{{ url()->previous() }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
            &larr; Quay lại
        </a>
    </div>

    <!-- Bảng lịch sử -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 text-sm">
                        <th class="px-6 py-4 font-semibold w-16 text-center">STT</th>
                        <th class="px-6 py-4 font-semibold">Tên đề thi</th>
                        <th class="px-6 py-4 font-semibold text-center">Điểm</th>
                        <th class="px-6 py-4 font-semibold text-right">Thời gian nộp bài</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($attempts as $index => $attempt)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 text-center font-medium text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $attempt->exam->title ?? 'Đề thi đã bị xóa' }}
                            </td>
                            {{-- Cùng kiểu hiển thị điểm với "Bảng điểm các lần làm" ở trang Phân tích năng lực --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block min-w-[3rem] text-center px-3 py-1 rounded-full bg-indigo-50 text-gray-800 font-bold">
                                    {{ rtrim(rtrim(number_format((float) $attempt->total_score, 2), '0'), '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-500">
                                {{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('d/m/Y H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">
                                Học sinh này chưa hoàn thành bài thi nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection