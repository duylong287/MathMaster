@extends('layouts.app')

@section('title', 'Thêm lớp học mới')

@section('content')

@php
    $days = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];

    // Ô giờ dạng 24 giờ: hai ô chọn Giờ : Phút (bước 5 phút) và một ô ẩn giữ giá trị HH:MM gửi lên máy chủ
    $timeField = function ($i, $key, $label, $val, $invalid) {
        $h = $mi = '';
        if (preg_match('/^(\d{2}):(\d{2})/', (string) $val, $m)) { $h = $m[1]; $mi = $m[2]; }
        $mins = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'];
        if ($mi !== '' && !in_array($mi, $mins, true)) { $mins[] = $mi; sort($mins); }
        $cls = 'cf-in' . ($invalid ? ' invalid' : '');
        $ho = '<option value="">Giờ</option>';
        for ($x = 0; $x < 24; $x++) { $v = sprintf('%02d', $x); $ho .= '<option value="' . $v . '"' . ($v === $h ? ' selected' : '') . '>' . $v . '</option>'; }
        $mo = '<option value="">Phút</option>';
        foreach ($mins as $v) { $mo .= '<option value="' . $v . '"' . ($v === $mi ? ' selected' : '') . '>' . $v . '</option>'; }
        $hid = $h !== '' ? $h . ':' . $mi : '';
        return '<div class="cf-time"><select class="' . $cls . '" data-part="h" aria-label="' . $label . ' (giờ)">' . $ho . '</select><span>:</span>'
             . '<select class="' . $cls . '" data-part="m" aria-label="' . $label . ' (phút)">' . $mo . '</select>'
             . '<input type="hidden" name="schedules[' . $i . '][' . $key . ']" value="' . $hid . '"></div>';
    };
    $rows = old('schedules', [['day_of_week' => 'Thứ 2', 'start_time' => '', 'end_time' => '']]);
    $curGrade = old('grade', '10');
    $curTeacherId = old('teacher_id');
