{{--
  Giao diện phân tích năng lực DÙNG CHUNG cho học sinh và giáo viên.
  Biến cần có: $filterAction (URL của form lọc), $filterGrade (khối của học sinh),
  $viewer ('student' | 'teacher'), $titleAccent (tên học sinh, chỉ giáo viên dùng)
  + các biến thống kê do controller truyền (giống nhau ở hai phía).
--}}
@php
    // Màu theo xếp loại, dùng chung cho bảng chuyên đề
    $levelStyle = [
        'Yếu'        => ['text' => 'text-red-600',    'bar' => 'bg-red-500'],
        'Trung bình' => ['text' => 'text-orange-500', 'bar' => 'bg-orange-400'],
        'Khá'        => ['text' => 'text-blue-600',   'bar' => 'bg-blue-500'],
        'Giỏi'       => ['text' => 'text-green-600',  'bar' => 'bg-green-500'],
    ];

    // Các mức không được xếp loại
    $unratedLevels = ['Chưa đủ dữ liệu', 'Chưa phân loại'];

    // Số câu tối thiểu để một chuyên đề được xếp loại (chỉ để hiển thị lời giải thích)
    $minQ = $minQuestions ?? 5;

    // Cùng một giao diện cho học sinh và giáo viên; chỉ khác tiêu đề và lời nhắn.
    $isTeacherView = ($viewer ?? 'student') === 'teacher';

    // Đường dẫn tới danh sách đề (chỉ học sinh mới có nút này)
    $examsUrl = (! $isTeacherView && \Illuminate\Support\Facades\Route::has('student.exams.index'))
        ? route('student.exams.index')
        : null;
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Tiêu đề và bộ lọc -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <h2 class="text-2xl font-bold text-gray-800">
            Phân tích năng lực
            @if(!empty($titleAccent))
                : <span class="text-blue-600">{{ $titleAccent }}</span>
            @endif
        </h2>

        @if(isset($hasAnyAttempt) && $hasAnyAttempt)
        <form action="{{ $filterAction }}" method="GET"
              class="flex gap-3 bg-white p-2 rounded-lg shadow-sm border border-gray-100">
            <select name="time"
                    class="border-gray-300 rounded-md text-sm border outline-none px-3 py-1.5 focus:ring-1 focus:ring-blue-500"
                    onchange="this.form.submit()">
                <option value="30" {{ $timeFilter == '30' ? 'selected' : '' }}>30 ngày gần nhất</option>
                <option value="90" {{ $timeFilter == '90' ? 'selected' : '' }}>90 ngày gần nhất</option>
                <option value="all" {{ $timeFilter == 'all' ? 'selected' : '' }}>Toàn bộ lịch sử</option>
            </select>
            <select name="type"
                    class="border-gray-300 rounded-md text-sm border outline-none px-3 py-1.5 focus:ring-1 focus:ring-blue-500"
                    onchange="this.form.submit()">
                <option value="">-- Tất cả loại đề --</option>
                <option value="Giữa kỳ I" {{ $typeFilter == 'Giữa kỳ I' ? 'selected' : '' }}>Giữa kỳ I</option>
                <option value="Cuối kỳ I" {{ $typeFilter == 'Cuối kỳ I' ? 'selected' : '' }}>Cuối kỳ I</option>
                <option value="Giữa kỳ II" {{ $typeFilter == 'Giữa kỳ II' ? 'selected' : '' }}>Giữa kỳ II</option>
                <option value="Cuối kỳ II" {{ $typeFilter == 'Cuối kỳ II' ? 'selected' : '' }}>Cuối kỳ II</option>
                @if(($filterGrade ?? null) == 12)
                    <option value="Thi thử THPT QG" {{ $typeFilter == 'Thi thử THPT QG' ? 'selected' : '' }}>Thi thử THPT QG</option>
                @endif
            </select>
        </form>
        @endif
    </div>

    @if(!isset($hasAnyAttempt) || !$hasAnyAttempt)
    <!-- Trường hợp 1: học sinh chưa nộp bài nào -->
    <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-gray-100">
        <svg class="w-16 h-16 mx-auto text-blue-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <h3 class="text-xl font-bold text-gray-700 mb-2">Chưa có dữ liệu phân tích</h3>
        <p class="text-gray-500 mb-6">
            @if($isTeacherView)
                Học sinh này chưa nộp bài thi nào nên hệ thống chưa có dữ liệu để đánh giá năng lực.
            @else
                Bạn chưa hoàn thành bài thi nào. Hãy làm ít nhất một đề để hệ thống đánh giá năng lực của bạn.
            @endif
        </p>
        @if($examsUrl)
            <a href="{{ $examsUrl }}"
               class="inline-block px-5 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                Đến danh sách đề thi
            </a>
        @endif
    </div>

    @elseif(!isset($hasData) || !$hasData)
    <!-- Trường hợp 2: có bài đã nộp nhưng bộ lọc hiện tại không có bài nào -->
    <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-gray-100">
        <h3 class="text-xl font-bold text-gray-700 mb-2">Không có bài làm nào phù hợp bộ lọc</h3>
        <p class="text-gray-500 mb-6">Hãy chọn khoảng thời gian dài hơn hoặc chọn tất cả loại đề.</p>
        <a href="{{ $filterAction }}"
           class="inline-block px-5 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
            Xóa bộ lọc
        </a>
    </div>

    @else
    @php
        $best      = collect($strengths ?? [])->first();
        $worst     = collect($weaknesses ?? [])->first();
        $bestName  = data_get($best, 'name');
        $worstName = data_get($worst, 'name');

        // Chuyên đề đã xếp loại: sắp từ thấp đến cao để chuyên đề yếu nhất nằm trên cùng
        $ratedTopics = collect($topicStats)->whereNotIn('level', $unratedLevels)->sortBy('percentage');
        // Chuyên đề chưa xếp loại: hiển thị riêng, không bị ẩn
        $unratedTopics = collect($topicStats)->whereIn('level', $unratedLevels);

        // Chỉ hiện cột Số câu và Điểm khi controller cung cấp dữ liệu
        $showCounts = collect($topicStats)->contains(fn ($s) => isset($s['question_count']));

        $levelStats = $levelStats ?? $radarData ?? [];

        $cognitiveConfig = [
            'Nhận biết'    => ['bg' => 'bg-green-500',  'text' => 'text-green-700',  'box' => 'bg-green-50 border-green-100'],
            'Thông hiểu'   => ['bg' => 'bg-blue-500',   'text' => 'text-blue-700',   'box' => 'bg-blue-50 border-blue-100'],
            'Vận dụng'     => ['bg' => 'bg-orange-500', 'text' => 'text-orange-700', 'box' => 'bg-orange-50 border-orange-100'],
            'Vận dụng cao' => ['bg' => 'bg-red-500',    'text' => 'text-red-700',    'box' => 'bg-red-50 border-red-100'],
        ];
    @endphp

    <!-- 1. Thẻ chỉ số tổng quan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Số đề đã làm</p>
            <h4 class="text-3xl font-bold text-gray-800">{{ $examsTakenCount }}</h4>
            <p class="text-xs text-gray-400 mt-1">Mỗi đề tính một lần, không tính lượt làm lại</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Điểm trung bình</p>
            <h4 class="text-3xl font-bold text-gray-800">{{ $averageScore }}</h4>
            <p class="text-xs text-gray-400 mt-1">Theo lượt làm gần nhất của mỗi đề</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100">
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Cần chú ý nhất</p>
            @if($worstName)
                <h4 class="text-base font-bold text-red-600 leading-snug">{{ $worstName }}</h4>
                <p class="text-xs text-gray-400 mt-1">Đạt {{ round(data_get($worst, 'percentage', 0)) }}% số điểm</p>
            @elseif($ratedTopics->isNotEmpty())
                <h4 class="text-base font-bold text-green-600 leading-snug">Không có chuyên đề yếu</h4>
            @else
                <h4 class="text-base font-bold text-gray-400 leading-snug">Chưa đủ dữ liệu</h4>
            @endif
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-green-100">
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Làm tốt nhất</p>
            @if($bestName)
                <h4 class="text-base font-bold text-green-600 leading-snug">{{ $bestName }}</h4>
                <p class="text-xs text-gray-400 mt-1">Đạt {{ round(data_get($best, 'percentage', 0)) }}% số điểm</p>
            @elseif($ratedTopics->isNotEmpty())
                <h4 class="text-base font-bold text-gray-500 leading-snug">Chưa có chuyên đề đạt mức Khá</h4>
            @else
                <h4 class="text-base font-bold text-gray-400 leading-snug">Chưa đủ dữ liệu</h4>
            @endif
        </div>
    </div>

    <!-- 2. Bảng chi tiết chuyên đề -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 shrink-0">
            <h3 class="font-bold text-lg text-gray-800">Chi tiết chuyên đề</h3>
            @isset($thresholds)
                <p class="text-xs text-gray-500 mt-1">
                    Yếu: dưới {{ $thresholds['average'] + 0 }}% &middot;
                    Trung bình: từ {{ $thresholds['average'] + 0 }}% đến dưới {{ $thresholds['good'] + 0 }}% &middot;
                    Khá: từ {{ $thresholds['good'] + 0 }}% đến dưới {{ $thresholds['excellent'] + 0 }}% &middot;
                    Giỏi: từ {{ $thresholds['excellent'] + 0 }}% trở lên
                </p>
            @endisset
        </div>

        <div class="overflow-x-auto overflow-y-auto max-h-[420px]">
            <table class="w-full text-left border-collapse relative">
                <thead class="sticky top-0 bg-gray-50 z-10">
                    <tr class="text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold border-b">Chuyên đề</th>
                        @if($showCounts)
                            <th class="p-4 font-semibold border-b text-center">Số câu</th>
                            <th class="p-4 font-semibold border-b text-center">Điểm</th>
                        @endif
                        <th class="p-4 font-semibold border-b text-center w-48">Tỷ lệ</th>
                        <th class="p-4 font-semibold border-b text-center">Xếp loại</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @foreach($ratedTopics as $stat)
                        @php $style = $levelStyle[$stat['level']] ?? ['text' => 'text-gray-500', 'bar' => 'bg-gray-400']; @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4 font-medium text-gray-800">{{ $stat['name'] }}</td>
                            @if($showCounts)
                                <td class="p-4 text-center text-gray-600">{{ $stat['question_count'] ?? '' }}</td>
                                <td class="p-4 text-center text-gray-600">
                                    @if(isset($stat['earned'], $stat['max']))
                                        {{ rtrim(rtrim(number_format($stat['earned'], 2), '0'), '.') }}/{{ rtrim(rtrim(number_format($stat['max'], 2), '0'), '.') }}
                                    @endif
                                </td>
                            @endif
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200/70 rounded-full h-2">
                                        <div class="{{ $style['bar'] }} h-2 rounded-full" style="width: {{ min(100, max(0, round($stat['percentage']))) }}%"></div>
                                    </div>
                                    <span class="w-10 text-right font-medium text-gray-700">{{ round($stat['percentage']) }}%</span>
                                </div>
                            </td>
                            <td class="p-4 text-center font-bold">
                                <span class="{{ $style['text'] }}">{{ $stat['level'] }}</span>
                            </td>
                        </tr>
                    @endforeach

                    @if($unratedTopics->isNotEmpty())
                        <tr class="bg-gray-50">
                            <td colspan="{{ $showCounts ? 5 : 3 }}" class="p-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Chưa xếp loại (chuyên đề cần tối thiểu {{ $minQ }} câu để xếp loại)
                            </td>
                        </tr>
                        @foreach($unratedTopics as $stat)
                            <tr class="text-gray-400">
                                <td class="p-4">{{ $stat['name'] }}</td>
                                @if($showCounts)
                                    <td class="p-4 text-center">{{ $stat['question_count'] ?? '' }}</td>
                                    <td class="p-4 text-center"></td>
                                @endif
                                <td class="p-4 text-center">&ndash;</td>
                                <td class="p-4 text-center text-xs">{{ $stat['level'] }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Thống kê mức độ câu hỏi -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-lg text-gray-800 mb-4">Thống kê mức độ câu hỏi</h3>

        <div class="flex flex-col-reverse gap-3">
            @foreach($levelStats as $item)
                @php
                    $name    = data_get($item, 'name');
                    $percent = round(data_get($item, 'percentage', 0));
                    $correct = data_get($item, 'correct');
                    $total   = data_get($item, 'total');
                    $config  = $cognitiveConfig[$name] ?? ['bg' => 'bg-gray-500', 'text' => 'text-gray-700', 'box' => 'bg-gray-50 border-gray-200'];
                @endphp
                <div class="p-3.5 rounded-lg border {{ $config['box'] }}">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-bold text-sm {{ $config['text'] }}">{{ $name }}</span>
                        <span class="font-bold {{ $config['text'] }}">
                            @if($total !== null && (int) $total === 0)
                                <span class="text-xs font-medium text-gray-400">Chưa có câu</span>
                            @else
                                {{ $percent }}%
                            @endif
                        </span>
                    </div>
                    <div class="w-full bg-gray-200/60 rounded-full h-2">
                        <div class="{{ $config['bg'] }} h-2 rounded-full" style="width: {{ min(100, max(0, $percent)) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3b. Bảng điểm các lần làm -->
    @php
        $rows = collect($attemptRows ?? []);
        $histUrl = null;
        if ($isTeacherView && isset($student) && \Illuminate\Support\Facades\Route::has('teacher.students.history')) {
            $histUrl = route('teacher.students.history', $student->id);
        } elseif (! $isTeacherView && \Illuminate\Support\Facades\Route::has('student.history')) {
            $histUrl = route('student.history');
        }
        $canOpenResult = ! $isTeacherView && \Illuminate\Support\Facades\Route::has('student.exams.result');
    @endphp
    @if($rows->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 shrink-0 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="font-bold text-lg text-gray-800">Bảng điểm các lần làm</h3>
            </div>
            @if($histUrl)
                <a href="{{ $histUrl }}" class="text-sm text-blue-600 hover:underline whitespace-nowrap">Xem toàn bộ lịch sử &rarr;</a>
            @endif
        </div>

        <div class="overflow-x-auto overflow-y-auto max-h-[420px]">
            <table class="w-full text-left border-collapse relative">
                <thead class="sticky top-0 bg-gray-50 z-10">
                    <tr class="text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold border-b">Đề thi</th>
                        <th class="p-4 font-semibold border-b">Loại đề</th>
                        <th class="p-4 font-semibold border-b text-center">Ngày nộp</th>
                        <th class="p-4 font-semibold border-b text-center">Thời gian làm</th>
                        <th class="p-4 font-semibold border-b text-center">Điểm</th>
                        @if($canOpenResult)
                            <th class="p-4 font-semibold border-b text-center"></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @foreach($rows as $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4 font-medium text-gray-800">{{ $row['title'] }}</td>
                            <td class="p-4 text-gray-600">{{ $row['type'] }}</td>
                            <td class="p-4 text-center text-gray-600">
                                {{ $row['submitted_at'] ? \Carbon\Carbon::parse($row['submitted_at'])->format('d/m/Y H:i') : '' }}
                            </td>
                            <td class="p-4 text-center text-gray-600">
                                {{ $row['minutes'] !== null ? $row['minutes'] . ' phút' : '' }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-block min-w-[3rem] text-center px-3 py-1 rounded-full bg-blue-50 text-gray-800 font-bold">
                                    {{ rtrim(rtrim(number_format($row['score'], 2), '0'), '.') }}
                                </span>
                            </td>
                            @if($canOpenResult)
                                <td class="p-4 text-center">
                                    <a href="{{ route('student.exams.result', [$row['exam_id'], $row['id']]) }}" class="text-blue-600 hover:underline">Xem bài</a>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- 4. Tiến bộ theo thời gian -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-lg text-gray-800 mb-1">Tiến bộ theo thời gian</h3>
        <p class="text-xs text-gray-500 mb-4">Mỗi điểm là một lượt làm bài. Di chuyển chuột vào các điểm để xem tên đề và điểm chính xác.</p>
        <div class="relative w-full" style="height: 300px;">
            <canvas id="progressChart"></canvas>
        </div>
    </div>
    @endif
</div>

@if(isset($hasData) && $hasData)
<script>
    (function () {
        function drawChart() {
            const progressLabels = @json($progressLabels);
            const progressScores = @json($progressScores);
            // Tên đề của từng lượt (tùy chọn, controller có thể bổ sung)
            const progressTitles = @json($progressTitles ?? []);

            new Chart(document.getElementById('progressChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: progressLabels,
                    datasets: [{
                        label: 'Tổng điểm',
                        data: progressScores,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0,              // không làm mượt, tránh tạo cảm giác có điểm ở giữa
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#2563eb'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { min: 0, max: 10, ticks: { stepSize: 1 } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function (items) {
                                    const i = items[0].dataIndex;
                                    return progressTitles[i] || progressLabels[i];
                                },
                                label: function (ctx) {
                                    return 'Tổng điểm: ' + ctx.parsed.y;
                                }
                            }
                        }
                    }
                }
            });
        }

        function showError() {
            const canvas = document.getElementById('progressChart');
            if (canvas) {
                canvas.parentNode.innerHTML = '<p class="text-sm text-gray-500 text-center pt-24">Không tải được biểu đồ. Hãy kiểm tra kết nối mạng rồi tải lại trang.</p>';
            }
        }

        // Thử lần lượt: file chép sẵn trong dự án (public/js/chart.umd.js), rồi CDN đã ghim phiên bản
        function load(urls, i) {
            if (typeof Chart !== 'undefined') return drawChart();
            if (i >= urls.length) return showError();
            const s = document.createElement('script');
            s.src = urls[i];
            s.onload = function () { typeof Chart !== 'undefined' ? drawChart() : load(urls, i + 1); };
            s.onerror = function () { load(urls, i + 1); };
            document.head.appendChild(s);
        }

        document.addEventListener('DOMContentLoaded', function () {
            load([@json(asset('js/chart.umd.js')), 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js'], 0);
        });
    })();
</script>
@endif