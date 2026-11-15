@extends('layouts.app')

@php
    // Biến kiểm tra xem đây là form Sửa hay form Thêm mới
    $isEdit = isset($topic);
    $actionUrl = $isEdit ? route('admin.topics.update', $topic->id) : route('admin.topics.store');
    $usedCount = $isEdit ? ($usedCount ?? 0) : 0;
    $selectedGrade = (string) old('grade', $isEdit ? $topic->grade : '');
    $isActive = (bool) old('is_active', $isEdit ? $topic->is_active : true);
    // Khi form được gửi lại sau lỗi, ô tích không còn gửi giá trị nếu đã bỏ tích
    if (old('name') !== null || $errors->any()) {
        $isActive = old('is_active') !== null;
    }
@endphp

@section('title', $isEdit ? 'Cập nhật chuyên đề' : 'Thêm chuyên đề mới')

@section('content')
<style>
    /* Trang tự chứa CSS để không phụ thuộc việc biên dịch lại Tailwind */
    .tf { max-width: 680px; margin: 0 auto; }
    .tf-back { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 14px; font-size: 14px; font-weight: 500; color: #2563eb; text-decoration: none; }
    .tf-back:hover { text-decoration: underline; }
    .tf-back svg { width: 16px; height: 16px; }
    .tf-card { padding: 32px; background: #fff; border: 1px solid #f1f5f9; border-radius: 14px; box-shadow: 0 1px 2px rgba(15, 23, 42, .05); }
    .tf-title { margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #1f2937; }
    .tf-sub { margin: 0 0 18px; font-size: 14px; color: #6b7280; }

    .tf-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: 0 0 20px; }
    .tf-chip { display: inline-block; padding: 3px 12px; font-size: 12.5px; font-weight: 600; border-radius: 99px; }
    .tf-chip.on { background: #dcfce7; color: #15803d; }
    .tf-chip.off { background: #f3f4f6; color: #6b7280; }
    .tf-chip.info { background: #dbeafe; color: #1d4ed8; }

    .tf-alert { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; margin-bottom: 18px; border-radius: 10px; font-size: 13.5px; line-height: 1.55; }
    .tf-alert svg { flex: none; width: 18px; height: 18px; margin-top: 1px; }
    .tf-alert b { display: block; margin-bottom: 2px; }
    .tf-alert ul { margin: 0; padding-left: 18px; }
    .tf-alert.err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }

    .tf-field { margin-bottom: 20px; }
    .tf label.lbl { display: block; margin-bottom: 6px; font-size: 13.5px; font-weight: 600; color: #374151; }
    .tf label.lbl i { color: #ef4444; font-style: normal; }
    .tf-in { box-sizing: border-box; width: 100%; padding: 10px 12px; font: inherit; font-size: 14px; color: #111827; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; outline: none; transition: border-color .15s, box-shadow .15s; }
    .tf-in:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
    .tf-in.invalid { border-color: #f87171; }
    .tf-in:disabled { background: #f3f4f6; color: #6b7280; cursor: not-allowed; }
    .tf-fe { margin: 5px 0 0; font-size: 12.5px; color: #dc2626; line-height: 1.5; }
    .tf-hint { margin: 6px 0 0; font-size: 12.5px; color: #6b7280; line-height: 1.5; }

    .tf-switch { display: flex; gap: 12px; align-items: flex-start; padding: 14px 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; cursor: pointer; }
    .tf-switch input { flex: none; width: 18px; height: 18px; margin-top: 2px; accent-color: #2563eb; cursor: pointer; }
    .tf-sw { display: block; }
    .tf-sw b { display: block; font-size: 14px; font-weight: 600; color: #374151; }
    .tf-sw small { display: block; margin-top: 2px; font-size: 12.5px; line-height: 1.5; color: #6b7280; }

    .tf-act { display: flex; justify-content: flex-end; gap: 12px; margin-top: 26px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
    .tf-btn { padding: 10px 22px; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; color: #374151; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; cursor: pointer; transition: background .15s; }
    .tf-btn:hover { background: #f9fafb; }
    .tf-btn.pri { color: #fff; background: #2563eb; border-color: #2563eb; }
    .tf-btn.pri:hover { background: #1d4ed8; }
    .tf-btn:disabled { opacity: .65; cursor: not-allowed; }

    @media (max-width: 640px) { .tf-card { padding: 20px; } }
</style>

<div class="tf">
    <a href="{{ route('admin.topics.index') }}" class="tf-back">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Quay lại danh sách
    </a>

    <div class="tf-card">
        <h3 class="tf-title">{{ $isEdit ? 'Chỉnh sửa chuyên đề' : 'Tạo chuyên đề mới' }}</h3>
        <p class="tf-sub">{{ $isEdit ? 'Đổi tên hoặc trạng thái của chuyên đề.' : 'Chuyên đề được dùng để gắn cho từng câu hỏi và phân tích năng lực học sinh.' }}</p>

        @if($isEdit)
            <div class="tf-meta">
                <span class="tf-chip {{ $topic->is_active ? 'on' : 'off' }}">{{ $topic->is_active ? 'Đang hoạt động' : 'Vô hiệu hóa' }}</span>
                <span class="tf-chip info">{{ $usedCount > 0 ? $usedCount . ' câu hỏi đang dùng' : 'Chưa gắn với câu hỏi nào' }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="tf-alert err" role="alert"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>
        @endif

        @if($errors->any())
            <div class="tf-alert err" role="alert"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <b>Chưa lưu được, vui lòng kiểm tra lại:</b>
                    <ul>
                        @foreach(array_unique($errors->all()) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="topic-form" action="{{ $actionUrl }}" method="POST" novalidate>
            @csrf

            {{-- Form sửa cần thêm method PUT --}}
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="tf-field">
                <label class="lbl" for="name">Tên chuyên đề <i>*</i></label>
                <input type="text" id="name" name="name" value="{{ old('name', $isEdit ? $topic->name : '') }}" maxlength="191" required
                       class="tf-in {{ $errors->has('name') ? 'invalid' : '' }}"
                       placeholder="VD: Hàm số lượng giác và phương trình lượng giác">
                @if($errors->has('name'))<p class="tf-fe">{{ $errors->first('name') }}</p>@endif
            </div>

            <div class="tf-field">
                <label class="lbl" for="grade">Khối lớp <i>*</i></label>
                @if($usedCount > 0)
                    <select id="grade" class="tf-in" disabled>
                        <option value="{{ $topic->grade }}">Toán {{ $topic->grade }}</option>
                    </select>
                    <input type="hidden" name="grade" value="{{ $topic->grade }}">
                    @if(!$errors->has('grade'))<p class="tf-hint">Chuyên đề đang gắn với {{ $usedCount }} câu hỏi trong các đề nên không đổi được khối.</p>@endif
                @else
                    <select id="grade" name="grade" required class="tf-in {{ $errors->has('grade') ? 'invalid' : '' }}">
                        <option value="">-- Chọn khối lớp --</option>
                        @foreach([10, 11, 12] as $g)
                            <option value="{{ $g }}" {{ $selectedGrade === (string) $g ? 'selected' : '' }}>Toán {{ $g }}</option>
                        @endforeach
                    </select>
                    <p class="tf-hint">Tên chuyên đề chỉ cần khác nhau trong cùng một khối.</p>
                @endif
                @if($errors->has('grade'))<p class="tf-fe">{{ $errors->first('grade') }}</p>@endif
            </div>

            <label class="tf-switch" for="is_active">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $isActive ? 'checked' : '' }}>
                <span class="tf-sw">
                    <b>Đang hoạt động</b>
                    <small>Giáo viên chỉ chọn được chuyên đề này khi cấu hình đáp án nếu mục này được bật. Bỏ tích sẽ vô hiệu hóa chuyên đề, các câu đã gắn trước đó vẫn được giữ.</small>
                </span>
            </label>

            <div class="tf-act">
                <a href="{{ route('admin.topics.index') }}" class="tf-btn">Hủy bỏ</a>
                <button type="submit" id="submit-btn" class="tf-btn pri" data-label="{{ $isEdit ? 'Lưu thay đổi' : 'Thêm chuyên đề' }}">{{ $isEdit ? 'Lưu thay đổi' : 'Thêm chuyên đề' }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var form = document.getElementById('topic-form');
        var btn = document.getElementById('submit-btn');
        // Chặn bấm gửi hai lần
        form.addEventListener('submit', function () {
            if (form.checkValidity && !form.checkValidity()) return;
            btn.disabled = true;
            btn.textContent = 'Đang lưu...';
        });
        // Quay lại bằng nút Back của trình duyệt: mở lại nút Lưu
        window.addEventListener('pageshow', function () {
            btn.disabled = false;
            btn.textContent = btn.getAttribute('data-label');
        });
    })();
</script>
@endsection