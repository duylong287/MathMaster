@extends('layouts.teacher')

@section('title', 'Cấu hình Đáp án (Bước 3/3)')

@section('content')
@php
    // Ưu tiên dữ liệu vừa nhập (old) -> dữ liệu đã lưu nháp ($saved) -> mặc định
    $val = fn ($key, $default = '') => old($key, data_get($saved ?? [], $key, $default));
    $bad = fn ($key) => $errors->has($key) ? ' ca-bad' : '';
    $isMock = ($exam->type ?? '') === 'Thi thử THPT QG';
    $noTopics = $topics->isEmpty();
@endphp

{{--
    Toàn bộ giao diện của trang dùng CSS riêng (tiền tố "ca-") đặt ngay dưới đây,
    KHÔNG phụ thuộc vào việc Tailwind/Vite đã build các class hay chưa,
    nên trang luôn hiển thị đúng dù chưa chạy "npm run build".
--}}
<style>
    .ca-wrap{max-width:1152px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.05);padding:24px}
    .ca-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;border-bottom:1px solid #f3f4f6;padding-bottom:16px;margin-bottom:20px}
    .ca-head h3{margin:0;font-size:22px;font-weight:700;color:#1f2937}
    .ca-head p{margin:4px 0 0;font-size:14px;color:#6b7280}
    .ca-head p b{color:#2563eb}
    .ca-step{font-size:13px;font-weight:600;color:#9ca3af;white-space:nowrap}
    .ca-alert{border-left:4px solid;border-radius:6px;padding:12px 16px;margin-bottom:16px;font-size:14px}
    .ca-alert.err{background:#fef2f2;border-color:#ef4444;color:#b91c1c}
    .ca-alert.warn{background:#fffbeb;border-color:#f59e0b;color:#92400e}
    .ca-alert.info{background:#eff6ff;border-color:#3b82f6;color:#1e40af}
    .ca-alert b{display:block;margin-bottom:4px}
    .ca-alert ul{margin:6px 0 0;padding-left:20px;max-height:170px;overflow-y:auto}
    .ca-alert li{margin:2px 0}

    .ca-part{margin-bottom:28px}
    .ca-part-title{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;padding:10px 14px;border:1px solid;border-radius:8px 8px 0 0;font-weight:700;font-size:16px}
    .ca-part-title small{font-weight:600;font-size:13px}
    .ca-p1 .ca-part-title{background:#eff6ff;border-color:#dbeafe;color:#1e40af}
    .ca-p2 .ca-part-title{background:#f0fdf4;border-color:#dcfce7;color:#166534}
    .ca-p3 .ca-part-title{background:#fff7ed;border-color:#ffedd5;color:#9a3412}

    .ca-bulk{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:10px 14px;background:#f9fafb;border:1px solid #e5e7eb;border-top:0;font-size:13px;color:#4b5563}
    .ca-bulk select{max-width:260px}
    .ca-btn-sm{padding:6px 12px;border:1px solid #d1d5db;border-radius:6px;background:#fff;font-size:13px;font-weight:600;color:#374151;cursor:pointer}
    .ca-btn-sm:hover{background:#f3f4f6}

    .ca-scroll{overflow-x:auto;border:1px solid #e5e7eb;border-top:0;border-radius:0 0 8px 8px}
    .ca-table{width:100%;border-collapse:collapse;font-size:14px}
    .ca-table th{background:#f9fafb;color:#374151;font-weight:600;padding:10px 12px;text-align:left;white-space:nowrap}
    .ca-table td{padding:8px 12px;border-top:1px solid #f3f4f6;vertical-align:middle}
    .ca-table tbody tr:hover{background:#fafafa}
    .ca-table .c{text-align:center}
    .ca-q{font-weight:700;text-align:center;width:56px}
    .ca-table tr.ca-incomplete{background:#fffbeb}

    .ca-input,.ca-select{width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:14px;background:#fff;color:#111827;outline:none}
    .ca-input:focus,.ca-select:focus{border-color:#2563eb;box-shadow:0 0 0 2px rgba(37,99,235,.2)}
    .ca-bad .ca-input,.ca-bad .ca-select,.ca-input.ca-bad,.ca-select.ca-bad{border-color:#ef4444;background:#fef2f2}
    td.ca-bad{outline:2px solid #fecaca;outline-offset:-2px;border-radius:6px}
    .ca-select.sm{width:68px;text-align:center;padding:6px 4px}
    .ca-select[data-v="Đ"]{background:#dcfce7;border-color:#86efac;color:#166534;font-weight:700}
    .ca-select[data-v="S"]{background:#fee2e2;border-color:#fca5a5;color:#991b1b;font-weight:700}
    .ca-tcol{min-width:200px}
    .ca-lcol{min-width:150px}

    .ca-chips{display:flex;justify-content:center;gap:8px}
    .ca-chip{position:relative;display:inline-block;cursor:pointer}
    .ca-chip input{position:absolute;opacity:0;width:100%;height:100%;margin:0;cursor:pointer}
    .ca-chip span{display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;border:1px solid #d1d5db;background:#fff;font-weight:600;color:#374151;transition:.15s}
    .ca-chip:hover span{background:#f3f4f6}
    .ca-chip input:checked + span{background:#2563eb;border-color:#2563eb;color:#fff}
    .ca-chip input:focus-visible + span{box-shadow:0 0 0 3px rgba(37,99,235,.35)}

    .ca-prev{display:block;margin-top:4px;font-size:12px;color:#9a3412;min-height:16px}
    .ca-note{font-size:12.5px;color:#6b7280;padding:10px 14px;background:#fffaf5;border:1px solid #ffedd5;border-top:0}

    .ca-foot{position:sticky;bottom:0;z-index:20;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;background:#fff;border-top:1px solid #e5e7eb;padding:12px 0;margin-top:8px;box-shadow:0 -6px 12px -8px rgba(0,0,0,.15)}
    .ca-prog{flex:1;min-width:260px}
    .ca-prog-line{font-size:13px;color:#4b5563;margin-bottom:6px}
    .ca-prog-line b{color:#111827}
    .ca-bar{height:8px;border-radius:999px;background:#e5e7eb;overflow:hidden}
    .ca-bar i{display:block;height:100%;width:0;background:#2563eb;transition:width .2s}
    .ca-bar i.done{background:#16a34a}
    .ca-actions{display:flex;gap:10px;flex-wrap:wrap}
    .ca-btn{padding:11px 20px;border:0;border-radius:8px;font-weight:700;font-size:14px;cursor:pointer}
    .ca-btn.gray{background:#e5e7eb;color:#374151}
    .ca-btn.gray:hover{background:#d1d5db}
    .ca-btn.blue{background:#2563eb;color:#fff;box-shadow:0 1px 3px rgba(37,99,235,.4)}
    .ca-btn.blue:hover{background:#1d4ed8}
    .ca-btn:disabled{opacity:.5;cursor:not-allowed}
    .ca-msg{display:none;margin-bottom:12px}
</style>

<div class="ca-wrap">
    <div class="ca-head">
        <div>
            <h3>Cấu hình Ma trận Đáp án</h3>
            <p>Đề thi: <b>{{ $exam->title }}</b></p>
        </div>
        <div class="ca-step">Bước 3/3</div>
    </div>

    @if(session('error'))
        <div class="ca-alert err"><b>Cảnh báo!</b>{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="ca-alert info">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="ca-alert err" id="serverErrors">
            <b>Vui lòng bổ sung / sửa các thông tin sau ({{ $errors->count() }} lỗi):</b>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($noTopics)
        <div class="ca-alert warn">
            <b>Chưa có chuyên đề nào để chọn.</b>
            Hãy vào mục <i>Chuyên đề</i> tạo chuyên đề{{ $isMock ? '' : ' cho khối ' . $exam->grade }} trước, rồi quay lại trang này.
            Bạn vẫn có thể lưu nháp các đáp án, nhưng chưa thể công bố đề.
        </div>
    @endif

 

    <div class="ca-alert err ca-msg" id="clientMsg"></div>

    <form action="{{ route('teacher.exams.store-answers', $exam->id) }}" method="POST" id="answerForm" novalidate>
        @csrf

        {{-- ===================== PHẦN I ===================== --}}
        <div class="ca-part ca-p1" data-part="1">
            <div class="ca-part-title">
                <span>Phần I: Trắc nghiệm nhiều phương án lựa chọn (12 câu)</span>
                <small>Đã xong: <span data-count="1">0</span>/12</small>
            </div>
            <div class="ca-bulk">
                <span>Áp dụng cho cả phần:</span>
                <select class="ca-select" data-bulk-topic="1">
                    <option value="">-- Chuyên đề --</option>
                    @foreach($topics as $topic)
                        <option value="{{ $topic->id }}">{{ $topic->name }}{{ $isMock ? ' (Khối ' . $topic->grade . ')' : '' }}</option>
                    @endforeach
                </select>
                <select class="ca-select" data-bulk-level="1" style="max-width:170px">
                    <option value="">-- Mức độ --</option>
                    @foreach($levels as $level)
                        <option value="{{ $level }}">{{ $level }}</option>
                    @endforeach
                </select>
                <button type="button" class="ca-btn-sm" data-bulk-apply="1">Áp dụng</button>
            </div>
            <div class="ca-scroll">
                <table class="ca-table" style="min-width:720px">
                    <thead>
                        <tr>
                            <th class="c" style="width:56px">Câu</th>
                            <th class="c" style="width:190px">Đáp án đúng</th>
                            <th>Chuyên đề</th>
                            <th style="width:190px">Mức độ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 1; $i <= 12; $i++)
                        <tr data-row="1">
                            <td class="ca-q">{{ $i }}</td>
                            <td class="c{{ $bad("part1.$i.answer") }}">
                                <div class="ca-chips">
                                    @foreach(['A', 'B', 'C', 'D'] as $ans)
                                        <label class="ca-chip">
                                            <input type="radio" name="part1[{{ $i }}][answer]" value="{{ $ans }}" @checked($val("part1.$i.answer") === $ans)>
                                            <span>{{ $ans }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </td>
                            <td class="ca-tcol">
                                <select name="part1[{{ $i }}][topic_id]" class="ca-select{{ $bad("part1.$i.topic_id") }}">
                                    <option value="">-- Chọn chuyên đề --</option>
                                    @foreach($topics as $topic)
                                        <option value="{{ $topic->id }}" @selected((string) $val("part1.$i.topic_id") === (string) $topic->id)>{{ $topic->name }}{{ $isMock ? ' (Khối ' . $topic->grade . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="ca-lcol">
                                <select name="part1[{{ $i }}][level]" class="ca-select{{ $bad("part1.$i.level") }}">
                                    <option value="">-- Chọn mức độ --</option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level }}" @selected($val("part1.$i.level") === $level)>{{ $level }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===================== PHẦN II ===================== --}}
        <div class="ca-part ca-p2" data-part="2">
            <div class="ca-part-title">
                <span>Phần II: Trắc nghiệm Đúng-Sai (4 câu)</span>
                <small>Đã xong: <span data-count="2">0</span>/4</small>
            </div>
            <div class="ca-bulk">
                <span>Áp dụng cho cả phần:</span>
                <select class="ca-select" data-bulk-topic="2">
                    <option value="">-- Chuyên đề --</option>
                    @foreach($topics as $topic)
                        <option value="{{ $topic->id }}">{{ $topic->name }}{{ $isMock ? ' (Khối ' . $topic->grade . ')' : '' }}</option>
                    @endforeach
                </select>
                <select class="ca-select" data-bulk-level="2" style="max-width:170px">
                    <option value="">-- Mức độ --</option>
                    @foreach($levels as $level)
                        <option value="{{ $level }}">{{ $level }}</option>
                    @endforeach
                </select>
                <button type="button" class="ca-btn-sm" data-bulk-apply="2">Áp dụng</button>
            </div>
            <div class="ca-scroll">
                <table class="ca-table" style="min-width:860px">
                    <thead>
                        <tr>
                            <th class="c" style="width:56px">Câu</th>
                            <th class="c">Ý a</th>
                            <th class="c">Ý b</th>
                            <th class="c">Ý c</th>
                            <th class="c">Ý d</th>
                            <th>Chuyên đề chung</th>
                            <th style="width:190px">Mức độ chung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 1; $i <= 4; $i++)
                        <tr data-row="2">
                            <td class="ca-q">{{ $i }}</td>
                            @foreach(['a', 'b', 'c', 'd'] as $sub)
                                @php $cur = $val("part2.$i.$sub"); @endphp
                                <td class="c">
                                    <select name="part2[{{ $i }}][{{ $sub }}]" data-tf class="ca-select sm{{ $bad("part2.$i.$sub") }}" data-v="{{ $cur }}">
                                        <option value="">-</option>
                                        <option value="Đ" @selected($cur === 'Đ')>Đ</option>
                                        <option value="S" @selected($cur === 'S')>S</option>
                                    </select>
                                </td>
                            @endforeach
                            <td class="ca-tcol">
                                <select name="part2[{{ $i }}][topic_id]" class="ca-select{{ $bad("part2.$i.topic_id") }}">
                                    <option value="">-- Chọn chuyên đề --</option>
                                    @foreach($topics as $topic)
                                        <option value="{{ $topic->id }}" @selected((string) $val("part2.$i.topic_id") === (string) $topic->id)>{{ $topic->name }}{{ $isMock ? ' (Khối ' . $topic->grade . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="ca-lcol">
                                <select name="part2[{{ $i }}][level]" class="ca-select{{ $bad("part2.$i.level") }}">
                                    <option value="">-- Chọn mức độ --</option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level }}" @selected($val("part2.$i.level") === $level)>{{ $level }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===================== PHẦN III ===================== --}}
        <div class="ca-part ca-p3" data-part="3">
            <div class="ca-part-title">
                <span>Phần III: Trắc nghiệm trả lời ngắn (6 câu)</span>
                <small>Đã xong: <span data-count="3">0</span>/6</small>
            </div>
            <div class="ca-bulk">
                <span>Áp dụng cho cả phần:</span>
                <select class="ca-select" data-bulk-topic="3">
                    <option value="">-- Chuyên đề --</option>
                    @foreach($topics as $topic)
                        <option value="{{ $topic->id }}">{{ $topic->name }}{{ $isMock ? ' (Khối ' . $topic->grade . ')' : '' }}</option>
                    @endforeach
                </select>
                <select class="ca-select" data-bulk-level="3" style="max-width:170px">
                    <option value="">-- Mức độ --</option>
                    @foreach($levels as $level)
                        <option value="{{ $level }}">{{ $level }}</option>
                    @endforeach
                </select>
                <button type="button" class="ca-btn-sm" data-bulk-apply="3">Áp dụng</button>
            </div>
            <div class="ca-scroll">
                <table class="ca-table" style="min-width:800px">
                    <thead>
                        <tr>
                            <th class="c" style="width:56px">Câu</th>
                            <th class="c" style="width:200px">Đáp án số</th>
                            <th class="c" style="width:150px">Làm tròn</th>
                            <th>Chuyên đề</th>
                            <th style="width:190px">Mức độ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 1; $i <= 6; $i++)
                        @php
                            $rv = (string) $val("part3.$i.rounding", '2');
                            if (! in_array($rv, ['0', '1', '2'], true)) { $rv = '2'; }
                        @endphp
                        <tr data-row="3">
                            <td class="ca-q">{{ $i }}</td>
                            <td class="c">
                                <input type="text" inputmode="decimal" autocomplete="off"
                                       name="part3[{{ $i }}][answer]" data-ans
                                       value="{{ $val("part3.$i.answer") }}"
                                       class="ca-input{{ $bad("part3.$i.answer") }}" style="text-align:center"
                                       placeholder="VD: 3,14 hoặc -2">
                                <span class="ca-prev" data-prev></span>
                            </td>
                            <td>
                                <select name="part3[{{ $i }}][rounding]" data-round class="ca-select{{ $bad("part3.$i.rounding") }}">
                                    <option value="0" @selected($rv === '0')>Số nguyên</option>
                                    <option value="1" @selected($rv === '1')>1 chữ số thập phân</option>
                                    <option value="2" @selected($rv === '2')>2 chữ số thập phân</option>
                                </select>
                            </td>
                            <td class="ca-tcol">
                                <select name="part3[{{ $i }}][topic_id]" class="ca-select{{ $bad("part3.$i.topic_id") }}">
                                    <option value="">-- Chọn chuyên đề --</option>
                                    @foreach($topics as $topic)
                                        <option value="{{ $topic->id }}" @selected((string) $val("part3.$i.topic_id") === (string) $topic->id)>{{ $topic->name }}{{ $isMock ? ' (Khối ' . $topic->grade . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="ca-lcol">
                                <select name="part3[{{ $i }}][level]" class="ca-select{{ $bad("part3.$i.level") }}">
                                    <option value="">-- Chọn mức độ --</option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level }}" @selected($val("part3.$i.level") === $level)>{{ $level }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="ca-note">
                Nhập đáp án bằng dấu phẩy hoặc dấu chấm (3,14 hay 3.14 đều được). Đáp án chuẩn sẽ được làm tròn theo mức đã chọn
                (dòng chữ nhỏ dưới ô nhập cho biết kết quả sau khi làm tròn). Học sinh phải tự làm tròn đúng:
                nếu đáp án chuẩn là 3,14 mà học sinh nhập 3,142 thì tính là sai.
            </div>
        </div>

        {{-- ===================== THANH TIẾN ĐỘ + NÚT ===================== --}}
        <div class="ca-foot">
            <div class="ca-prog">
                <div class="ca-prog-line">Hoàn thành: <b id="totalDone">0</b>/22 câu</div>
                <div class="ca-bar"><i id="totalBar"></i></div>
            </div>
            <div class="ca-actions">
                <button type="submit" name="action" value="draft" class="ca-btn gray">Lưu nháp</button>
                <button type="submit" name="action" value="publish" id="btnPublish" class="ca-btn blue" @disabled($noTopics)>Lưu và Mở đề</button>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    var form = document.getElementById('answerForm');
    if (!form) return;

    var NUM_RE = /^-?\d+([.,]\d+)?$/;
    var dirty = false, submitting = false;

    function val(el) { return el ? (el.value || '').trim() : ''; }

    // Làm tròn "half away from zero", tránh lỗi nhị phân của số thực (vd 1.005)
    function roundKey(text, d) {
        var s = text.replace(',', '.');
        var x = parseFloat(s);
        if (!isFinite(x)) return null;
        var sign = x < 0 ? -1 : 1;
        var r = Math.round(Number(Math.abs(x) + 'e' + d));
        var out = sign * Number(r + 'e-' + d);
        return (out === 0 ? 0 : out).toFixed(d).replace('.', ',');
    }

    function rowState(tr) {
        var part = tr.getAttribute('data-row');
        var ok = val(tr.querySelector('select[name$="[topic_id]"]')) !== ''
              && val(tr.querySelector('select[name$="[level]"]')) !== '';
        if (part === '1') {
            ok = ok && !!tr.querySelector('input[type=radio]:checked');
        } else if (part === '2') {
            tr.querySelectorAll('select[data-tf]').forEach(function (s) { if (val(s) === '') ok = false; });
        } else {
            ok = ok && NUM_RE.test(val(tr.querySelector('input[data-ans]')));
        }
        return ok;
    }

    function updatePreview(tr) {
        var inp = tr.querySelector('input[data-ans]');
        if (!inp) return;
        var out = tr.querySelector('[data-prev]');
        var v = val(inp);
        if (v === '') { out.textContent = ''; return; }
        if (!NUM_RE.test(v)) { out.textContent = 'Chỉ nhập số, vd: 3,14 hoặc -2'; return; }
        var d = parseInt(tr.querySelector('select[data-round]').value, 10);
        out.textContent = 'Đáp án chuẩn: ' + roundKey(v, d);
    }

    function refresh() {
        var total = 0;
        [1, 2, 3].forEach(function (p) {
            var rows = form.querySelectorAll('tr[data-row="' + p + '"]');
            var n = 0;
            rows.forEach(function (tr) {
                var ok = rowState(tr);
                if (ok) n++;
                if (ok) tr.classList.remove('ca-incomplete');
            });
            total += n;
            var c = form.querySelector('[data-count="' + p + '"]');
            if (c) c.textContent = n;
        });
        document.getElementById('totalDone').textContent = total;
        var bar = document.getElementById('totalBar');
        bar.style.width = (total / 22 * 100) + '%';
        bar.className = total === 22 ? 'done' : '';
    }

    function paintTF(sel) { sel.setAttribute('data-v', sel.value); }

    form.addEventListener('input', function (e) {
        dirty = true;
        var tr = e.target.closest('tr[data-row]');
        if (tr) updatePreview(tr);
        e.target.classList.remove('ca-bad');
        var td = e.target.closest('td.ca-bad'); if (td) td.classList.remove('ca-bad');
        refresh();
    });
    form.addEventListener('change', function (e) {
        dirty = true;
        if (e.target.matches('select[data-tf]')) paintTF(e.target);
        var tr = e.target.closest('tr[data-row]');
        if (tr) updatePreview(tr);
        e.target.classList.remove('ca-bad');
        var td = e.target.closest('td.ca-bad'); if (td) td.classList.remove('ca-bad');
        refresh();
    });

    // Áp dụng chuyên đề / mức độ hàng loạt cho một phần
    form.querySelectorAll('[data-bulk-apply]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var p = btn.getAttribute('data-bulk-apply');
            var t = form.querySelector('[data-bulk-topic="' + p + '"]').value;
            var l = form.querySelector('[data-bulk-level="' + p + '"]').value;
            if (t === '' && l === '') { showMsg('Hãy chọn chuyên đề và/hoặc mức độ trước khi bấm "Áp dụng".'); return; }
            hideMsg();
            form.querySelectorAll('tr[data-row="' + p + '"]').forEach(function (tr) {
                if (t !== '') tr.querySelector('select[name$="[topic_id]"]').value = t;
                if (l !== '') tr.querySelector('select[name$="[level]"]').value = l;
            });
            dirty = true;
            refresh();
        });
    });

    function showMsg(text) {
        var m = document.getElementById('clientMsg');
        m.textContent = text;
        m.style.display = 'block';
        m.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function hideMsg() { document.getElementById('clientMsg').style.display = 'none'; }

    form.addEventListener('submit', function (e) {
        if (submitting) { e.preventDefault(); return; }
        var submitter = e.submitter;
        var action = submitter && submitter.value ? submitter.value : 'draft';

        if (action === 'publish') {
            var first = null, missing = 0;
            form.querySelectorAll('tr[data-row]').forEach(function (tr) {
                if (rowState(tr)) { tr.classList.remove('ca-incomplete'); return; }
                tr.classList.add('ca-incomplete');
                missing++;
                if (!first) first = tr;
            });
            if (missing > 0) {
                e.preventDefault();
                showMsg('Còn ' + missing + ' câu chưa đủ thông tin (đáp án, chuyên đề, mức độ). Các câu cần bổ sung được tô vàng. Nếu chưa muốn nhập đủ, hãy bấm "Lưu nháp".');
                first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if (!window.confirm('Công bố đề? Sau khi công bố và có học sinh nộp bài, bạn sẽ không thể cấu hình lại đáp án ở bước này.')) {
                e.preventDefault();
                return;
            }
        }

        // Nút bị vô hiệu hoá sẽ không gửi giá trị name=value, nên gửi kèm trường ẩn.
        var hidden = document.createElement('input');
        hidden.type = 'hidden'; hidden.name = 'action'; hidden.value = action;
        form.appendChild(hidden);

        submitting = true; dirty = false;
        setTimeout(function () {
            form.querySelectorAll('button[type=submit]').forEach(function (b) { b.disabled = true; });
        }, 0);
    });

    window.addEventListener('beforeunload', function (e) {
        if (dirty && !submitting) { e.preventDefault(); e.returnValue = ''; }
    });

    // Khởi tạo
    form.querySelectorAll('select[data-tf]').forEach(paintTF);
    form.querySelectorAll('tr[data-row="3"]').forEach(updatePreview);
    refresh();

    // Nếu server trả về lỗi: cuộn tới ô lỗi đầu tiên
    var firstBad = form.querySelector('.ca-bad');
    if (firstBad) {
        var tr = firstBad.closest('tr') || firstBad;
        setTimeout(function () { tr.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 150);
    }
})();
</script>
@endsection