@extends('layouts.teacher')

@section('title', 'Tải file PDF (Bước 2/3)')

@section('content')
    <style>
        /* Thanh 3 bước tạo đề (dùng chung kiểu với bước 1) */
        .cx-steps { display: flex; align-items: center; gap: 10px; margin-bottom: 22px; font-size: 13px; color: #9ca3af; }
        .cx-step { display: flex; align-items: center; gap: 8px; white-space: nowrap; }
        .cx-dot { width: 26px; height: 26px; border-radius: 9999px; display: flex; align-items: center; justify-content: center;
                  font-size: 12px; font-weight: 700; background: #e5e7eb; color: #6b7280; flex-shrink: 0; }
        .cx-step.on { color: #1f2937; font-weight: 600; }
        .cx-step.on .cx-dot { background: #2563eb; color: #fff; }
        .cx-step.done { color: #4b5563; }
        .cx-step.done .cx-dot { background: #16a34a; color: #fff; }
        .cx-line { flex: 1; height: 2px; background: #e5e7eb; min-width: 14px; }
        .cx-line.done { background: #16a34a; }
        @media (max-width: 640px) { .cx-step:not(.on) .cx-label { display: none; } }
        /* Khung thả file khi đang kéo file vào */
        #drop-zone.cx-drag { background: #dbeafe; border-color: #2563eb; }
    </style>

    <div class="max-w-4xl bg-white p-6 md:p-8 rounded-xl shadow-sm border border-gray-100 mx-auto">

        <!-- Các bước tạo đề -->
        <div class="cx-steps" aria-label="Các bước tạo đề thi">
            <div class="cx-step done"><span class="cx-dot">&#10003;</span><span class="cx-label">Thông tin chung</span></div>
            <span class="cx-line done"></span>
            <div class="cx-step on"><span class="cx-dot">2</span><span class="cx-label">Tải đề PDF</span></div>
            <span class="cx-line"></span>
            <div class="cx-step"><span class="cx-dot">3</span><span class="cx-label">Cấu hình đáp án</span></div>
        </div>

        <div class="mb-6 border-b border-gray-100 pb-4 flex flex-wrap justify-between items-start gap-3">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Tải lên file Đề thi</h3>
                <p class="text-sm text-gray-500 mt-1">Đề thi: <span class="font-bold text-blue-600">{{ $exam->title }}</span></p>
            </div>
            <a href="{{ route('teacher.exams.index') }}" class="text-sm text-gray-500 hover:text-gray-800 font-medium">&larr; Về danh sách đề</a>
        </div>

        @if(session('error'))
            <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg font-medium text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Lỗi từ máy chủ (sai định dạng, quá 10MB, tải lên không thành công...) --}}
        @if($errors->any())
            <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm" role="alert">
                <p class="font-semibold mb-1">Chưa lưu được file PDF:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach(array_unique($errors->all()) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Cột bên trái: Form upload -->
            <div>
                <form id="uploadForm" action="{{ route('teacher.exams.store-pdf', $exam->id) }}" method="POST" enctype="multipart/form-data" class="h-full flex flex-col">
                    @csrf

                    <div class="flex-1 space-y-4">
                        @if($exam->pdf_path)
                            <div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                                Đề này đã có file PDF (xem ở khung bên phải). Nếu muốn thay bằng file khác, hãy chọn file mới bên dưới.
                            </div>
                        @endif

                        <!-- Trạng thái 1: CHƯA chọn file -->
                        <div id="drop-zone" class="border-2 border-dashed border-blue-300 rounded-xl p-10 text-center bg-blue-50 hover:bg-blue-100 transition cursor-pointer" onclick="document.getElementById('file-upload').click()">
                            <svg class="mx-auto h-16 w-16 text-blue-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <span class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium shadow-sm pointer-events-none">Chọn file PDF</span>
                            <p class="text-sm text-gray-600 mt-5 pointer-events-none">hoặc kéo thả file vào đây</p>
                            <p class="text-xs text-gray-500 mt-1 pointer-events-none">Chỉ nhận định dạng .pdf, tối đa 10MB</p>
                            <input id="file-upload" name="pdf_file" type="file" accept=".pdf,application/pdf" class="hidden" required>
                        </div>

                        <!-- Báo lỗi ngay khi chọn sai file -->
                        <div id="file-error" class="hidden p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm" role="alert"></div>

                        <!-- Trạng thái 2: ĐÃ chọn file -->
                        <div id="file-selected-card" class="hidden border border-green-200 bg-green-50 rounded-xl p-8 flex flex-col items-center justify-center text-center shadow-sm">
                            <svg class="w-16 h-16 text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h4 class="text-xl font-bold text-gray-800 mb-1">Bản xem trước đã sẵn sàng!</h4>
                            <p id="file-name-display" class="text-sm text-green-700 font-medium break-all"></p>
                            <p id="file-size-display" class="text-xs text-gray-500 mb-5"></p>

                            <button type="button" id="btn-reselect" class="text-sm text-blue-600 hover:text-blue-800 underline font-medium cursor-pointer">Chọn một file khác</button>
                        </div>
                    </div>

                    <!-- Khu vực nút bấm dưới cùng -->
                    <div class="mt-8 border-t border-gray-100 pt-6">
                        <p id="note-text" class="text-sm text-gray-500 mb-4">
                            @if($exam->pdf_path)
                                Bạn có thể sang bước cấu hình đáp án ngay, hoặc chọn file mới để thay file hiện tại.
                            @else
                                Lưu ý: Bạn cần hoàn thành tải lên PDF để sang phần nhập cấu hình đáp án.
                            @endif
                        </p>

                        @if($exam->pdf_path)
                            {{-- Đề đã có PDF: đi tiếp mà không cần tải lại --}}
                            <a id="btn-continue" href="{{ route('teacher.exams.config-answers', $exam->id) }}" class="flex items-center justify-center w-full px-4 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-md">
                                Tiếp tục: Cấu hình Đáp án
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                            {{-- Chỉ hiện khi chọn file mới --}}
                            <button type="submit" id="btn-submit" class="hidden flex items-center justify-center w-full px-4 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-md"></button>
                        @else
                            <!-- Nút Submit (lúc đầu mờ, sáng lên khi đã chọn file) -->
                            <button type="submit" id="btn-submit" disabled class="flex items-center justify-center w-full px-4 py-3 bg-gray-200 text-gray-400 font-bold rounded-lg cursor-not-allowed transition-all duration-300">
                                Hoàn thành tải lên PDF để tiếp tục
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Cột bên phải: Khung xem trước PDF -->
            <div id="preview-container" class="h-[500px] border border-gray-200 rounded-xl overflow-hidden bg-gray-100 flex items-center justify-center shadow-inner relative">
                @if($exam->pdf_path)
                    <iframe src="{{ route('teacher.exams.preview-pdf', $exam->id) }}#toolbar=0" class="w-full h-full" frameborder="0"></iframe>
                @else
                    <div id="preview-placeholder" class="text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-sm">Bản xem trước PDF sẽ xuất hiện ở đây.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Script: kiểm tra file, tạo xem trước và bật nút bấm -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const MAX_BYTES = 10 * 1024 * 1024; // 10MB, khớp với luật kiểm tra ở máy chủ
            const hasPdf = {{ $exam->pdf_path ? 'true' : 'false' }};

            const form = document.getElementById('uploadForm');
            const fileUpload = document.getElementById('file-upload');
            const dropZone = document.getElementById('drop-zone');
            const fileSelectedCard = document.getElementById('file-selected-card');
            const fileNameDisplay = document.getElementById('file-name-display');
            const fileSizeDisplay = document.getElementById('file-size-display');
            const fileError = document.getElementById('file-error');
            const btnReselect = document.getElementById('btn-reselect');
            const btnSubmit = document.getElementById('btn-submit');
            const btnContinue = document.getElementById('btn-continue');
            const noteText = document.getElementById('note-text');
            const previewContainer = document.getElementById('preview-container');

            const originalPreviewContent = previewContainer.innerHTML;
            const originalNote = noteText.textContent;
            const originalSubmitClass = btnSubmit.className;
            const originalSubmitHtml = btnSubmit.innerHTML;
            const arrowSvg = '<svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>';
            let objectUrl = null;
            let submitting = false;

            function formatSize(bytes) {
                return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
            }

            function showError(msg) {
                fileError.textContent = msg;
                fileError.classList.remove('hidden');
            }

            function resetToEmpty() {
                fileUpload.value = '';
                dropZone.classList.remove('hidden');
                fileSelectedCard.classList.add('hidden');
                noteText.textContent = originalNote;

                btnSubmit.className = originalSubmitClass;
                btnSubmit.innerHTML = originalSubmitHtml;
                if (hasPdf) {
                    btnSubmit.classList.add('hidden');
                    if (btnContinue) btnContinue.classList.remove('hidden');
                } else {
                    btnSubmit.disabled = true;
                }

                if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
                previewContainer.innerHTML = originalPreviewContent;
            }

            function handleFile(file) {
                fileError.classList.add('hidden');

                const isPdf = /\.pdf$/i.test(file.name) && (file.type === '' || file.type === 'application/pdf');
                if (!isPdf) {
                    fileUpload.value = '';
                    showError('File "' + file.name + '" không phải PDF. Vui lòng chọn file có đuôi .pdf.');
                    return;
                }
                if (file.size > MAX_BYTES) {
                    fileUpload.value = '';
                    showError('File "' + file.name + '" nặng ' + formatSize(file.size) + ', vượt quá giới hạn 10MB. Hãy nén bớt rồi chọn lại.');
                    return;
                }

                // 1. Chuyển giao diện sang "đã chọn file"
                dropZone.classList.add('hidden');
                fileSelectedCard.classList.remove('hidden');
                fileNameDisplay.textContent = file.name;
                fileSizeDisplay.textContent = formatSize(file.size);

                // 2. Bật nút lưu
                btnSubmit.disabled = false;
                btnSubmit.className = 'flex items-center justify-center w-full px-4 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-md';
                btnSubmit.innerHTML = (hasPdf ? 'Lưu file mới & Đi tới Cấu hình Đáp án' : 'Lưu PDF & Đi tới Cấu hình Đáp án') + ' ' + arrowSvg;
                if (hasPdf) {
                    if (btnContinue) btnContinue.classList.add('hidden');
                    noteText.textContent = 'File mới sẽ thay thế file hiện tại sau khi bạn bấm lưu.';
                }

                // 3. Xem trước
                if (objectUrl) URL.revokeObjectURL(objectUrl);
                objectUrl = URL.createObjectURL(file);
                previewContainer.innerHTML = '<iframe src="' + objectUrl + '#toolbar=0" class="w-full h-full" frameborder="0"></iframe>';
            }

            fileUpload.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file) handleFile(file);
            });

            btnReselect.addEventListener('click', function () {
                fileError.classList.add('hidden');
                resetToEmpty();
            });

            // Kéo thả file vào khung
            ['dragenter', 'dragover'].forEach(function (name) {
                dropZone.addEventListener(name, function (e) { e.preventDefault(); dropZone.classList.add('cx-drag'); });
            });
            ['dragleave', 'drop'].forEach(function (name) {
                dropZone.addEventListener(name, function (e) { e.preventDefault(); dropZone.classList.remove('cx-drag'); });
            });
            dropZone.addEventListener('drop', function (e) {
                const files = e.dataTransfer && e.dataTransfer.files;
                if (!files || files.length === 0) return;
                try {
                    fileUpload.files = files;
                } catch (err) { /* trình duyệt cũ: bỏ qua, người dùng chọn bằng nút */ }
                if (fileUpload.files.length > 0) handleFile(fileUpload.files[0]);
            });

            // Chống bấm lưu hai lần khi file đang tải lên
            form.addEventListener('submit', function (e) {
                if (submitting) { e.preventDefault(); return; }
                if (fileUpload.files.length === 0) { e.preventDefault(); return; }
                submitting = true;
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-70', 'cursor-wait');
                btnSubmit.textContent = 'Đang tải lên, vui lòng chờ...';
            });
        });
    </script>
@endsection