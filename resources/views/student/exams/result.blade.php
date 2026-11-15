@extends('layouts.student')

@section('title', 'Kết quả thi: ' . $exam->title)

@section('content')
@php
    $grader = app(\App\Services\GradingService::class);

    // Điểm tối đa của một câu theo phần (khớp GradingService)
    $maxOf = ['I' => 0.25, 'II' => 1.0, 'III' => 0.5];
    $partName = ['I' => 'Phần I', 'II' => 'Phần II', 'III' => 'Phần III'];
    $partDesc = ['I' => 'Trắc nghiệm nhiều lựa chọn', 'II' => 'Đúng / Sai', 'III' => 'Trả lời ngắn'];
    $partUnit = ['I' => 'câu đúng', 'II' => 'câu đúng cả 4 ý', 'III' => 'câu đúng'];

    // 5,25 thay vì 5.2500; 7 thay vì 7.00
    $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.') ?: '0';

    $groups = $questions->groupBy(fn ($q) => strtoupper(trim((string) $q->part)));

    $parts = [];
    $totalMax = 0.0;
    foreach (['I', 'II', 'III'] as $p) {
        $qs = $groups->get($p, collect());
        $max = $qs->count() * $maxOf[$p];
        $got = $qs->sum(fn ($q) => (float) optional($answers->get($q->id))->score);
        $full = $qs->filter(fn ($q) => (float) optional($answers->get($q->id))->score >= $maxOf[$p] - 1e-9)->count();
        $parts[$p] = ['qs' => $qs, 'count' => $qs->count(), 'max' => $max, 'got' => $got, 'full' => $full];
        $totalMax += $max;
    }

    $total = (float) $attempt->total_score;
    $pct = $totalMax > 0 ? ($total / $totalMax) * 100 : 0;

    $answeredCount = $answers->filter(fn ($a) => $a->answer !== null && trim((string) $a->answer) !== '')->count();

    if ($answeredCount === 0) {
        $tone = 'idle';
        $msg = 'Lượt làm này không có câu trả lời nào.';
    } elseif ($pct >= 80) {
        $tone = 'great';
        $msg = 'Bạn đạt ' . round($pct) . '% số điểm tối đa. Làm bài rất tốt!';
    } elseif ($pct >= 50) {
        $tone = 'ok';
        $msg = 'Bạn đạt ' . round($pct) . '% số điểm tối đa. Xem lại các câu chưa đạt điểm để cải thiện thêm.';
    } else {
        $tone = 'low';
        $msg = 'Bạn đạt ' . round($pct) . '% số điểm tối đa. Hãy xem lại các câu bên dưới để rút kinh nghiệm cho lần sau.';
    }

    $submittedAt = $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at) : null;
    $durationText = null;
    if ($attempt->started_at && $submittedAt) {
        $secs = (int) round(abs($submittedAt->diffInSeconds(\Carbon\Carbon::parse($attempt->started_at))));
        $durationText = intdiv($secs, 60) . ' phút' . ($secs % 60 ? ' ' . ($secs % 60) . ' giây' : '');
    }
@endphp

