@extends('layouts.app')

@section('title', 'Quản lý chuyên đề')

@section('content')
@php
    $grade = (string) request('grade', '');
    $status = (string) request('status', '');
    $keyword = trim((string) request('q', ''));
    $filtered = $grade !== '' || $status !== '' || $keyword !== '';
    $totalAll = $gradeCounts->sum();

    // Giữ nguyên các bộ lọc còn lại khi chuyển tab khối
    $qs = fn (array $over) => array_filter(
        array_merge(['grade' => $grade, 'status' => $status, 'q' => $keyword], $over),
        fn ($v) => $v !== null && $v !== ''
    );
@endphp
<style>
    /* Trang tự chứa CSS để không phụ thuộc việc biên dịch lại Tailwind */
    .tp-alert { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; margin-bottom: 16px; border-radius: 10px; font-size: 14px; line-height: 1.55; }
    .tp-alert svg { flex: none; width: 18px; height: 18px; margin-top: 1px; }
    .tp-alert.ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
    .tp-alert.err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }

    .tp-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 14px; box-shadow: 0 1px 2px rgba(15, 23, 42, .05); overflow: hidden; }
    .tp-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 22px 24px 0; }
    .tp-title { margin: 0; font-size: 18px; font-weight: 700; color: #1f2937; }
    .tp-sub { margin: 3px 0 0; font-size: 13.5px; color: #6b7280; }
    .tp-add { display: inline-flex; align-items: center; gap: 8px; flex: none; padding: 9px 16px; font-size: 14px; font-weight: 600; color: #fff; text-decoration: none; background: #2563eb; border-radius: 10px; transition: background .15s; }
    .tp-add:hover { background: #1d4ed8; }
    .tp-add svg { width: 16px; height: 16px; }

    .tp-tabs { display: flex; flex-wrap: wrap; gap: 8px; padding: 18px 24px 0; }
    .tp-tab { display: inline-flex; align-items: center; gap: 7px; padding: 6px 14px; font-size: 13.5px; font-weight: 600; color: #4b5563; text-decoration: none; background: #f3f4f6; border-radius: 99px; transition: background .15s; }
    .tp-tab:hover { background: #e5e7eb; }
    .tp-tab b { font-weight: 600; font-size: 12px; color: #6b7280; }
    .tp-tab.on { color: #fff; background: #2563eb; }
    .tp-tab.on b { color: #dbeafe; }

    .tp-filter { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 16px 24px; border-bottom: 1px solid #f1f5f9; }
    .tp-search { position: relative; flex: 1 1 240px; max-width: 360px; }
    .tp-search svg { position: absolute; left: 11px; top: 50%; width: 16px; height: 16px; margin-top: -8px; color: #9ca3af; pointer-events: none; }
    .tp-in { box-sizing: border-box; width: 100%; padding: 9px 12px; font: inherit; font-size: 14px; color: #111827; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; outline: none; transition: border-color .15s, box-shadow .15s; }
    .tp-search .tp-in { padding-left: 34px; }
    .tp-in:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
    .tp-select { width: auto; cursor: pointer; }
    .tp-clear { font-size: 13.5px; font-weight: 600; color: #2563eb; text-decoration: none; }
    .tp-clear:hover { text-decoration: underline; }
    .tp-count { margin-left: auto; font-size: 13px; color: #6b7280; }

    .tp-wrap { overflow-x: auto; }
    .tp-table { width: 100%; min-width: 640px; border-collapse: collapse; text-align: left; font-size: 14px; color: #374151; }
    .tp-table th { padding: 11px 24px; font-size: 12.5px; font-weight: 600; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
    .tp-table td { padding: 14px 24px; vertical-align: middle; border-bottom: 1px solid #f3f4f6; }
    .tp-table tbody tr:hover { background: #f9fafb; }
    .tp-table tbody tr:last-child td { border-bottom: 0; }
    .tp-c { text-align: center; }
    .tp-r { text-align: right; }
    .tp-name { font-weight: 600; color: #111827; }
    .tp-off .tp-name { color: #9ca3af; font-weight: 500; }

    .tp-chip { display: inline-block; padding: 3px 12px; font-size: 12.5px; font-weight: 600; border-radius: 99px; white-space: nowrap; }
    .tp-chip.grade { background: #eff6ff; color: #1d4ed8; }
    .tp-chip.on { background: #dcfce7; color: #15803d; }
    .tp-chip.off { background: #f3f4f6; color: #6b7280; }

    .tp-acts { display: flex; align-items: center; justify-content: flex-end; gap: 8px; }
    .tp-acts form { margin: 0; }
    .tp-btn { display: inline-block; padding: 6px 12px; font: inherit; font-size: 13.5px; font-weight: 600; line-height: 1.3; text-decoration: none; border: 0; border-radius: 8px; cursor: pointer; transition: background .15s; }
    .tp-btn.edit { color: #1d4ed8; background: #eff6ff; }
    .tp-btn.edit:hover { background: #dbeafe; }
    .tp-btn.stop { color: #b91c1c; background: #fef2f2; }
    .tp-btn.stop:hover { background: #fee2e2; }
    .tp-btn.go { color: #15803d; background: #f0fdf4; }
    .tp-btn.go:hover { background: #dcfce7; }

    .tp-empty { padding: 48px 24px; text-align: center; color: #6b7280; font-size: 14.5px; }
    .tp-empty a { font-weight: 600; color: #2563eb; }

    @media (max-width: 640px) {
        .tp-head { flex-direction: column; align-items: stretch; }
        .tp-add { justify-content: center; }
        .tp-head, .tp-tabs, .tp-filter { padding-left: 16px; padding-right: 16px; }
        .tp-count { margin-left: 0; width: 100%; }
    }
</style>

@if(session('success'))
    <div class="tp-alert ok" role="status">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif
@if(session('error'))
    <div class="tp-alert err" role="alert">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

<div class="tp-card">
    <div class="tp-head">
        <div>
            <h3 class="tp-title">Danh sách chuyên đề</h3>
            
        </div>
        <a href="{{ route('admin.topics.create') }}" class="tp-add">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 4v16m8-8H4"></path></svg>
            Thêm chuyên đề
        </a>
    </div>

    <div class="tp-tabs">
        <a href="{{ route('admin.topics.index', $qs(['grade' => null])) }}" class="tp-tab {{ $grade === '' ? 'on' : '' }}">Tất cả</a>
        @foreach([10, 11, 12] as $g)
            <a href="{{ route('admin.topics.index', $qs(['grade' => (string) $g])) }}" class="tp-tab {{ $grade === (string) $g ? 'on' : '' }}">Toán {{ $g }}</a>
        @endforeach
    </div>

    <form action="{{ route('admin.topics.index') }}" method="GET" class="tp-filter">
        @if($grade !== '')<input type="hidden" name="grade" value="{{ $grade }}">@endif
        <div class="tp-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
            <input type="search" name="q" value="{{ $keyword }}" placeholder="Tìm theo tên chuyên đề..." class="tp-in" aria-label="Tìm theo tên chuyên đề">
        </div>
        <select name="status" class="tp-in tp-select" aria-label="Lọc theo trạng thái" onchange="this.form.submit()">
            <option value="">Mọi trạng thái</option>
            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Vô hiệu hóa</option>
        </select>
        @if($filtered)
            <a href="{{ route('admin.topics.index') }}" class="tp-clear">Xóa bộ lọc</a>
        @endif
        
    </form>

    <div class="tp-wrap">
        <table class="tp-table">
            <thead>
                <tr>
                    <th>Tên chuyên đề</th>
                    <th class="tp-c">Khối lớp</th>
                    <th class="tp-c">Trạng thái</th>
                    <th class="tp-c">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topics as $topic)
                    <tr class="{{ $topic->is_active ? '' : 'tp-off' }}">
                        <td class="tp-name">{{ $topic->name }}</td>
                        <td class="tp-c"><span class="tp-chip grade">Khối {{ $topic->grade }}</span></td>
                        <td class="tp-c">
                            @if($topic->is_active)
                                <span class="tp-chip on">Đang hoạt động</span>
                            @else
                                <span class="tp-chip off">Vô hiệu hóa</span>
                            @endif
                        </td>
                        <td>
                            <div class="tp-acts">
                                <a href="{{ route('admin.topics.edit', $topic->id) }}" class="tp-btn edit">Sửa</a>

                                @if($topic->is_active)
                                    <form action="{{ route('admin.topics.deactivate', $topic->id) }}" method="POST"
                                          data-confirm="Vô hiệu hóa chuyên đề “{{ $topic->name }}”? Chuyên đề sẽ không còn xuất hiện khi giáo viên cấu hình đáp án. Các câu đã gắn chuyên đề này vẫn được giữ, và bạn có thể bật lại bất cứ lúc nào.">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="tp-btn stop">Vô hiệu hóa</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.topics.update', $topic->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="name" value="{{ $topic->name }}">
                                        <input type="hidden" name="grade" value="{{ $topic->grade }}">
                                        <input type="hidden" name="is_active" value="1">
                                        <input type="hidden" name="_reactivate" value="1">
                                        <button type="submit" class="tp-btn go">Bật lại</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="tp-empty">
                            @if($filtered)
                                Không có chuyên đề nào khớp bộ lọc. <a href="{{ route('admin.topics.index') }}">Xóa bộ lọc</a>
                            @else
                                Chưa có chuyên đề nào trong hệ thống. <a href="{{ route('admin.topics.create') }}">Thêm chuyên đề đầu tiên</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // Hỏi xác nhận cho các form có data-confirm (tên chuyên đề đã được Blade escape trong thuộc tính)
    document.addEventListener('submit', function (e) {
        var msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) e.preventDefault();
    });
</script>
@endsection