@extends('layouts.app')

@section('title', 'Quản lý lớp học')

@section('content')
@php
    $grade = (string) request('grade', '');
    $status = (string) request('status', '');
    $keyword = trim((string) request('q', ''));
    $filtered = $grade !== '' || $keyword !== '' || in_array($status, ['closed', 'all'], true);
    $totalAll = $gradeCounts->sum();
    $warnings = (array) session('warning_list', []);

    // Giữ nguyên các bộ lọc còn lại khi chuyển tab khối
    $qs = fn (array $over) => array_filter(
        array_merge(['grade' => $grade, 'status' => $status, 'q' => $keyword], $over),
        fn ($v) => $v !== null && $v !== ''
    );
@endphp
<style>
    /* Trang tự chứa CSS để không phụ thuộc việc biên dịch lại Tailwind */
    .cl-alert { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; margin-bottom: 16px; border-radius: 10px; font-size: 14px; line-height: 1.55; }
    .cl-alert svg { flex: none; width: 18px; height: 18px; margin-top: 1px; }
    .cl-alert.ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
    .cl-alert.err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }

    .cl-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 14px; box-shadow: 0 1px 2px rgba(15, 23, 42, .05); overflow: hidden; }
    .cl-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 22px 24px 0; }
    .cl-title { margin: 0; font-size: 18px; font-weight: 700; color: #1f2937; }
    .cl-sub { margin: 3px 0 0; font-size: 13.5px; color: #6b7280; }
    .cl-add { display: inline-flex; align-items: center; gap: 8px; flex: none; padding: 9px 16px; font-size: 14px; font-weight: 600; color: #fff; text-decoration: none; background: #2563eb; border-radius: 10px; transition: background .15s; }
    .cl-add:hover { background: #1d4ed8; }
    .cl-add svg { width: 16px; height: 16px; }

    .cl-tabs { display: flex; flex-wrap: wrap; gap: 8px; padding: 18px 24px 0; }
    .cl-tab { display: inline-flex; align-items: center; gap: 7px; padding: 6px 14px; font-size: 13.5px; font-weight: 600; color: #4b5563; text-decoration: none; background: #f3f4f6; border-radius: 99px; transition: background .15s; }
    .cl-tab:hover { background: #e5e7eb; }
    .cl-tab b { font-weight: 600; font-size: 12px; color: #6b7280; }
    .cl-tab.on { color: #fff; background: #2563eb; }
    .cl-tab.on b { color: #dbeafe; }

    .cl-filter { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 16px 24px; border-bottom: 1px solid #f1f5f9; }
    .cl-search { position: relative; flex: 1 1 240px; max-width: 360px; }
    .cl-search svg { position: absolute; left: 11px; top: 50%; width: 16px; height: 16px; margin-top: -8px; color: #9ca3af; pointer-events: none; }
    .cl-in { box-sizing: border-box; width: 100%; padding: 9px 12px; font: inherit; font-size: 14px; color: #111827; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; outline: none; transition: border-color .15s, box-shadow .15s; }
    .cl-search .cl-in { padding-left: 34px; }
    .cl-in:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
    .cl-select { width: auto; cursor: pointer; }
    .cl-clear { font-size: 13.5px; font-weight: 600; color: #2563eb; text-decoration: none; }
    .cl-clear:hover { text-decoration: underline; }
    .cl-count { margin-left: auto; font-size: 13px; color: #6b7280; }

    .cl-wrap { overflow-x: auto; }
    .cl-table { width: 100%; min-width: 940px; border-collapse: collapse; text-align: left; font-size: 14px; color: #374151; }
    .cl-table th { padding: 11px 24px; font-size: 12.5px; font-weight: 600; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
    .cl-table td { padding: 14px 24px; vertical-align: middle; border-bottom: 1px solid #f3f4f6; }
    .cl-table tbody tr:hover { background: #f9fafb; }
    .cl-table tbody tr:last-child td { border-bottom: 0; }
    .cl-c { text-align: center; }
    .cl-r { text-align: right; }
    .cl-name { font-weight: 600; color: #111827; }
    .cl-off .cl-name { color: #9ca3af; font-weight: 500; }

    .cl-chip { display: inline-block; padding: 3px 12px; font-size: 12.5px; font-weight: 600; border-radius: 99px; white-space: nowrap; }
    .cl-chip.grade { background: #eff6ff; color: #1d4ed8; }
    .cl-chip.on { background: #dcfce7; color: #15803d; }
    .cl-chip.off { background: #f3f4f6; color: #6b7280; }

    .cl-acts { display: flex; align-items: center; justify-content: flex-end; gap: 8px; }
    .cl-acts form { margin: 0; }
    .cl-btn { display: inline-block; padding: 6px 12px; font: inherit; font-size: 13.5px; font-weight: 600; line-height: 1.3; text-decoration: none; border: 0; border-radius: 8px; cursor: pointer; transition: background .15s; }
    .cl-btn.edit { color: #1d4ed8; background: #eff6ff; }
    .cl-btn.edit:hover { background: #dbeafe; }
    .cl-btn.stop { color: #b91c1c; background: #fef2f2; }
    .cl-btn.stop:hover { background: #fee2e2; }
    .cl-btn.go { color: #15803d; background: #f0fdf4; }
    .cl-btn.go:hover { background: #dcfce7; }

    .cl-empty { padding: 48px 24px; text-align: center; color: #6b7280; font-size: 14.5px; }
    .cl-empty a { font-weight: 600; color: #2563eb; }

    @media (max-width: 640px) {
        .cl-head { flex-direction: column; align-items: stretch; }
        .cl-add { justify-content: center; }
        .cl-head, .cl-tabs, .cl-filter { padding-left: 16px; padding-right: 16px; }
        .cl-count { margin-left: 0; width: 100%; }
    }

    .cl-name a { color: #111827; text-decoration: none; }
    .cl-name a:hover { color: #2563eb; text-decoration: underline; }
    .cl-off .cl-name a { color: #9ca3af; font-weight: 500; }
    .cl-warnrow { background: #fffbeb; }
    .cl-warnrow:hover { background: #fef3c7 !important; }
    .cl-flags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 5px; }
    .cl-flag { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; font-size: 11.5px; font-weight: 600; color: #b45309; background: #fef3c7; border-radius: 99px; }
    .cl-flag svg { width: 11px; height: 11px; }
    .cl-lines div { white-space: nowrap; line-height: 1.6; }
    .cl-muted { color: #9ca3af; }
    .cl-sub2 { display: block; margin-top: 2px; font-size: 12px; color: #b45309; }
    .cl-num { font-weight: 600; color: #2563eb; }
    .cl-num.zero { color: #9ca3af; font-weight: 500; }
    .cl-btn.view { color: #374151; background: #f3f4f6; }
    .cl-btn.view:hover { background: #e5e7eb; }
    .cl-btn.dis { color: #9ca3af; background: #f3f4f6; cursor: not-allowed; }
    .cl-acts { white-space: nowrap; }
    .cl-pager { padding: 14px 24px; border-top: 1px solid #f3f4f6; }
    .cl-alert.warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .cl-alert ul { margin: 4px 0 0; padding-left: 18px; }
    .cl-alert b { display: block; }
</style>

@if(session('success'))
    <div class="cl-alert ok" role="status">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif
@if(session('error'))
    <div class="cl-alert err" role="alert">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif
@if(count($warnings) > 0)
    <div class="cl-alert warn" role="status">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
        <div>
            <b>Lớp đã được lưu, nhưng bạn nên kiểm tra:</b>
            <ul>
                @foreach($warnings as $w)
                    <li>{{ $w }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="cl-card">
    <div class="cl-head">
        <div>
            <h3 class="cl-title">Danh sách lớp học</h3>
            <p class="cl-sub">Quản lý lớp, giáo viên phụ trách và lịch học cố định hằng tuần.</p>
        </div>
        <a href="{{ route('admin.classes.create') }}" class="cl-add">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 4v16m8-8H4"></path></svg>
            Thêm lớp mới
        </a>
    </div>

    <div class="cl-tabs">
        <a href="{{ route('admin.classes.index', $qs(['grade' => null])) }}" class="cl-tab {{ $grade === '' ? 'on' : '' }}">Tất cả <b>{{ $totalAll }}</b></a>
        @foreach([10, 11, 12] as $g)
            <a href="{{ route('admin.classes.index', $qs(['grade' => (string) $g])) }}" class="cl-tab {{ $grade === (string) $g ? 'on' : '' }}">Khối {{ $g }} <b>{{ $gradeCounts[$g] ?? 0 }}</b></a>
        @endforeach
    </div>

    <form action="{{ route('admin.classes.index') }}" method="GET" class="cl-filter">
        @if($grade !== '')<input type="hidden" name="grade" value="{{ $grade }}">@endif
        <div class="cl-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
            <input type="search" name="q" value="{{ $keyword }}" placeholder="Tìm theo tên lớp..." class="cl-in" aria-label="Tìm theo tên lớp">
        </div>
        <select name="status" class="cl-in cl-select" aria-label="Lọc theo trạng thái" onchange="this.form.submit()">
            <option value="">Đang hoạt động</option>
            <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Đã đóng</option>
            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
        </select>
        @if($filtered)
            <a href="{{ route('admin.classes.index') }}" class="cl-clear">Xóa bộ lọc</a>
        @endif
       
    </form>

    <div class="cl-wrap">
        <table class="cl-table">
            <thead>
                <tr>
                    <th>Tên lớp</th>
                    <th class="cl-c">Khối</th>
                    <th>Lịch học</th>
                    <th>Giáo viên</th>
                    <th class="cl-c">Sĩ số</th>
                    <th class="cl-c">Trạng thái</th>
                    <th class="cl-c">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classes as $cls)
                    @php
                        $isActive = $cls->status === 'active';
                        $noTeacher = $isActive && !$cls->teacher_id;
                        $noSchedule = $isActive && empty($cls->schedule_lines);
                        $lines = $cls->schedule_lines ?? [];
                    @endphp
                    <tr class="{{ ($noTeacher || $noSchedule) ? 'cl-warnrow' : '' }} {{ $isActive ? '' : 'cl-off' }}">
                        <td class="cl-name">
                            <a href="{{ route('admin.classes.show', $cls->id) }}">{{ $cls->name }}</a>
                            @if($noTeacher || $noSchedule)
                                <div class="cl-flags">
                                    @if($noTeacher)<span class="cl-flag">Thiếu giáo viên</span>@endif
                                    @if($noSchedule)<span class="cl-flag">Chưa xếp lịch</span>@endif
                                </div>
                            @endif
                        </td>
                        <td class="cl-c">{{ $cls->grade }}</td>
                        <td>
                            @if(count($lines) > 0)
                                <div class="cl-lines">
                                    @foreach($lines as $line)
                                        <div>{{ $line }}</div>
                                    @endforeach
                                </div>
                            @else
                                <span class="cl-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($cls->teacher)
                                {{ $cls->teacher->name }}
                                @if(!$cls->teacher->is_active)<span class="cl-sub2">tài khoản đang bị khóa</span>@endif
                            @else
                                <span class="cl-muted">—</span>
                            @endif
                        </td>
                        <td class="cl-c"><span class="cl-num {{ $cls->students_count > 0 ? '' : 'zero' }}">{{ $cls->students_count }}</span></td>
                        <td class="cl-c">
                            @if($isActive)
                                <span class="cl-chip on">Hoạt động</span>
                            @else
                                <span class="cl-chip off">Đã đóng</span>
                            @endif
                        </td>
                        <td>
                            <div class="cl-acts">
                                <a href="{{ route('admin.classes.show', $cls->id) }}" class="cl-btn view">Danh sách lớp</a>
                                <a href="{{ route('admin.classes.edit', $cls->id) }}" class="cl-btn edit">Sửa</a>

                                @if($isActive)
                                    @if($cls->students_count > 0)
                                        <button type="button" class="cl-btn dis" disabled title="Lớp còn {{ $cls->students_count }} học sinh. Hãy chuyển các em sang lớp khác trước khi đóng lớp.">Đóng lớp</button>
                                    @else
                                        <form action="{{ route('admin.classes.close', $cls->id) }}" method="POST"
                                              data-confirm="Đóng lớp “{{ $cls->name }}”? Lớp sẽ không còn hiện trong danh sách đang hoạt động.">
                                            @csrf
                                            <button type="submit" class="cl-btn stop">Đóng lớp</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="cl-empty">
                            @if($filtered)
                                Không có lớp nào khớp bộ lọc. <a href="{{ route('admin.classes.index') }}">Xóa bộ lọc</a>
                            @else
                                Chưa có lớp nào đang hoạt động. <a href="{{ route('admin.classes.create') }}">Thêm lớp đầu tiên</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($classes->hasPages())
        <div class="cl-pager">{{ $classes->links() }}</div>
    @endif
</div>

<script>
    // Hỏi xác nhận cho các form có data-confirm (tên lớp đã được Blade escape trong thuộc tính)
    document.addEventListener('submit', function (e) {
        var msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) e.preventDefault();
    });
</script>
@endsection