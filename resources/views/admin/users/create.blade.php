@extends('layouts.app')

@section('title', 'Thêm tài khoản mới')

@section('content')
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
        <h3 class="uf-title">Tạo tài khoản mới</h3>
        <p class="uf-sub">Nhập thông tin cơ bản, hệ thống sẽ tạo mật khẩu và gửi cho người dùng.</p>

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

        <div class="uf-alert info"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <b>Mật khẩu tự động</b>
                Hệ thống tự sinh mật khẩu ngẫu nhiên và gửi thông tin đăng nhập tới email của người dùng sau khi bạn bấm Tạo. Nếu không gửi được email, tài khoản sẽ không được tạo.
            </div>
        </div>

        <form id="user-form" action="{{ route('admin.users.store') }}" method="POST" novalidate>
            @csrf

            <div class="uf-grid">
                <div>
                    <label class="lbl" for="name">Họ và tên <i>*</i></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nhập họ và tên..." required
                           class="uf-in {{ $errors->has('name') ? 'invalid' : '' }}" >
                    @if($errors->has('name'))<p class="uf-fe">{{ $errors->first('name') }}</p>@endif
                </div>
                <div>
                    <label class="lbl" for="email">Email <i>*</i></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="ten@example.com" required
                           class="uf-in {{ $errors->has('email') ? 'invalid' : '' }}" autocomplete="off">
                    @if($errors->has('email'))<p class="uf-fe">{{ $errors->first('email') }}</p>@endif
                </div>

                <div class="uf-full">
                    <label class="lbl" for="role-select">Vai trò <i>*</i></label>
                    <select id="role-select" name="role" class="uf-in {{ $errors->has('role') ? 'invalid' : '' }}" required>
                        <option value="student" {{ old('role', 'student') === 'student' ? 'selected' : '' }}>Học sinh</option>
                        <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Giáo viên</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @if($errors->has('role'))<p class="uf-fe">{{ $errors->first('role') }}</p>@endif
                    <p class="uf-hint" id="role-hint"></p>
                </div>

            <div class="uf-full">
                <div id="class-container" class="uf-class" {{ old('role', 'student') === 'student' ? '' : 'hidden' }}>
                    <label class="lbl" for="class_id">Gán vào lớp học (tùy chọn)</label>
                    <select id="class_id" name="class_id" class="uf-in {{ $errors->has('class_id') ? 'invalid' : '' }}">
                        <option value="">-- Chưa vào lớp --</option>
                        @foreach($classes as $cls)
                            <option value="{{ $cls->id }}" {{ (string) old('class_id') === (string) $cls->id ? 'selected' : '' }}>{{ $cls->name }} (Khối {{ $cls->grade }})</option>
                        @endforeach
                    </select>
                    @if($errors->has('class_id'))<p class="uf-fe">{{ $errors->first('class_id') }}</p>@endif
                    @if($classes->isEmpty())
                        <p class="uf-hint">Chưa có lớp nào đang hoạt động. Hãy tạo lớp tại <a href="{{ route('admin.classes.index') }}">Quản lý Lớp học</a> trước.</p>
                    @else
                        <p class="uf-hint">Danh sách chỉ hiển thị các lớp đang hoạt động.</p>
                    @endif
                </div>
            </div>
            </div>

            <div class="uf-act">
                <a href="{{ route('admin.users.index') }}" class="uf-btn">Hủy bỏ</a>
                <button type="submit" id="submit-btn" class="uf-btn pri">Tạo tài khoản</button>
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