<style>
    /* Trang tự chứa CSS để không phụ thuộc việc biên dịch lại Tailwind */
    .rs { max-width: 1024px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px; }
    .rs-flash { padding: 12px 16px; border-radius: 10px; font-size: 14px; font-weight: 500; }
    .rs-flash.err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
    .rs-flash.ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }

    .rs-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }

    /* Khối điểm tổng */
    .rs-hero { padding: 28px 32px; display: flex; gap: 28px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .rs-hero-l { display: flex; gap: 20px; align-items: center; min-width: 0; }
    .rs-ico { flex: none; width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; }
    .rs-ico svg { width: 30px; height: 30px; }
    .rs-ico.great { background: #dcfce7; color: #16a34a; }
    .rs-ico.ok { background: #dbeafe; color: #2563eb; }
    .rs-ico.low { background: #fef3c7; color: #d97706; }
    .rs-ico.idle { background: #f1f5f9; color: #64748b; }
    .rs-eyebrow { margin: 0 0 2px; font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #64748b; }
    .rs-title { margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #0f172a; line-height: 1.3; }
    .rs-msg { margin: 0; font-size: 14px; color: #475569; line-height: 1.5; max-width: 460px; }
    .rs-meta { margin: 10px 0 0; display: flex; flex-wrap: wrap; gap: 6px 18px; font-size: 13px; color: #64748b; }
    .rs-meta b { color: #334155; font-weight: 600; }

    .rs-score { flex: none; min-width: 190px; text-align: center; padding: 16px 24px; border-radius: 14px; background: #eff6ff; border: 1px solid #bfdbfe; }
    .rs-score.great { background: #f0fdf4; border-color: #bbf7d0; }
    .rs-score.low { background: #fffbeb; border-color: #fde68a; }
    .rs-score.idle { background: #f8fafc; border-color: #e2e8f0; }
    .rs-score small { display: block; margin-bottom: 4px; font-size: 12px; font-weight: 700; letter-spacing: .06em; color: #64748b; }
    .rs-score .n { font-size: 44px; font-weight: 800; line-height: 1; color: #0f172a; }
    .rs-score .m { font-size: 20px; font-weight: 600; color: #94a3b8; }

    .rs-acts { padding: 16px 32px; border-top: 1px solid #f1f5f9; background: #f8fafc; border-radius: 0 0 16px 16px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .rs-btn { display: inline-flex; align-items: center; padding: 9px 16px; border-radius: 10px; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background .15s; border: 1px solid #cbd5e1; background: #fff; color: #334155; }
    .rs-btn:hover { background: #f1f5f9; }
    .rs-btn.pri { background: #2563eb; border-color: #2563eb; color: #fff; }
    .rs-btn.pri:hover { background: #1d4ed8; }
    .rs-acts form { margin: 0; margin-left: auto; }

    /* Điểm từng phần */
    .rs-parts { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .rs-part { padding: 18px 20px; }
    .rs-part h4 { margin: 0; font-size: 14px; font-weight: 700; color: #0f172a; }
    .rs-part p.d { margin: 2px 0 12px; font-size: 12.5px; color: #64748b; }
    .rs-part .v { font-size: 24px; font-weight: 800; color: #0f172a; }
    .rs-part .v span { font-size: 14px; font-weight: 600; color: #94a3b8; }
    .rs-bar { height: 6px; margin: 10px 0 8px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
    .rs-bar i { display: block; height: 100%; background: #2563eb; border-radius: 99px; }
    .rs-part .c { font-size: 12.5px; color: #64748b; }

    /* Bảng chi tiết */
    .rs-head { padding: 16px 24px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; }
    .rs-head h3 { margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; }
    .rs-filter { display: inline-flex; align-items: center; gap: 8px; font-size: 13.5px; color: #475569; cursor: pointer; user-select: none; }
    .rs-filter input { width: 16px; height: 16px; accent-color: #2563eb; cursor: pointer; }
    .rs-wrap { overflow-x: auto; }
    table.rs-t { width: 100%; border-collapse: collapse; font-size: 14px; }
    .rs-t th { padding: 10px 16px; text-align: left; font-size: 12px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
    .rs-t td { padding: 11px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: top; color: #334155; }
    .rs-t .gr td { padding: 9px 16px; background: #f1f5f9; font-size: 13px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #e2e8f0; }
    .rs-t .gr td span { font-weight: 600; color: #64748b; margin-left: 8px; }
    .rs-t .gr td.sc { text-align: right; white-space: nowrap; }
    .rs-t td.q { font-weight: 600; white-space: nowrap; }
    .rs-t td.sc { text-align: right; white-space: nowrap; font-weight: 700; font-variant-numeric: tabular-nums; }
    .rs-t td.sc small { font-weight: 500; color: #94a3b8; }
    .rs-t tr.full td.sc { color: #15803d; }
    .rs-t tr.part td.sc { color: #b45309; }
    .rs-t tr.wrong td.sc, .rs-t tr.blank td.sc { color: #dc2626; }
    .rs-t tr.full { background: rgba(240, 253, 244, .45); }
    .rs-t tr.part { background: rgba(255, 251, 235, .55); }
    .rs-t tr.wrong { background: rgba(254, 242, 242, .45); }
    .rs-only-miss .rs-t tr.full { display: none; }

    .rs-chip { display: inline-block; padding: 2px 10px; border-radius: 99px; font-size: 12px; font-weight: 600; white-space: nowrap; }
    .rs-chip.full { background: #dcfce7; color: #15803d; }
    .rs-chip.part { background: #fef3c7; color: #b45309; }
    .rs-chip.wrong { background: #fee2e2; color: #b91c1c; }
    .rs-chip.blank { background: #f1f5f9; color: #64748b; }

    .rs-sub { display: flex; gap: 6px; align-items: baseline; line-height: 1.7; }
    .rs-sub b { min-width: 18px; color: #0f172a; }
    .rs-sub .ok { color: #16a34a; font-weight: 700; }
    .rs-sub .no { color: #dc2626; font-weight: 700; }
    .rs-none { color: #94a3b8; font-style: italic; }

    @media (max-width: 900px) {
        .rs-hero { padding: 20px; }
        .rs-acts { padding: 14px 20px; }
        .rs-acts form { margin-left: 0; }
        .rs-parts { grid-template-columns: 1fr; }
    }
</style>

<div class="rs">

    @if(session('error'))
        <div class="rs-flash err" role="alert">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="rs-flash ok" role="status">{{ session('success') }}</div>
    @endif

    <!-- 1. KHỐI ĐIỂM TỔNG -->
    <section class="rs-card">
        <div class="rs-hero">
            <div class="rs-hero-l">
                <div class="rs-ico {{ $tone }}">
                    @if($tone === 'great' || $tone === 'ok')
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif($tone === 'low')
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    @else
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>
                <div style="min-width:0">
                    <p class="rs-eyebrow">Kết quả bài làm</p>
                    <h2 class="rs-title">{{ $exam->title }}</h2>
                    <p class="rs-msg">{{ $msg }}</p>
                    <p class="rs-meta">
                        @if($submittedAt)<span>Nộp lúc <b>{{ $submittedAt->format('H:i d/m/Y') }}</b></span>@endif
                        @if($durationText)<span>Thời gian làm <b>{{ $durationText }}</b></span>@endif
                        <span>Đã trả lời <b>{{ $answeredCount }}/{{ $questions->count() }}</b> câu</span>
                    </p>
                </div>
            </div>

            <div class="rs-score {{ $tone }}">
                <small>TỔNG ĐIỂM</small>
                <span class="n">{{ $fmt($total) }}</span> <span class="m">/ {{ $fmt($totalMax) }}</span>
            </div>
        </div>

        <div class="rs-acts">
            <a href="{{ route('student.exams.index') }}" class="rs-btn">Về danh sách đề thi</a>
            <a href="{{ route('student.history') }}" class="rs-btn">Lịch sử làm bài</a>
            <a href="{{ route('student.analytics') }}" class="rs-btn">Phân tích năng lực</a>

            <form action="{{ route('student.exams.start', $exam->id) }}" method="POST"
                  onsubmit="return confirm('Bắt đầu một lượt làm mới? Đồng hồ ({{ (int) $exam->duration_minutes }} phút) chạy ngay khi bạn đồng ý.');">
                @csrf
                <button type="submit" class="rs-btn pri">Làm lại đề này</button>
            </form>
        </div>
    </section>

    <!-- 2. ĐIỂM TỪNG PHẦN -->
    <section class="rs-parts">
        @foreach($parts as $p => $info)
            @if($info['count'] > 0)
                <div class="rs-card rs-part">
                    <h4>{{ $partName[$p] }}</h4>
                    <p class="d">{{ $partDesc[$p] }}</p>
                    <div class="v">{{ $fmt($info['got']) }} <span>/ {{ $fmt($info['max']) }}</span></div>
                    <div class="rs-bar"><i style="width: {{ $info['max'] > 0 ? min(100, round($info['got'] / $info['max'] * 100)) : 0 }}%"></i></div>
                    <div class="c">{{ $info['full'] }}/{{ $info['count'] }} {{ $partUnit[$p] }}</div>
                </div>
            @endif
        @endforeach
    </section>

    <!-- 3. CHI TIẾT TỪNG CÂU -->
    <section class="rs-card" id="detail-card">
        <div class="rs-head">
            <h3>Chi tiết bài làm</h3>
            <label class="rs-filter">
                <input type="checkbox" id="only-miss">
                Chỉ xem câu chưa đạt điểm tối đa
            </label>
        </div>

        <div class="rs-wrap">
            <table class="rs-t">
                <thead>
                    <tr>
                        <th>Câu</th>
                        <th>Đáp án chuẩn</th>
                        <th>Bài làm của bạn</th>
                        <th>Kết quả</th>
                        <th style="text-align:right">Điểm</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($parts as $p => $info)
                    @if($info['count'] > 0)
                        <tr class="gr">
                            <td colspan="4">{{ $partName[$p] }}<span>{{ $partDesc[$p] }}</span></td>
                            <td class="sc">{{ $fmt($info['got']) }}/{{ $fmt($info['max']) }}</td>
                        </tr>

                        @foreach($info['qs'] as $q)
                            @php
                                $ans = $answers->get($q->id);
                                $given = ($ans && $ans->answer !== null && trim((string) $ans->answer) !== '') ? (string) $ans->answer : null;
                                $score = $ans ? (float) $ans->score : 0.0;
                                $max = $maxOf[$p];

                                if ($score >= $max - 1e-9) { $state = 'full'; $label = 'Đúng'; }
                                elseif ($score > 0) { $state = 'part'; $label = 'Đúng một phần'; }
                                elseif ($given === null) { $state = 'blank'; $label = 'Chưa làm'; }
                                else { $state = 'wrong'; $label = 'Sai'; }

                                if ($p === 'II') {
                                    $corrArr = $grader->parseTrueFalse((string) $q->correct_answer);
                                    $givenArr = $given !== null ? $grader->parseTrueFalse($given) : [];
                                }
                            @endphp

                            <tr class="{{ $state }}">
                                <td class="q">Câu {{ $q->number }}</td>

                                @if($p === 'II')
                                    <td>
                                        @foreach(['a', 'b', 'c', 'd'] as $idx => $sub)
                                            <div class="rs-sub">
                                                <b>{{ $sub }})</b>
                                                <span>{{ array_key_exists($idx, $corrArr) && $corrArr[$idx] !== null ? ($corrArr[$idx] ? 'Đúng' : 'Sai') : '—' }}</span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($given === null)
                                            <span class="rs-none">Chưa làm</span>
                                        @else
                                            @foreach(['a', 'b', 'c', 'd'] as $idx => $sub)
                                                @php
                                                    $g = $givenArr[$idx] ?? null;
                                                    $c = $corrArr[$idx] ?? null;
                                                @endphp
                                                <div class="rs-sub">
                                                    <b>{{ $sub }})</b>
                                                    @if($g === null)
                                                        <span class="rs-none">bỏ trống</span>
                                                    @else
                                                        <span>{{ $g ? 'Đúng' : 'Sai' }}</span>
                                                        @if($c !== null)
                                                            @if($g === $c)<span class="ok" title="Đúng">&#10003;</span>@else<span class="no" title="Sai">&#10007;</span>@endif
                                                        @endif
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                @else
                                    <td>{{ $q->correct_answer }}</td>
                                    <td>
                                        @if($given === null)
                                            <span class="rs-none">Chưa làm</span>
                                        @else
                                            {{ $given }}
                                        @endif
                                    </td>
                                @endif

                                <td><span class="rs-chip {{ $state }}">{{ $label }}</span></td>
                                <td class="sc">{{ $fmt($score) }} <small>/ {{ $fmt($max) }}</small></td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
    document.getElementById('only-miss').addEventListener('change', function () {
        document.getElementById('detail-card').classList.toggle('rs-only-miss', this.checked);
    });
</script>
@endsection