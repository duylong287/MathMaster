@extends('layouts.teacher')

@section('title', 'Thống kê đề thi')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Tiêu đề trang -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Thống kê: {{ $exam->title ?? 'Đề thi' }}</h2>
            <p class="text-sm text-gray-500 mt-1">Phổ điểm và bảng điểm chỉ tính các bài đã nộp; học sinh làm nhiều lần thì mỗi lần là một dòng.</p>
        </div>
        <a href="{{ route('teacher.exams.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
            &larr; Quay lại danh sách
        </a>
    </div>

    <!-- Tiêu đề mục -->
    <h3 class="font-bold text-gray-800 uppercase tracking-wide text-sm mt-8 mb-4">Chỉ số thống kê cơ bản</h3>

    <!-- Lưới 5 cột Chỉ số -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- 1. Số học sinh trong lớp hiện có -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 border-t-4 border-t-indigo-600 flex flex-col justify-between">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <h4 class="text-3xl font-bold text-gray-900 mb-1">{{ $totalStudents }}</h4>
                <p class="text-sm text-gray-500 font-medium">Số học sinh trong lớp hiện có</p>
            </div>
        </div>

        <!-- 2. Tổng lượt làm -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 border-t-4 border-t-emerald-500 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <!-- Tỷ lệ học sinh đã tham gia làm đề / sĩ số -->
                <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-2 py-1 rounded-md" title="Số học sinh đã làm đề chia cho số học sinh trong lớp được giao">{{ $attemptRate }}% HS tham gia</span>
            </div>
            <div>
                <h4 class="text-3xl font-bold text-gray-900 mb-1">{{ $totalAttempts }}</h4>
                <p class="text-sm text-gray-500 font-medium">Tổng lượt làm</p>
            </div>
        </div>

        <!-- 3. Số người chưa thi và đang thi -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 border-t-4 border-t-rose-500 flex flex-col justify-between">
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <h4 class="text-3xl font-bold text-gray-900 mb-1">{{ $notFinishedCount }}</h4>
                <p class="text-sm text-gray-500 font-medium">Số người chưa thi</p>
            </div>
        </div>

        <!-- 4. Số thí sinh đạt điểm < 1 -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 border-t-4 border-t-amber-400 flex flex-col justify-between">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h4 class="text-3xl font-bold text-gray-900 mb-1">{{ $scoreUnder1 }}</h4>
                <p class="text-sm text-gray-500 font-medium">Số thí sinh đạt điểm &lt; 1</p>
            </div>
        </div>

        <!-- 5. Số thí sinh đạt điểm >= 5 -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 border-t-4 border-t-blue-600 flex flex-col justify-between">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <div>
                <h4 class="text-3xl font-bold text-gray-900 mb-1">{{ $scoreOver5 }}</h4>
                <p class="text-sm text-gray-500 font-medium">Số thí sinh đạt điểm &ge; 5</p>
            </div>
        </div>

    </div>

    <!-- Tiêu đề mục Thống kê phổ điểm -->
    <h3 class="font-bold text-gray-800 uppercase tracking-wide text-sm mt-10 mb-4">Thống kê phổ điểm</h3>

    <!-- Khối chứa Biểu đồ và Danh sách -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-10">
        
        <!-- Cột trái: Biểu đồ (Chiếm 2 phần) -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50/50 px-5 py-4 border-b border-gray-200 flex items-center">
                <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <h4 class="font-semibold text-gray-700">Biểu đồ phổ điểm</h4>
            </div>
            <div class="p-5">
                <canvas id="scoreChart" height="120"></canvas>
            </div>
        </div>

        <!-- Cột phải: Thống kê chi tiết (Chiếm 1 phần) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col">
            <div class="bg-gray-50/50 px-5 py-4 border-b border-gray-200 flex items-center">
                <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                <h4 class="font-semibold text-gray-700">Thống kê</h4>
            </div>
            
            <!-- Danh sách thanh ngang -->
            <div class="p-5 flex-grow space-y-3">
                @if(isset($scoreDistribution))
                    @foreach($scoreDistribution as $label => $count)
                        <div class="flex items-center justify-between text-sm">
                            <span class="w-12 font-medium text-gray-600">{{ $label }}</span>
                            <div class="flex-grow mx-3 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                @php 
                                    $percent = (isset($maxCount) && $maxCount > 0) ? ($count / $maxCount) * 100 : 0; 
                                @endphp
                                <div class="h-full bg-blue-700 rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="w-6 text-right font-bold text-gray-800">{{ $count }}</span>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Tổng kết cuối bảng -->
            <div class="border-t border-gray-100 p-5 grid grid-cols-2 gap-4 text-center bg-gray-50/30 rounded-b-xl">
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ rtrim(rtrim(number_format((float) ($averageScore ?? 0), 2), '0'), '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">Điểm trung bình</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $mostFrequentScore ?? '-' }}</p>
                    <p class="text-xs text-gray-500 mt-1">Mốc điểm nhiều người đạt</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Tiêu đề mục Tỷ lệ đúng sai -->
    <h3 class="font-bold text-gray-800 uppercase tracking-wide text-sm mt-10 mb-4">Thống kê tỷ lệ đúng/sai theo câu hỏi</h3>

    <!-- Bảng chi tiết -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 text-sm">
                        <th class="px-6 py-4 font-semibold w-40 text-center">Câu</th>
                        <th class="px-6 py-4 font-semibold text-center">Tổng lượt trả lời</th>
                        <th class="px-6 py-4 font-semibold text-emerald-600 text-center" title="Đạt điểm tối đa của câu (Phần II: đúng cả 4 ý)">Số lượt đúng</th>
                        <th class="px-6 py-4 font-semibold text-rose-500 text-center">Số lượt sai</th>
                        <th class="px-6 py-4 font-semibold text-center w-48">Tỷ lệ đúng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @if(isset($questionStats) && $questionStats->count() > 0)
                        @foreach($questionStats as $index => $stat)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 text-center font-medium text-gray-900">Phần {{ $stat->part }} - Câu {{ $stat->number }}</td>
                                <td class="px-6 py-4 text-center text-gray-600">{{ $stat->total_answers }}</td>
                                <td class="px-6 py-4 text-center font-bold text-emerald-600">{{ $stat->correct_answers }}</td>
                                <td class="px-6 py-4 text-center font-bold text-rose-500">{{ $stat->incorrect_answers }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-bold {{ $stat->correct_rate < 50 ? 'text-rose-500' : 'text-emerald-600' }}">{{ $stat->correct_rate }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $stat->correct_rate < 50 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $stat->correct_rate }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">Chưa có dữ liệu làm bài để thống kê.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tiêu đề mục Bảng điểm -->
    <h3 class="font-bold text-gray-800 uppercase tracking-wide text-sm mt-10 mb-4">Bảng điểm chi tiết học sinh</h3>

    <!-- Khối Bảng điểm -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 text-sm">
                        <th class="px-6 py-4 font-semibold w-16 text-center">STT</th>
                        <th class="px-6 py-4 font-semibold">Họ và tên học sinh</th>
                        <th class="px-6 py-4 font-semibold text-center">Điểm số</th>
                        <th class="px-6 py-4 font-semibold text-right">Thời gian nộp bài</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @if(isset($attempts) && $attempts->count() > 0)
                        @foreach($attempts as $index => $attempt)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 text-center font-medium text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $attempt->user->name ?? 'Học sinh ẩn danh' }}
                                    @if(isset($attempt->user->email))
                                        <span class="block text-xs text-gray-500 font-normal mt-0.5">{{ $attempt->user->email }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{-- Cùng kiểu hiển thị điểm với trang lịch sử làm bài và trang Phân tích năng lực --}}
                                    <span class="inline-block min-w-[3rem] text-center px-3 py-1 rounded-full bg-indigo-50 text-gray-800 font-bold">
                                        {{ rtrim(rtrim(number_format((float) $attempt->total_score, 2), '0'), '.') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-gray-500">
                                    {{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('d/m/Y H:i') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">Chưa có học sinh nào nộp bài.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>



<!-- Load thư viện Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('scoreChart').getContext('2d');
        
        // Kiểm tra xem có dữ liệu truyền ra không
        const scoreDistribution = @json($scoreDistribution ?? []);
        
        // Lấy danh sách keys (nhãn) và values (dữ liệu)
        const scoreLabels = Object.keys(scoreDistribution);
        const scoreData = Object.values(scoreDistribution);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: scoreLabels.length > 0 ? scoreLabels : ['< 1', '< 2', '< 3', '< 4', '< 5', '< 6', '< 7', '< 8', '< 9', '<= 10'],
                datasets: [{
                    label: 'Số bài nộp',
                    data: scoreData.length > 0 ? scoreData : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: '#2563eb', // Màu xanh dương Tailwind blue-600
                    borderRadius: 2,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (item) { return item.parsed.y + ' bài nộp'; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    },
                    x: {
                        grid: { display: false } 
                    }
                }
            }
        });
    });
</script>
@endsection