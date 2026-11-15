@extends('layouts.student')

@section('title', 'Đang làm bài: ' . $exam->title)

@section('content')
@php
    // Cấu trúc đề: 12 câu Phần I, 4 câu Phần II (mỗi câu 4 ý), 6 câu Phần III
    $n1 = 12; $n2 = 4; $n3 = 6;
@endphp

<style>
    /* Trang tự chứa CSS để không phụ thuộc việc biên dịch lại Tailwind */
    .tk-err { margin-bottom: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 14px; font-weight: 500; border-radius: 10px; padding: 10px 16px; }
    .tk-wrap { display: grid; grid-template-columns: 7fr 5fr; gap: 24px; height: calc(100vh - 8rem); min-height: 520px; }
    .tk-wrap.has-err { height: calc(100vh - 12rem); }
    .tk-card { display: flex; flex-direction: column; min-height: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); overflow: hidden; }
    .tk-pane { display: flex; }

    .tk-tabs { display: none; align-items: center; gap: 8px; margin-bottom: 12px; }
    .tk-tab { flex: 1; padding: 9px 12px; border: 1px solid #cbd5e1; background: #fff; border-radius: 10px; font: inherit; font-size: 14px; font-weight: 600; color: #475569; cursor: pointer; }
    .tk-tab.is-on { background: #2563eb; border-color: #2563eb; color: #fff; }

    /* Cột đề */
    .tk-bar { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 20px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; }
    .tk-bar h3 { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .tk-dl { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border: 1px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: background .15s, color .15s; }
    .tk-dl:hover { background: #2563eb; color: #fff; }
    .tk-dl svg { width: 16px; height: 16px; }
    .tk-pdf { position: relative; flex: 1; background: #f1f5f9; }
    .tk-pdf iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }

    /* Đồng hồ */
    .tk-timer { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 8px; border: 1px solid #fecdd3; background: #fff1f2; color: #e11d48; font-weight: 700; font-size: 15px; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .tk-timer svg { width: 16px; height: 16px; animation: tkPulse 1.6s ease-in-out infinite; }
    .tk-timer.warn { background: #fee2e2; border-color: #fca5a5; }
    .tk-timer.over { background: #dc2626; border-color: #b91c1c; color: #fff; }
    .tk-timer.over svg { animation: none; }
    @keyframes tkPulse { 50% { opacity: .35; } }

    /* Cột phiếu trả lời */
    .tk-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 20px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; }
    .tk-head h3 { margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; }
    .tk-prog { margin: 2px 0 0; font-size: 12.5px; color: #64748b; }
    .tk-prog b { color: #2563eb; }

    .tk-nav { display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: center; padding: 10px 20px; border-bottom: 1px solid #e2e8f0; background: #fff; flex-shrink: 0; }
    .tk-ng { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; }
    .tk-ng > span { font-size: 11px; font-weight: 700; color: #94a3b8; margin-right: 4px; letter-spacing: .04em; }
    .tk-dot { width: 24px; height: 24px; padding: 0; border: 1px solid #cbd5e1; background: #fff; border-radius: 6px; font: inherit; font-size: 11px; font-weight: 600; color: #64748b; cursor: pointer; }
    .tk-dot:hover { border-color: #2563eb; color: #2563eb; }
    .tk-dot.done { background: #2563eb; border-color: #2563eb; color: #fff; }
    .tk-dot.part { background: #fef3c7; border-color: #f59e0b; color: #92400e; }

    .tk-scroll { flex: 1; min-height: 0; overflow-y: auto; background: #f8fafc; padding: 20px; display: flex; flex-direction: column; gap: 28px; }
    .tk-scroll::-webkit-scrollbar { width: 6px; }
    .tk-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .tk-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    .tk-sec { display: flex; align-items: center; gap: 14px; margin-bottom: 12px; }
    .tk-badge { background: #2563eb; color: #fff; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 8px; letter-spacing: .04em; }
    .tk-line { flex: 1; height: 1px; background: #e2e8f0; }
    .tk-hint { margin: 0 0 10px; font-size: 13px; font-weight: 500; color: #dc2626; }
    .tk-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; }
    .tk-stack { display: flex; flex-direction: column; gap: 14px; }
    .tk-q { scroll-margin-top: 12px; }
    .tk-qt { margin: 0 0 10px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9; font-size: 15px; font-weight: 700; color: #1e293b; }
    .tk-qt i { color: #dc2626; font-style: normal; margin-left: 4px; }

    .tk-p1h, .tk-p1r { display: grid; grid-template-columns: 64px repeat(4, 1fr); gap: 4px; align-items: center; }
    .tk-p1h { margin-bottom: 8px; text-align: center; font-size: 13px; font-weight: 600; color: #64748b; }
    .tk-p1r { border-radius: 8px; scroll-margin-top: 12px; }
    .tk-p1r:nth-child(odd) { background: #f8fafc; }
    .tk-lbl { padding-left: 8px; font-size: 13px; font-weight: 700; color: #334155; }

    .tk-p2h, .tk-p2r { display: grid; grid-template-columns: 1fr 72px 72px; gap: 4px; align-items: center; }
    .tk-p2h { margin-bottom: 6px; text-align: center; font-size: 13px; font-weight: 600; color: #64748b; }
    .tk-p2r { border-radius: 8px; }
    .tk-p2r:nth-child(odd) { background: #f8fafc; }
    .tk-sub { padding-left: 8px; font-size: 14px; font-weight: 600; color: #334155; }

    /* Cả ô là vùng bấm, không chỉ vòng tròn radio */
    .tk-pick { display: flex; align-items: center; justify-content: center; height: 42px; border-radius: 8px; cursor: pointer; transition: background .12s; }
    .tk-pick:hover { background: #dbeafe; }
    .tk-pick:has(input:checked) { background: #dbeafe; }
    .tk-pick input { width: 20px; height: 20px; margin: 0; accent-color: #2563eb; cursor: pointer; }

    .tk-ans { width: 100%; padding: 10px 4px; border: 0; border-bottom: 2px solid #e2e8f0; background: transparent; font: inherit; font-size: 15px; font-weight: 600; color: #0f172a; outline: none; transition: border-color .15s; }
    .tk-ans::placeholder { color: #94a3b8; font-weight: 400; }
    .tk-ans:focus { border-bottom-color: #2563eb; }

    .tk-foot { padding: 16px 20px; border-top: 1px solid #f1f5f9; background: #fff; flex-shrink: 0; }
    .tk-submit { width: 100%; padding: 13px 16px; border: 0; border-radius: 12px; background: #2563eb; color: #fff; font: inherit; font-size: 14px; font-weight: 700; letter-spacing: .02em; cursor: pointer; transition: background .15s; }
    .tk-submit:hover { background: #1d4ed8; }
    .tk-submit:disabled { background: #93c5fd; cursor: not-allowed; }

    /* Hộp xác nhận nộp bài */
    .tk-modal { position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(15, 23, 42, .55); }
    .tk-modal[hidden] { display: none; }
    .tk-dialog { width: 100%; max-width: 420px; padding: 24px; background: #fff; border-radius: 16px; box-shadow: 0 20px 50px rgba(15, 23, 42, .3); }
    .tk-dialog h4 { margin: 0 0 8px; font-size: 18px; font-weight: 700; color: #0f172a; }
    .tk-dialog p { margin: 0 0 6px; font-size: 14px; line-height: 1.6; color: #475569; }
    .tk-dialog .warnline { color: #b45309; font-weight: 600; }
    .tk-dialog .okline { color: #15803d; font-weight: 600; }
    .tk-dialog .acts { display: flex; gap: 10px; margin-top: 20px; }
    .tk-dialog button { flex: 1; padding: 11px 12px; border-radius: 10px; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; }
    .tk-b-cancel { border: 1px solid #cbd5e1; background: #fff; color: #334155; }
    .tk-b-cancel:hover { background: #f8fafc; }
    .tk-b-ok { border: 0; background: #2563eb; color: #fff; }
    .tk-b-ok:hover { background: #1d4ed8; }
    .tk-b-ok:disabled, .tk-b-cancel:disabled { opacity: .6; cursor: not-allowed; }

    .tk-toast { position: fixed; top: 80px; left: 50%; transform: translateX(-50%); z-index: 70; padding: 10px 18px; border-radius: 10px; background: #0f172a; color: #fff; font-size: 14px; font-weight: 600; box-shadow: 0 10px 30px rgba(15, 23, 42, .25); }
    .tk-toast[hidden] { display: none; }

    .tk-dot:focus-visible, .tk-pick:focus-within, .tk-tab:focus-visible, .tk-submit:focus-visible, .tk-dialog button:focus-visible { outline: 2px solid #93c5fd; outline-offset: 2px; }

    /* Cửa sổ hẹp: chuyển qua lại giữa Đề và Phiếu trả lời */
    @media (max-width: 1179px) {
        .tk-tabs { display: flex; }
        .tk-wrap, .tk-wrap.has-err { display: block; height: auto; }
        .tk-pane { height: calc(100vh - 14rem); min-height: 460px; }
        .tk-pane:not(.is-on) { display: none; }
        .tk-head .tk-timer { display: none; } /* đã có đồng hồ ở thanh chuyển Đề/Phiếu */
    }
</style>

@if(session('error'))
    <div class="tk-err">{{ session('error') }}</div>
@endif

<!-- Thanh chuyển Đề / Phiếu (chỉ hiện khi cửa sổ hẹp) kèm đồng hồ để luôn thấy giờ -->
<div class="tk-tabs">
    <button type="button" class="tk-tab is-on" data-pane="pane-exam">Đề thi</button>
    <button type="button" class="tk-tab" data-pane="pane-sheet">Phiếu trả lời</button>
    <span class="tk-timer" data-timer-box><span data-timer-text>--:--:--</span></span>
</div>

<div class="tk-wrap {{ session('error') ? 'has-err' : '' }}">

    <!-- CỘT TRÁI: ĐỀ THI PDF -->
    <section id="pane-exam" class="tk-card tk-pane is-on" style="flex-direction: column;">
        <div class="tk-bar">
            <h3>Đề thi: {{ $exam->title }}</h3>
            <a class="tk-dl" href="{{ route('student.exams.pdf', ['exam' => $exam->id, 'download' => 1]) }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Tải bản PDF
            </a>
        </div>
        <div class="tk-pdf">
            <iframe title="Đề thi {{ $exam->title }}" src="{{ route('student.exams.pdf', $exam->id) }}#toolbar=0"></iframe>
        </div>
    </section>

    <!-- CỘT PHẢI: PHIẾU TRẢ LỜI -->
    <form id="exam-form" class="tk-card tk-pane" style="flex-direction: column;"
          action="{{ route('student.exams.submit', ['exam' => $exam->id, 'attempt' => $attempt->id]) }}" method="POST" novalidate>
        @csrf

        <div class="tk-head">
            <div>
                <h3>Phiếu trả lời</h3>
                <p class="tk-prog">Đã làm <b id="prog-done">0</b>/{{ $n1 + $n2 + $n3 }} câu</p>
            </div>
            <div class="tk-timer" id="countdown-timer" data-timer-box>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span data-timer-text>00:00:00</span>
            </div>
        </div>

        <!-- Bảng điều hướng: bấm vào số câu để nhảy tới câu đó -->
        <div class="tk-nav" aria-label="Điều hướng câu hỏi">
            <div class="tk-ng"><span>I</span>
                @for($i = 1; $i <= $n1; $i++)<button type="button" class="tk-dot" id="nav-1-{{ $i }}" data-go="q-1-{{ $i }}">{{ $i }}</button>@endfor
            </div>
            <div class="tk-ng"><span>II</span>
                @for($i = 1; $i <= $n2; $i++)<button type="button" class="tk-dot" id="nav-2-{{ $i }}" data-go="q-2-{{ $i }}">{{ $i }}</button>@endfor
            </div>
            <div class="tk-ng"><span>III</span>
                @for($i = 1; $i <= $n3; $i++)<button type="button" class="tk-dot" id="nav-3-{{ $i }}" data-go="q-3-{{ $i }}">{{ $i }}</button>@endfor
            </div>
        </div>

        <div class="tk-scroll">

            <!-- PHẦN I -->
            <div>
                <div class="tk-sec"><div class="tk-badge">PHẦN I</div><div class="tk-line"></div></div>
                <div class="tk-box">
                    <p class="tk-hint">* Chọn 1 đáp án đúng duy nhất</p>
                    <div class="tk-p1h"><div></div><div>A</div><div>B</div><div>C</div><div>D</div></div>
                    <div>
                        @for($i = 1; $i <= $n1; $i++)
                            <div class="tk-p1r" id="q-1-{{ $i }}">
                                <div class="tk-lbl">CÂU {{ $i }}</div>
                                @foreach(['A', 'B', 'C', 'D'] as $ans)
                                    <label class="tk-pick"><input type="radio" name="part1[{{ $i }}]" value="{{ $ans }}" aria-label="Câu {{ $i }}, đáp án {{ $ans }}"></label>
                                @endforeach
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- PHẦN II -->
            <div>
                <div class="tk-sec"><div class="tk-badge">PHẦN II</div><div class="tk-line"></div></div>
                <p class="tk-hint">* Mỗi câu có 4 ý, chọn Đúng hoặc Sai cho từng ý</p>
                <div class="tk-stack">
                    @for($i = 1; $i <= $n2; $i++)
                        <div class="tk-box tk-q" id="q-2-{{ $i }}">
                            <p class="tk-qt">CÂU {{ $i }}<i>*</i></p>
                            <div class="tk-p2h"><div></div><div>Đúng</div><div>Sai</div></div>
                            <div>
                                @foreach(['a', 'b', 'c', 'd'] as $sub)
                                    <div class="tk-p2r">
                                        <div class="tk-sub">{{ $sub }})</div>
                                        <label class="tk-pick"><input type="radio" name="part2[{{ $i }}][{{ $sub }}]" value="Đ" aria-label="Câu {{ $i }} ý {{ $sub }}, Đúng"></label>
                                        <label class="tk-pick"><input type="radio" name="part2[{{ $i }}][{{ $sub }}]" value="S" aria-label="Câu {{ $i }} ý {{ $sub }}, Sai"></label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- PHẦN III -->
            <div>
                <div class="tk-sec"><div class="tk-badge">PHẦN III</div><div class="tk-line"></div></div>
                <p class="tk-hint">* Nhập một số. Dấu thập phân là dấu phẩy hoặc dấu chấm đều được (ví dụ 2,5 hoặc 2.5)</p>
                <div class="tk-stack">
                    @for($i = 1; $i <= $n3; $i++)
                        <div class="tk-box tk-q" id="q-3-{{ $i }}">
                            <p class="tk-qt">CÂU {{ $i }}<i>*</i></p>
                            <input type="text" name="part3[{{ $i }}]" class="tk-ans" autocomplete="off" placeholder="Nhập câu trả lời của bạn..." aria-label="Câu {{ $i }}">
                        </div>
                    @endfor
                </div>
            </div>

        </div>

        <div class="tk-foot">
            <button type="button" id="open-confirm" class="tk-submit">NỘP BÀI THI</button>
        </div>
    </form>
</div>

<!-- Hộp xác nhận nộp bài -->
<div class="tk-modal" id="confirm-modal" hidden role="dialog" aria-modal="true" aria-labelledby="confirm-title">
    <div class="tk-dialog">
        <h4 id="confirm-title">Nộp bài thi?</h4>
        <div id="confirm-body"></div>
        <p>Sau khi nộp, bạn không sửa được bài của lượt làm này.</p>
        <div class="acts">
            <button type="button" id="confirm-cancel" class="tk-b-cancel">Quay lại làm bài</button>
            <button type="button" id="confirm-ok" class="tk-b-ok">Nộp bài</button>
        </div>
    </div>
</div>

<div class="tk-toast" id="toast" hidden></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const N1 = {{ $n1 }}, N2 = {{ $n2 }}, N3 = {{ $n3 }};
        const form = document.getElementById('exam-form');
        const modal = document.getElementById('confirm-modal');
        const toast = document.getElementById('toast');
        const openBtn = document.getElementById('open-confirm');
        const okBtn = document.getElementById('confirm-ok');
        const cancelBtn = document.getElementById('confirm-cancel');

        let submitting = false;
        let timerInterval = null;

        // ------------------------------------------------------------
        // Chuyển Đề / Phiếu trả lời khi cửa sổ hẹp
        // ------------------------------------------------------------
        const paneExam = document.getElementById('pane-exam');
        document.querySelectorAll('.tk-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                const showSheet = tab.dataset.pane === 'pane-sheet';
                paneExam.classList.toggle('is-on', !showSheet);
                form.classList.toggle('is-on', showSheet);
                document.querySelectorAll('.tk-tab').forEach(function (t) { t.classList.toggle('is-on', t === tab); });
            });
        });

        // ------------------------------------------------------------
        // Theo dõi tiến độ làm bài
        // ------------------------------------------------------------
        function answerState() {
            let done = 0;
            const missing = [];   // chưa trả lời gì
            const partial = [];   // Phần II mới chọn một phần số ý

            for (let i = 1; i <= N1; i++) {
                const ok = !!form.querySelector('input[name="part1[' + i + ']"]:checked');
                mark('nav-1-' + i, ok ? 'done' : '');
                if (ok) done++; else missing.push('I.' + i);
            }
            for (let i = 1; i <= N2; i++) {
                const cnt = ['a', 'b', 'c', 'd'].filter(function (s) {
                    return form.querySelector('input[name="part2[' + i + '][' + s + ']"]:checked');
                }).length;
                mark('nav-2-' + i, cnt === 4 ? 'done' : (cnt > 0 ? 'part' : ''));
                if (cnt === 4) done++; else if (cnt > 0) partial.push('II.' + i); else missing.push('II.' + i);
            }
            for (let i = 1; i <= N3; i++) {
                const el = form.querySelector('input[name="part3[' + i + ']"]');
                const ok = el && el.value.trim() !== '';
                mark('nav-3-' + i, ok ? 'done' : '');
                if (ok) done++; else missing.push('III.' + i);
            }
            document.getElementById('prog-done').textContent = done;
            return { done: done, missing: missing, partial: partial };
        }

        function mark(id, cls) {
            const el = document.getElementById(id);
            el.classList.remove('done', 'part');
            if (cls) el.classList.add(cls);
        }

        form.addEventListener('change', answerState);
        form.addEventListener('input', answerState);
        answerState();

        // Bấm số câu trên bảng điều hướng: cuộn tới câu đó
        document.querySelectorAll('[data-go]').forEach(function (b) {
            b.addEventListener('click', function () {
                const target = document.getElementById(b.dataset.go);
                if (!target) return;
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const input = target.querySelector('input[type="text"]');
                if (input) input.focus({ preventScroll: true });
            });
        });

        // Cảnh báo khi vô tình đóng hoặc tải lại trang (đáp án đang chọn sẽ mất, đồng hồ vẫn chạy)
        window.addEventListener('beforeunload', function (e) {
            if (submitting) return;
            if (answerState().done > 0 || form.querySelector('input[type="radio"]:checked')) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // ------------------------------------------------------------
        // Hộp xác nhận nộp bài
        // ------------------------------------------------------------
        function openConfirm() {
            if (submitting) return;
            const s = answerState();
            const body = document.getElementById('confirm-body');
            body.innerHTML = '';

            const add = function (text, cls) {
                const p = document.createElement('p');
                if (cls) p.className = cls;
                p.textContent = text;
                body.appendChild(p);
            };

            if (s.missing.length === 0 && s.partial.length === 0) {
                add('Bạn đã trả lời đủ ' + (N1 + N2 + N3) + ' câu.', 'okline');
            } else {
                if (s.missing.length) add('Chưa trả lời ' + s.missing.length + ' câu: ' + s.missing.join(', ') + '.', 'warnline');
                if (s.partial.length) add('Làm dở (chưa chọn đủ 4 ý) ở Phần II: câu ' + s.partial.map(function (x) { return x.replace('II.', ''); }).join(', ') + '.', 'warnline');
            }
            modal.hidden = false;
            cancelBtn.focus();
        }

        function closeConfirm() { modal.hidden = true; }

        openBtn.addEventListener('click', openConfirm);
        cancelBtn.addEventListener('click', closeConfirm);
        modal.addEventListener('click', function (e) { if (e.target === modal) closeConfirm(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeConfirm(); });

        // Bấm Enter trong ô Phần III cũng không được nộp bài ngay: đưa về hộp xác nhận
        form.addEventListener('submit', function (e) {
            if (submitting) return;
            e.preventDefault();
            openConfirm();
        });

        function lockButtons() {
            [openBtn, okBtn, cancelBtn].forEach(function (b) { b.disabled = true; });
        }

        function doSubmit() {
            if (submitting) return;
            submitting = true;
            clearInterval(timerInterval);
            lockButtons();
            // form.submit() không kích hoạt sự kiện submit nên không bị hộp xác nhận chặn
            form.submit();
        }

        okBtn.addEventListener('click', doSubmit);

        // ------------------------------------------------------------
        // ĐỒNG HỒ ĐẾM NGƯỢC VÀ TỰ ĐỘNG NỘP BÀI KHI HẾT GIỜ
        // (giữ nguyên cách tính: mốc kết thúc theo Date.now() từ số giây do máy chủ tính)
        // ------------------------------------------------------------
        const timerBoxes = document.querySelectorAll('[data-timer-box]');
        const timerTexts = document.querySelectorAll('[data-timer-text]');
        const initialSeconds = parseInt("{{ $remainingSeconds ?? 0 }}", 10);
        const endAt = Date.now() + initialSeconds * 1000;

        let warned5 = false, warned1 = false;
        const pad = (n) => n.toString().padStart(2, '0');

        function setTimerText(t) { timerTexts.forEach(function (el) { el.textContent = t; }); }
        function timerClass(cls) { timerBoxes.forEach(function (el) { el.classList.add(cls); }); }

        function showToast(msg) {
            toast.textContent = msg;
            toast.hidden = false;
            setTimeout(function () { toast.hidden = true; }, 4000);
        }

        function autoSubmit() {
            if (submitting) return;
            closeConfirm();
            setTimerText('HẾT GIỜ! Đang nộp bài...');
            timerBoxes.forEach(function (el) { el.classList.remove('warn'); el.classList.add('over'); });
            doSubmit();
        }

        function tick() {
            const remaining = Math.max(0, Math.ceil((endAt - Date.now()) / 1000));

            if (remaining <= 0) {
                autoSubmit();
                return;
            }

            const h = Math.floor(remaining / 3600);
            const m = Math.floor((remaining % 3600) / 60);
            const s = remaining % 60;
            setTimerText(pad(h) + ':' + pad(m) + ':' + pad(s));

            if (remaining <= 300) {
                timerClass('warn');
                if (!warned5) { warned5 = true; showToast('Còn 5 phút. Hãy kiểm tra lại các câu chưa làm.'); }
            }
            if (remaining <= 60 && !warned1) { warned1 = true; showToast('Còn 1 phút, bài sẽ tự nộp khi hết giờ.'); }
        }

        tick();
        timerInterval = setInterval(tick, 500);
    });
</script>
@endsection