@extends('layouts.app')

@section('title', 'Tổng quan')

@section('content')
@php
    $attention = [];
    if ($studentsNoClass > 0) {
        $attention[] = [
            'text' => $studentsNoClass . ' học sinh chưa được xếp lớp',
            'hint' => 'Các em này chưa thấy đề thi nào khi đăng nhập.',
            'url' => route('admin.users.index'),
        ];
    }
    if ($classesNoTeacher > 0) {
        $attention[] = [
            'text' => $classesNoTeacher . ' lớp đang hoạt động chưa có giáo viên phụ trách',
            'hint' => 'Lớp chưa có giáo viên thì không ai giao đề cho lớp.',
            'url' => route('admin.classes.index'),
        ];
    }
    if ($lockedAccounts > 0) {
        $attention[] = [
            'text' => $lockedAccounts . ' tài khoản đang bị khóa hoặc ngừng hoạt động',
            'hint' => 'Gồm khóa tạm do nhập sai mật khẩu và tài khoản đã ngừng hoạt động.',
            'url' => route('admin.users.index'),
        ];
    }
@endphp

<style>
    /* Trang tự chứa CSS để không phụ thuộc việc biên dịch lại Tailwind */
    .ad { display: flex; flex-direction: column; gap: 24px; }
    .ad-h2 { margin: 0; font-size: 24px; font-weight: 700; color: #1f2937; }
    .ad-sub { margin: 4px 0 0; font-size: 14px; color: #6b7280; }

    .ad-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 14px; box-shadow: 0 1px 2px rgba(15, 23, 42, .05); }
    a.ad-card { display: block; text-decoration: none; color: inherit; transition: box-shadow .15s, transform .15s; }
    a.ad-card.ad-stat { display: flex; }
    a.ad-card.ad-wide { display: block; }
    a.ad-card:hover { box-shadow: 0 6px 18px rgba(15, 23, 42, .08); transform: translateY(-2px); }

    /* Mục cần chú ý */
    .ad-att { padding: 18px 22px; }
    .ad-att h3 { margin: 0 0 12px; font-size: 15px; font-weight: 700; color: #1f2937; }
    .ad-att-row { display: flex; align-items: center; gap: 14px; padding: 11px 0; border-top: 1px solid #f1f5f9; }
    .ad-att-row:first-of-type { border-top: 0; }
    .ad-dot { flex: none; width: 10px; height: 10px; border-radius: 99px; background: #f59e0b; }
    .ad-att-t { flex: 1; min-width: 0; }
    .ad-att-t b { display: block; font-size: 14px; font-weight: 600; color: #1f2937; }
    .ad-att-t span { font-size: 12.5px; color: #6b7280; }
    .ad-link { flex: none; padding: 6px 14px; border-radius: 8px; background: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 600; text-decoration: none; }
    .ad-link:hover { background: #2563eb; color: #fff; }
    .ad-ok { display: flex; align-items: center; gap: 10px; font-size: 14px; color: #15803d; font-weight: 500; }
    .ad-ok svg { width: 20px; height: 20px; }

    /* Thẻ số liệu */
    .ad-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
    .ad-stat { padding: 20px 22px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .ad-lbl { margin: 0 0 4px; font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .ad-num { margin: 0; font-size: 32px; font-weight: 800; line-height: 1.1; color: #2563eb; }
    .ad-note { margin: 8px 0 0; font-size: 12.5px; color: #9ca3af; }
    .ad-ico { flex: none; width: 52px; height: 52px; border-radius: 99px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; }
    .ad-ico svg { width: 26px; height: 26px; }

    .ad-row2 { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
    .ad-wide { padding: 20px 22px; }
    .ad-wide-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .ad-wide-top .ad-ico { width: 40px; height: 40px; }
    .ad-wide-top .ad-ico svg { width: 20px; height: 20px; }
    .ad-wide-top .ad-lbl { margin: 0; }

    .ad-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .ad-chip { display: inline-block; padding: 3px 12px; border-radius: 99px; font-size: 13px; font-weight: 600; }
    .ad-chip.open { background: #dcfce7; color: #15803d; }
    .ad-chip.draft { background: #f1f5f9; color: #475569; }

    .ad-tiles { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .ad-tile { padding: 12px; text-align: center; border-radius: 10px; background: #f8fafc; border: 1px solid #f1f5f9; }
    .ad-tile small { display: block; margin-bottom: 4px; font-size: 12px; color: #6b7280; }
    .ad-tile b { font-size: 22px; font-weight: 800; color: #1d4ed8; }
    .ad-tile span { font-size: 12px; font-weight: 400; color: #9ca3af; }

    @media (max-width: 1279px) { .ad-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 900px) { .ad-row2 { grid-template-columns: 1fr; } }
    @media (max-width: 560px) { .ad-grid { grid-template-columns: 1fr; } .ad-tiles { grid-template-columns: 1fr; } }
</style>

<div class="ad">
    <div>
        <h2 class="ad-h2">Số liệu tổng quan</h2>
        <p class="ad-sub">Thống kê nhanh các chỉ số hoạt động của nền tảng.</p>
    </div>

    <!-- Cần chú ý -->
    <section class="ad-card ad-att">
        <h3>Công việc cần chú ý:</h3>
        @if(count($attention) > 0)
            @foreach($attention as $item)
                <div class="ad-att-row">
                    <span class="ad-dot"></span>
                    <div class="ad-att-t">
                        <b>{{ $item['text'] }}</b>
                        <span>{{ $item['hint'] }}</span>
                    </div>
                    <a href="{{ $item['url'] }}" class="ad-link">Xử lý</a>
                </div>
            @endforeach
        @else
            <div class="ad-ok">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Không có mục nào cần xử lý.
            </div>
        @endif
    </section>

    <!-- Thẻ số liệu -->
    <div class="ad-grid">
        <a href="{{ route('admin.users.index') }}" class="ad-card ad-stat">
            <div>
                <p class="ad-lbl">Tổng học sinh</p>
                <p class="ad-num">{{ number_format($totalStudents) }}</p>
                <p class="ad-note">Học sinh đăng ký trên hệ thống</p>
            </div>
            <div class="ad-ico">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
            </div>
        </a>

        <a href="{{ route('admin.users.index') }}" class="ad-card ad-stat">
            <div>
                <p class="ad-lbl">Tổng giáo viên</p>
                <p class="ad-num">{{ number_format($totalTeachers) }}</p>
                <p class="ad-note">Tài khoản giáo viên được cấp</p>
            </div>
            <div class="ad-ico">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
        </a>

        <a href="{{ route('admin.classes.index') }}" class="ad-card ad-stat">
            <div>
                <p class="ad-lbl">Lớp học</p>
                <p class="ad-num">{{ number_format($activeClasses) }}</p>
                <p class="ad-note">Đang hoạt động{{ $closedClasses > 0 ? ', ' . $closedClasses . ' lớp đã đóng' : '' }}</p>
            </div>
            <div class="ad-ico">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
        </a>

        <div class="ad-card ad-stat">
            <div>
                <p class="ad-lbl">Lượt nộp bài</p>
                <p class="ad-num">{{ number_format($submittedAttempts) }}</p>
                <p class="ad-note">Lượt làm bài đã nộp</p>
            </div>
            <div class="ad-ico">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Đề thi và chuyên đề -->
    <div class="ad-row2">
        <div class="ad-card ad-wide">
            <div class="ad-wide-top">
                <p class="ad-lbl">Tổng đề thi</p>
                <div class="ad-ico">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
            <p class="ad-num">{{ number_format($totalExams) }}</p>
            <div class="ad-chips">
                <span class="ad-chip open">{{ number_format($openExams) }} đang mở</span>
                <span class="ad-chip draft">{{ number_format($draftExams) }} bản nháp</span>
            </div>
        </div>

        <a href="{{ route('admin.topics.index') }}" class="ad-card ad-wide">
            <div class="ad-wide-top">
                <p class="ad-lbl">Danh mục chuyên đề</p>
                <div class="ad-ico">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            <div class="ad-tiles">
                <div class="ad-tile"><small>Khối 10</small><b>{{ $topics10 }}</b> <span>chuyên đề</span></div>
                <div class="ad-tile"><small>Khối 11</small><b>{{ $topics11 }}</b> <span>chuyên đề</span></div>
                <div class="ad-tile"><small>Khối 12</small><b>{{ $topics12 }}</b> <span>chuyên đề</span></div>
            </div>
        </a>
    </div>
</div>
@endsection