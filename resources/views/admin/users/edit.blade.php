@extends('layouts.app')

@section('title', 'Sửa tài khoản: ' . $user->name)

@section('content')
@php
    $isSelf = $user->id === auth()->id();
    $currentClassMissing = $user->class_id && !$classes->contains('id', $user->class_id);
@endphp

<style>
    /* Trang tự chứa CSS để không phụ thuộc việc biên dịch lại Tailwind */
    .uf { max-width: 760px; margin: 0 auto; }
    .uf-back { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 14px; font-size: 14px; font-weight: 500; color: #2563eb; text-decoration: none; }
    .uf-back:hover { text-decoration: underline; }
    .uf-back svg { width: 16px; height: 16px; }
    .uf-card { padding: 32px; background: #fff; border: 1px solid #f1f5f9; border-radius: 14px; box-shadow: 0 1px 2px rgba(15, 23, 42, .05); }
    .uf-title { margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #1f2937; }
    .uf-sub { margin: 0 0 22px; font-size: 14px; color: #6b7280; }

    .uf-alert { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; margin-bottom: 18px; border-radius: 10px; font-size: 13.5px; line-height: 1.55; }
    .uf-alert svg { flex: none; width: 18px; height: 18px; margin-top: 1px; }
    .uf-alert ul { margin: 0; padding-left: 18px; }
    .uf-alert.err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
    .uf-alert.info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
    .uf-alert.info b { display: block; margin-bottom: 2px; color: #1e3a8a; }
    .uf-alert.warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

    .uf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .uf-full { grid-column: 1 / -1; }
    .uf label.lbl { display: block; margin-bottom: 6px; font-size: 13.5px; font-weight: 600; color: #374151; }
    .uf label.lbl i { color: #ef4444; font-style: normal; }
    .uf-in { box-sizing: border-box; width: 100%; padding: 10px 12px; font: inherit; font-size: 14px; color: #111827; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; outline: none; transition: border-color .15s, box-shadow .15s; }
    .uf-in:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
    .uf-in.invalid { border-color: #f87171; }
    .uf-in:disabled { background: #f3f4f6; color: #6b7280; cursor: not-allowed; }
    .uf-fe { margin: 5px 0 0; font-size: 12.5px; color: #dc2626; }
    .uf-hint { margin: 6px 0 0; font-size: 12.5px; color: #6b7280; line-height: 1.5; }

    .uf-class { padding: 16px; background: #eff6ff; border: 1px solid #dbeafe; border-radius: 12px; }
    .uf-class[hidden] { display: none; }
    .uf-full:has(> .uf-class[hidden]) { display: none; }
    .uf-class .uf-hint { color: #1d4ed8; }
    .uf-class a { color: #1d4ed8; font-weight: 600; }

    .uf-chip { display: inline-block; padding: 3px 12px; border-radius: 99px; font-size: 12.5px; font-weight: 600; }
    .uf-chip.on { background: #dcfce7; color: #15803d; }
    .uf-chip.off { background: #fee2e2; color: #b91c1c; }

    .uf-act { display: flex; justify-content: flex-end; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
    .uf-btn { padding: 10px 22px; border-radius: 10px; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid #d1d5db; background: #fff; color: #374151; transition: background .15s; }
    .uf-btn:hover { background: #f9fafb; }
    .uf-btn.pri { background: #2563eb; border-color: #2563eb; color: #fff; }
    .uf-btn.pri:hover { background: #1d4ed8; }
    .uf-btn:disabled { opacity: .65; cursor: not-allowed; }

    @media (max-width: 640px) { .uf-card { padding: 20px; } .uf-grid { grid-template-columns: 1fr; } }
</style>

<div class="uf">
    <a href="{{ route('admin.users.index') }}" class="uf-back">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Quay lại danh sách
    </a>

    <div class="uf-card">
        <h3 class="uf-title">Cập nhật thông tin</h3>
        <p class="uf-sub">
            Trạng thái:
            @if($user->is_active)<span class="uf-chip on">Đang hoạt động</span>@else<span class="uf-chip off">Đã khóa</span>@endif
            &middot; 
        </p>

        @if(session('error'))
            <div class="uf-alert err" role="alert"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>
        @endif

        @if($errors->any())
            <div class="uf-alert err" role="alert"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($user->role === 'teacher' && $teachingClasses->count() > 0)
            <div class="uf-alert warn"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Giáo viên này đang phụ trách {{ $teachingClasses->count() }} lớp: <b>{{ $teachingClasses->implode(', ') }}</b>. Nếu đổi sang vai trò khác, các lớp này sẽ không còn giáo viên phụ trách cho tới khi bạn gán người khác.</span>
            </div>
        @endif

        <form id="user-form" action="{{ route('admin.users.update', $user->id) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            <div class="uf-grid">
                <div>
                    <label class="lbl" for="name">Họ và tên <i>*</i></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="" required
                           class="uf-in {{ $errors->has('name') ? 'invalid' : '' }}" >
                    @if($errors->has('name'))<p class="uf-fe">{{ $errors->first('name') }}</p>@endif
                </div>
                <div>
                    <label class="lbl" for="email">Email <i>*</i></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="" required
                           class="uf-in {{ $errors->has('email') ? 'invalid' : '' }}" autocomplete="off">
                    @if($errors->has('email'))<p class="uf-fe">{{ $errors->first('email') }}</p>@endif
                </div>

                <div class="uf-full">
                    <label class="lbl" for="role-select">Vai trò <i>*</i></label>
                    @if($isSelf)
                        <input type="hidden" name="role" value="admin">
                        <select id="role-select" class="uf-in" disabled>
                            <option>Admin</option>
                        </select>
                        <p class="uf-hint" id="role-hint">Bạn không thể thay đổi vai trò của chính mình.</p>
                    @else
                        <select id="role-select" name="role" class="uf-in {{ $errors->has('role') ? 'invalid' : '' }}" required>
                            <option value="student" {{ old('role', $user->role) === 'student' ? 'selected' : '' }}>Học sinh</option>
                            <option value="teacher" {{ old('role', $user->role) === 'teacher' ? 'selected' : '' }}>Giáo viên</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @if($errors->has('role'))<p class="uf-fe">{{ $errors->first('role') }}</p>@endif
                        <p class="uf-hint" id="role-hint"></p>
                    @endif
                </div>

            <div class="uf-full">
                <div id="class-container" class="uf-class" {{ old('role', $user->role) === 'student' ? '' : 'hidden' }}>
                    <label class="lbl" for="class_id">Lớp của học sinh</label>
                    <select id="class_id" name="class_id" class="uf-in {{ $errors->has('class_id') ? 'invalid' : '' }}">
                        <option value="">-- Chưa vào lớp --</option>
                        @if($currentClassMissing)
                            <option value="{{ $user->class_id }}" {{ (string) old('class_id', $user->class_id) === (string) $user->class_id ? 'selected' : '' }}>{{ $user->schoolClass?->name ?? ('Lớp #' . $user->class_id) }} (lớp đã đóng)</option>
                        @endif
                        @foreach($classes as $cls)
                            <option value="{{ $cls->id }}" {{ (string) old('class_id', $user->class_id) === (string) $cls->id ? 'selected' : '' }}>{{ $cls->name }} (Khối {{ $cls->grade }})</option>
                        @endforeach
                    </select>
                    @if($errors->has('class_id'))<p class="uf-fe">{{ $errors->first('class_id') }}</p>@endif
                    @if($currentClassMissing)
                        <p class="uf-hint">Học sinh đang thuộc một lớp đã đóng. Giữ nguyên lựa chọn này để không đổi lớp, hoặc chọn lớp đang hoạt động khác.</p>
                    @elseif($classes->isEmpty())
                        <p class="uf-hint">Chưa có lớp nào đang hoạt động. Hãy tạo lớp tại <a href="{{ route('admin.classes.index') }}">Quản lý Lớp học</a>.</p>
                    @else
                        <p class="uf-hint">Danh sách chỉ hiển thị các lớp đang hoạt động.</p>
                    @endif
                </div>
            </div>
            </div>

            <div class="uf-act">
                <a href="{{ route('admin.users.index') }}" class="uf-btn">Hủy bỏ</a>
                <button type="submit" id="submit-btn" class="uf-btn pri">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var roleSel = document.getElementById('role-select');
        var classBox = document.getElementById('class-container');
        var roleHint = document.getElementById('role-hint');
        var hints = {
            student: 'Học sinh chưa có lớp sẽ chưa thấy đề thi nào khi đăng nhập.',
            teacher: 'Gán giáo viên vào lớp ở mục Quản lý Lớp học.',
            admin: 'Admin có toàn quyền quản trị hệ thống.'
        };

        function sync() {
            if (!roleSel) return;
            var v = roleSel.value;
            if (classBox) classBox.hidden = (v !== 'student');
            if (roleHint && hints[v] && !roleSel.disabled) roleHint.textContent = hints[v];
        }
        if (roleSel) { roleSel.addEventListener('change', sync); sync(); }

        // Chặn bấm gửi hai lần
        var form = document.getElementById('user-form');
        var btn = document.getElementById('submit-btn');
        form.addEventListener('submit', function () {
            if (form.checkValidity && !form.checkValidity()) return;
            btn.disabled = true;
            btn.textContent = 'Đang xử lý...';
        });
    })();
</script>
@endsection