@endphp
<style>
    /* Trang tự chứa CSS để không phụ thuộc việc biên dịch lại Tailwind */
    .cf { max-width: 820px; margin: 0 auto; }
    .cf-back { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 14px; font-size: 14px; font-weight: 500; color: #2563eb; text-decoration: none; }
    .cf-back:hover { text-decoration: underline; }
    .cf-back svg { width: 16px; height: 16px; }
    .cf-card { padding: 32px; background: #fff; border: 1px solid #f1f5f9; border-radius: 14px; box-shadow: 0 1px 2px rgba(15, 23, 42, .05); }
    .cf-title { margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #1f2937; }
    .cf-sub { margin: 0 0 18px; font-size: 14px; color: #6b7280; }

    .cf-alert { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; margin-bottom: 18px; border-radius: 10px; font-size: 13.5px; line-height: 1.55; }
    .cf-alert svg { flex: none; width: 18px; height: 18px; margin-top: 1px; }
    .cf-alert b { display: block; margin-bottom: 2px; }
    .cf-alert ul { margin: 0; padding-left: 18px; }
    .cf-alert.err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
    .cf-alert.warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

    .cf-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: 0 0 20px; }
    .cf-meta a { font-size: 13px; font-weight: 600; color: #2563eb; text-decoration: none; }
    .cf-meta a:hover { text-decoration: underline; }
    .cf-chip { display: inline-block; padding: 3px 12px; border-radius: 99px; font-size: 12.5px; font-weight: 600; }
    .cf-chip.on { background: #dcfce7; color: #15803d; }
    .cf-chip.off { background: #fee2e2; color: #b91c1c; }
    .cf-chip.info { background: #dbeafe; color: #1d4ed8; }

    .cf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
    .cf-full { grid-column: 1 / -1; }
    .cf label.lbl { display: block; margin-bottom: 6px; font-size: 13.5px; font-weight: 600; color: #374151; }
    .cf label.lbl i { color: #ef4444; font-style: normal; }
    .cf-in { box-sizing: border-box; width: 100%; padding: 10px 12px; font: inherit; font-size: 14px; color: #111827; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; outline: none; transition: border-color .15s, box-shadow .15s; }
    .cf-in:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
    .cf-in.invalid { border-color: #f87171; }
    .cf-in:disabled { background: #f3f4f6; color: #6b7280; cursor: not-allowed; }
    .cf-fe { margin: 5px 0 0; font-size: 12.5px; color: #dc2626; line-height: 1.5; }
    .cf-hint { margin: 6px 0 0; font-size: 12.5px; color: #6b7280; line-height: 1.5; }
    .cf-hint a { color: #2563eb; font-weight: 600; }
    .cf-soft { margin: 6px 0 0; font-size: 12.5px; color: #b45309; line-height: 1.5; }
    .cf-fe[hidden], .cf-soft[hidden] { display: none; }

    .cf-sec { padding-top: 22px; border-top: 1px solid #e5e7eb; }
    .cf-sec-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
    .cf-sec-title { margin: 0; font-size: 16px; font-weight: 700; color: #1f2937; }
    .cf-sec-title i { color: #ef4444; font-style: normal; }
    .cf-add { display: inline-flex; align-items: center; gap: 6px; flex: none; padding: 8px 14px; font: inherit; font-size: 13.5px; font-weight: 600; color: #1d4ed8; background: #eff6ff; border: 1px solid #dbeafe; border-radius: 10px; cursor: pointer; transition: background .15s; }
    .cf-add:hover { background: #dbeafe; }
    .cf-add svg { width: 15px; height: 15px; }

    .cf-row { display: grid; grid-template-columns: 1.2fr 1fr 1fr auto; gap: 12px; align-items: end; margin-bottom: 10px; padding: 14px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; }
    .cf-row label.lbl { margin-bottom: 5px; font-size: 11.5px; letter-spacing: .04em; text-transform: uppercase; color: #6b7280; }
    .cf-row .cf-fe { grid-column: 1 / -1; margin: 0; }
    .cf-rm { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; padding: 0; color: #9ca3af; background: transparent; border: 0; border-radius: 10px; cursor: pointer; transition: background .15s, color .15s; }
    .cf-rm:hover:not(:disabled) { color: #dc2626; background: #fee2e2; }
    .cf-rm:disabled { opacity: .4; cursor: not-allowed; }
    .cf-rm svg { width: 19px; height: 19px; }

    .cf-time { display: flex; align-items: center; gap: 6px; }
    .cf-time .cf-in { flex: 1; min-width: 0; padding-right: 6px; }
    .cf-time span { font-weight: 700; color: #6b7280; }

    .cf-act { display: flex; justify-content: flex-end; gap: 12px; margin-top: 26px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
    .cf-btn { padding: 10px 22px; border-radius: 10px; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid #d1d5db; background: #fff; color: #374151; transition: background .15s; }
    .cf-btn:hover { background: #f9fafb; }
    .cf-btn.pri { background: #2563eb; border-color: #2563eb; color: #fff; }
    .cf-btn.pri:hover { background: #1d4ed8; }
    .cf-btn:disabled { opacity: .65; cursor: not-allowed; }

    @media (max-width: 640px) {
        .cf-card { padding: 20px; }
        .cf-grid { grid-template-columns: 1fr; }
        .cf-sec-head { flex-direction: column; }
        .cf-row { grid-template-columns: 1fr 1fr; }
        .cf-row > div:first-child { grid-column: 1 / -1; }
        .cf-row .cf-rm { grid-column: 1 / -1; justify-self: end; }
    }
</style>

<div class="cf">
    <a href="{{ route('admin.classes.index') }}" class="cf-back">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Quay lại danh sách
    </a>

    <div class="cf-card">
        <h3 class="cf-title">Thêm lớp học mới</h3>
        <p class="cf-sub">Nhập thông tin lớp và lịch học cố định hằng tuần.</p>

        @if(session('error'))
            <div class="cf-alert err" role="alert"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>
        @endif

        @if($errors->any())
            <div class="cf-alert err" role="alert"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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

        <form id="class-form" action="{{ route('admin.classes.store') }}" method="POST" novalidate>
            @csrf

            <div class="cf-grid">
                <div>
                    <label class="lbl" for="name">Tên lớp <i>*</i></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="VD: 10A1, 11 Toán..." maxlength="255" required
                           class="cf-in {{ $errors->has('name') ? 'invalid' : '' }}">
                    @if($errors->has('name'))<p class="cf-fe">{{ $errors->first('name') }}</p>@endif
                    <p class="cf-soft" id="name-hint" hidden>Tên lớp thường bắt đầu bằng số khối (ví dụ 10A1). Bạn vẫn có thể lưu.</p>
                </div>

                <div>
                    <label class="lbl" for="grade">Khối <i>*</i></label>
                    <select id="grade" name="grade" class="cf-in {{ $errors->has('grade') ? 'invalid' : '' }}" required>
                        @foreach([10, 11, 12] as $g)
                            <option value="{{ $g }}" {{ (string) $curGrade === (string) $g ? 'selected' : '' }}>Khối {{ $g }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('grade'))<p class="cf-fe">{{ $errors->first('grade') }}</p>@endif
                </div>

                <div class="cf-full">
                    <label class="lbl" for="teacher_id">Giáo viên phụ trách</label>
                    <select id="teacher_id" name="teacher_id" class="cf-in {{ ($errors->has('teacher_id') || $errors->has('teacher_overlap')) ? 'invalid' : '' }}">
                        <option value="">-- Chưa phân công --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ (string) $curTeacherId === (string) $teacher->id ? 'selected' : '' }}>{{ $teacher->name }} ({{ $teacher->email }})</option>
                        @endforeach
                    </select>
                    @if($errors->has('teacher_id'))<p class="cf-fe">{{ $errors->first('teacher_id') }}</p>@endif
                  
                    @if($teachers->isEmpty())
                        <p class="cf-hint">Chưa có giáo viên nào đang hoạt động. Tạo tài khoản giáo viên ở <a href="{{ route('admin.users.create') }}">Quản lý tài khoản</a>, hoặc để trống và gán sau.</p>
                    @else
                        <p class="cf-hint">Chỉ liệt kê giáo viên đang hoạt động (chưa bị khóa). Hệ thống sẽ chặn nếu giáo viên bị trùng giờ dạy với lớp khác.</p>
                    @endif
                </div>
            </div>

            <div class="cf-sec">
                <div class="cf-sec-head">
                    <div>
                        <h3 class="cf-sec-title">Lịch học hằng tuần <i>*</i></h3>
                        <p class="cf-hint">Mỗi buổi gồm thứ, giờ bắt đầu và giờ kết thúc. Các buổi cùng ngày không được chồng giờ.</p>
                    </div>
                    <button type="button" id="cf-add" class="cf-add">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 4v16m8-8H4"></path></svg>
                        Thêm buổi học
                    </button>
                </div>

                @if($errors->has('schedules'))<p class="cf-fe" style="margin: -6px 0 10px;">{{ $errors->first('schedules') }}</p>@endif

                <div id="cf-rows">
                    @foreach($rows as $i => $sch)
                @php
                    $rowErr = $errors->first("schedules.$i.day_of_week") ?: ($errors->first("schedules.$i.start_time") ?: $errors->first("schedules.$i.end_time"));
                @endphp
                <div class="cf-row">
        <div>
            <label class="lbl">Thứ</label>
            <select name="schedules[{{ $i }}][day_of_week]" aria-label="Thứ" class="cf-in{{ $errors->has("schedules.$i.day_of_week") ? ' invalid' : '' }}">@foreach($days as $d)<option value="{{ $d }}" {{ ($sch['day_of_week'] ?? '') === $d ? 'selected' : '' }}>{{ $d }}</option>@endforeach</select>
        </div>
        <div>
            <label class="lbl">Giờ bắt đầu</label>
            {!! $timeField($i, 'start_time', 'Giờ bắt đầu', $sch['start_time'] ?? '', $errors->has("schedules.$i.start_time")) !!}
        </div>
        <div>
            <label class="lbl">Giờ kết thúc</label>
            {!! $timeField($i, 'end_time', 'Giờ kết thúc', $sch['end_time'] ?? '', $errors->has("schedules.$i.end_time")) !!}
        </div>
        <button type="button" class="cf-rm" title="Xóa buổi này" aria-label="Xóa buổi này" {{ count($rows) <= 1 ? 'disabled' : '' }}><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    <p class="cf-fe cf-row-msg" {{ $rowErr ? '' : 'hidden' }}>{{ $rowErr }}</p>
                </div>
                    @endforeach
                </div>
            </div>

            <div class="cf-act">
                <a href="{{ route('admin.classes.index') }}" class="cf-btn">Hủy bỏ</a>
                <button type="submit" id="submit-btn" class="cf-btn pri" data-label="Lưu lớp học">Lưu lớp học</button>
            </div>
        </form>
    </div>
</div>

<template id="cf-tpl">
        <div class="cf-row">
        <div>
            <label class="lbl">Thứ</label>
            <select name="schedules[__INDEX__][day_of_week]" aria-label="Thứ" class="cf-in">@foreach($days as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach</select>
        </div>
        <div>
            <label class="lbl">Giờ bắt đầu</label>
            {!! $timeField('__INDEX__', 'start_time', 'Giờ bắt đầu', '', false) !!}
        </div>
        <div>
            <label class="lbl">Giờ kết thúc</label>
            {!! $timeField('__INDEX__', 'end_time', 'Giờ kết thúc', '', false) !!}
        </div>
        <button type="button" class="cf-rm" title="Xóa buổi này" aria-label="Xóa buổi này" ><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
            <p class="cf-fe cf-row-msg" hidden></p>
        </div>
</template>

<script>
    (function () {
        var DAYS = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];
        var form = document.getElementById('class-form');
        var box = document.getElementById('cf-rows');
        var tpl = document.getElementById('cf-tpl').innerHTML;
        var addBtn = document.getElementById('cf-add');
        var submitBtn = document.getElementById('submit-btn');
        var nameIn = document.getElementById('name');
        var gradeSel = document.getElementById('grade');
        var nameHint = document.getElementById('name-hint');

        function rows() { return box.querySelectorAll('.cf-row'); }
        // Ô giờ gồm hai ô chọn (giờ, phút) và một ô ẩn giữ giá trị HH:MM
        function selects(el) { return el.type === 'hidden' ? el.parentNode.querySelectorAll('select') : [el]; }
        function syncTime(wrap) {
            var h = wrap.querySelector('[data-part="h"]'), m = wrap.querySelector('[data-part="m"]');
            if (h.value && !m.value) m.value = '00';
            if (!h.value) m.value = '';
            wrap.querySelector('input[type="hidden"]').value = (h.value && m.value) ? h.value + ':' + m.value : '';
        }
        function setTime(hid, val) {
            var wrap = hid.parentNode, h = wrap.querySelector('[data-part="h"]'), m = wrap.querySelector('[data-part="m"]');
            var mt = /^(\d{2}):(\d{2})/.exec(val || '');
            h.value = mt ? mt[1] : '';
            if (mt && !m.querySelector('option[value="' + mt[2] + '"]')) {
                var o = document.createElement('option'); o.value = o.textContent = mt[2]; m.appendChild(o);
            }
            m.value = mt ? mt[2] : '';
            syncTime(wrap);
        }
        function f(row, key) { return row.querySelector('[name$="[' + key + ']"]'); }

        // Chỉ số kế tiếp = chỉ số lớn nhất đang có + 1 (không dùng số dòng, vì chỉ số có thể bị thủng)
        function nextIndex() {
            var max = -1;
            box.querySelectorAll('[name]').forEach(function (el) {
                var m = el.name.match(/^schedules\[(\d+)\]/);
                if (m && +m[1] > max) max = +m[1];
            });
            return max + 1;
        }

        function syncRemove() {
            var one = rows().length <= 1;
            box.querySelectorAll('.cf-rm').forEach(function (b) { b.disabled = one; });
        }

        function clearRow(row) {
            row.querySelectorAll('.cf-in').forEach(function (el) { el.classList.remove('invalid'); });
            var msg = row.querySelector('.cf-row-msg');
            msg.hidden = true; msg.textContent = '';
        }
        function markRow(row, fields, text) {
            fields.forEach(function (el) { selects(el).forEach(function (x) { x.classList.add('invalid'); }); });
            var msg = row.querySelector('.cf-row-msg');
            msg.textContent = text; msg.hidden = false;
        }

        // Kiểm tra giờ ngay trên trình duyệt (máy chủ vẫn kiểm tra lại)
        function validateRows() {
            var ok = true, byDay = {}, firstBad = null;
            function bad(row, fields, text) { ok = false; markRow(row, fields, text); if (!firstBad) firstBad = selects(fields[0])[0]; }

            rows().forEach(function (row) {
                clearRow(row);
                var d = f(row, 'day_of_week'), s = f(row, 'start_time'), e = f(row, 'end_time');
                if (!s.value || !e.value) {
                    bad(row, [!s.value ? s : e], 'Hãy nhập đủ giờ bắt đầu và giờ kết thúc.');
                } else if (e.value <= s.value) {
                    bad(row, [e], 'Giờ kết thúc phải sau giờ bắt đầu.');
                } else {
                    (byDay[d.value] = byDay[d.value] || []).push({ row: row, s: s, e: e });
                }
            });

            Object.keys(byDay).forEach(function (day) {
                var list = byDay[day].sort(function (a, b) { return a.s.value < b.s.value ? -1 : 1; });
                for (var i = 0; i < list.length - 1; i++) {
                    if (list[i + 1].s.value < list[i].e.value) {
                        bad(list[i + 1].row, [list[i + 1].s], 'Buổi này chồng giờ với một buổi khác trong ' + day + '.');
                    }
                }
            });
            if (firstBad) firstBad.focus();
            return ok;
        }

        addBtn.addEventListener('click', function () {
            var list = rows(), last = list[list.length - 1];
            box.insertAdjacentHTML('beforeend', tpl.replace(/__INDEX__/g, nextIndex()));
            var row = rows()[rows().length - 1];
            if (last) {
                // Gợi ý: ngày kế tiếp, giữ nguyên giờ của buổi trước
                var pos = DAYS.indexOf(f(last, 'day_of_week').value);
                f(row, 'day_of_week').value = DAYS[(pos + 1) % DAYS.length];
                setTime(f(row, 'start_time'), f(last, 'start_time').value);
                setTime(f(row, 'end_time'), f(last, 'end_time').value);
            }
            syncRemove();
            f(row, 'day_of_week').focus();
        });

        box.addEventListener('click', function (e) {
            var btn = e.target.closest('.cf-rm');
            if (!btn || btn.disabled) return;
            btn.closest('.cf-row').remove();
            syncRemove();
        });

        box.addEventListener('input', function (e) {
            var row = e.target.closest('.cf-row');
            if (row) clearRow(row);
        });
        box.addEventListener('change', function (e) {
            var wrap = e.target.closest('.cf-time');
            if (wrap) syncTime(wrap);
            var row = e.target.closest('.cf-row');
            if (row) clearRow(row);
        });

        // Nhắc nhẹ khi tên lớp không bắt đầu bằng số khối (máy chủ cũng chỉ nhắc, không chặn)
        function syncNameHint() {
            var v = nameIn.value.trim();
            nameHint.hidden = !v || v.indexOf(gradeSel.value) === 0;
        }
        nameIn.addEventListener('input', syncNameHint);
        gradeSel.addEventListener('change', syncNameHint);
        syncNameHint();

        form.addEventListener('submit', function (e) {
            if (!validateRows()) { e.preventDefault(); return; }
            if (form.checkValidity && !form.checkValidity()) return;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang lưu...';
        });
        // Quay lại bằng nút Back của trình duyệt: mở lại nút Lưu
        window.addEventListener('pageshow', function () {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.getAttribute('data-label');
        });

        syncRemove();
    })();
</script>
@endsection