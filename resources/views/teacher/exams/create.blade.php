@extends('layouts.teacher')

@section('title', 'Tạo đề thi mới (Bước 1/3)')

@section('content')
    <style>
        /* Thanh 3 bước tạo đề */
        .cx-steps { display: flex; align-items: center; gap: 10px; margin-bottom: 22px; font-size: 13px; color: #9ca3af; }
        .cx-step { display: flex; align-items: center; gap: 8px; white-space: nowrap; }
        .cx-dot { width: 26px; height: 26px; border-radius: 9999px; display: flex; align-items: center; justify-content: center;
                  font-size: 12px; font-weight: 700; background: #e5e7eb; color: #6b7280; flex-shrink: 0; }
        .cx-step.on { color: #1f2937; font-weight: 600; }
        .cx-step.on .cx-dot { background: #2563eb; color: #fff; }
        .cx-line { flex: 1; height: 2px; background: #e5e7eb; min-width: 14px; }
        @media (max-width: 640px) { .cx-step:not(.on) .cx-label { display: none; } }
    </style>

    <div class="max-w-3xl bg-white p-6 md:p-8 rounded-xl shadow-sm border border-gray-100 mx-auto">

        <!-- Các bước tạo đề -->
        <div class="cx-steps" aria-label="Các bước tạo đề thi">
            <div class="cx-step on"><span class="cx-dot">1</span><span class="cx-label">Thông tin chung</span></div>
            <span class="cx-line"></span>
            <div class="cx-step"><span class="cx-dot">2</span><span class="cx-label">Tải đề PDF</span></div>
            <span class="cx-line"></span>
            <div class="cx-step"><span class="cx-dot">3</span><span class="cx-label">Cấu hình đáp án</span></div>
        </div>

        <div class="mb-6 border-b border-gray-100 pb-4">
            <h3 class="text-2xl font-bold text-gray-800">Thông tin chung</h3>
            <p class="text-sm text-gray-500 mt-1">Khởi tạo thông tin cơ bản cho đề thi. Đề thi sẽ được lưu ở trạng thái bản nháp.</p>
        </div>

        @if(session('error'))
            <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg font-medium text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Tóm tắt lỗi ở đầu form để không bị bỏ sót, kể cả lỗi nằm ở khung đang ẩn --}}
        @if($errors->any())
            <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm" role="alert">
                <p class="font-semibold mb-1">Chưa lưu được, vui lòng kiểm tra lại:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach(array_unique($errors->all()) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('teacher.exams.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Tên đề thi -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tên đề thi <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition"
                       placeholder="VD: Đề kiểm tra Giữa học kỳ 1 Toán 10">
                @error('title') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Khối lớp, loại đề, thời gian: một hàng cho gọn -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Khối lớp -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Khối lớp <span class="text-red-500">*</span></label>
                    <select name="grade" id="gradeSelect" required
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
                        <option value="">-- Chọn khối --</option>
                        <option value="10" {{ old('grade') == '10' ? 'selected' : '' }}>Khối 10</option>
                        <option value="11" {{ old('grade') == '11' ? 'selected' : '' }}>Khối 11</option>
                        <option value="12" {{ old('grade') == '12' ? 'selected' : '' }}>Khối 12</option>
                    </select>
                    @error('grade') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Loại đề thi -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Loại đề <span class="text-red-500">*</span></label>
                    <select name="exam_type" id="typeSelect" required
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
                        <option value="">-- Chọn loại đề --</option>
                        <option value="Giữa kỳ I" {{ old('exam_type') == 'Giữa kỳ I' ? 'selected' : '' }}>Giữa kỳ I</option>
                        <option value="Cuối kỳ I" {{ old('exam_type') == 'Cuối kỳ I' ? 'selected' : '' }}>Cuối kỳ I</option>
                        <option value="Giữa kỳ II" {{ old('exam_type') == 'Giữa kỳ II' ? 'selected' : '' }}>Giữa kỳ II</option>
                        <option value="Cuối kỳ II" {{ old('exam_type') == 'Cuối kỳ II' ? 'selected' : '' }}>Cuối kỳ II</option>
                        <option value="Thi thử THPT QG" id="thptOption" class="hidden" {{ old('exam_type') == 'Thi thử THPT QG' ? 'selected' : '' }}>Thi thử THPT QG</option>
                    </select>
                    @error('exam_type') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Thời gian làm bài -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Thời gian (phút) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration" value="{{ old('duration', 90) }}" required min="15" max="300"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition"
                           placeholder="VD: 90">
                    <p class="text-xs text-gray-400 mt-1">Từ 15 đến 300 phút</p>
                    @error('duration') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Giao đề cho lớp: luôn có mặt trên trang, nội dung đổi theo khối đã chọn -->
            <div id="classSelectionContainer" class="border border-blue-100 bg-blue-50/50 p-4 rounded-lg">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-sm font-semibold text-gray-700">Giao đề cho lớp <span class="text-red-500">*</span></label>
                    <!-- Chỉ hiện khi đã có danh sách lớp -->
                    <div id="classTools" class="hidden">
                        <div class="flex items-center gap-3 text-xs whitespace-nowrap">
                            <span id="classCount" class="text-gray-500"></span>
                            <button type="button" id="toggleAllBtn" class="text-blue-600 hover:underline font-medium">Chọn tất cả</button>
                        </div>
                    </div>
                </div>

                <!-- Chưa chọn khối -->
                <p id="classHintPickGrade" class="text-sm text-gray-500">Chọn khối lớp ở trên để hiện danh sách lớp bạn phụ trách.</p>

                <!-- Khối đã chọn nhưng giáo viên chưa có lớp nào -->
                <div id="classHintEmpty" class="hidden p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm">
                    Hiện tại bạn chưa được phân công dạy khối <b id="emptyGradeLabel"></b>, nên chưa thể tạo đề cho khối này.
                </div>

                <!-- Lưới ô tick chọn lớp (JS render) -->
                <div id="checkboxGroup" class="grid grid-cols-2 md:grid-cols-3 gap-3"></div>

                @error('class_ids')
                    <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
                @enderror
                @error('class_ids.*')
                    <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="pt-5 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('teacher.exams.index') }}"
                   class="px-5 py-3 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Hủy
                </a>
                <button type="submit" id="submitBtn"
                        class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition flex items-center">
                    Lưu & Tiếp tục
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Script: ẩn/hiện loại đề THPT QG và lọc lớp theo khối -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Danh sách lớp của giáo viên đang đăng nhập (ExamController@create truyền $classes)
            const allClasses = @json($classes ?? []);
            // Các lớp đã tick trước đó khi form bị trả về do lỗi
            const oldClassIds = @json(old('class_ids', [])).map(String);

            const gradeSelect = document.getElementById('gradeSelect');
            const typeSelect = document.getElementById('typeSelect');
            const thptOption = document.getElementById('thptOption');
            const checkboxGroup = document.getElementById('checkboxGroup');
            const hintPick = document.getElementById('classHintPickGrade');
            const hintEmpty = document.getElementById('classHintEmpty');
            const emptyGradeLabel = document.getElementById('emptyGradeLabel');
            const classBox = document.getElementById('classSelectionContainer');
            const classTools = document.getElementById('classTools');
            const classCount = document.getElementById('classCount');
            const toggleAllBtn = document.getElementById('toggleAllBtn');

            function boxes() { return Array.from(checkboxGroup.querySelectorAll('input[type=checkbox]')); }

            // Cập nhật dòng "Đã chọn x/y lớp" và nhãn của nút Chọn tất cả
            function refreshCount() {
                const all = boxes();
                const n = all.filter(b => b.checked).length;
                classCount.textContent = 'Đã chọn ' + n + '/' + all.length + ' lớp';
                toggleAllBtn.textContent = (all.length > 0 && n === all.length) ? 'Bỏ chọn tất cả' : 'Chọn tất cả';
                if (n > 0) {
                    classBox.classList.remove('border-red-300');
                    const msg = document.getElementById('classPickMsg');
                    if (msg) msg.remove();
                }
            }

            // Thi thử THPT QG chỉ dành cho khối 12
            function toggleThptOption() {
                if (gradeSelect.value === '12') {
                    thptOption.classList.remove('hidden');
                } else {
                    thptOption.classList.add('hidden');
                    if (typeSelect.value === 'Thi thử THPT QG') {
                        typeSelect.value = '';
                    }
                }
            }

            function updateClassSelection() {
                const grade = gradeSelect.value;
                // Giữ lại các lớp đang tick; khi form bị trả về do lỗi thì lấy lại từ old()
                const ticked = boxes().filter(i => i.checked).map(i => i.value);
                const keep = ticked.length ? ticked : oldClassIds;

                checkboxGroup.innerHTML = '';
                hintPick.classList.add('hidden');
                hintEmpty.classList.add('hidden');
                classTools.classList.add('hidden');

                if (!grade) {
                    hintPick.classList.remove('hidden');
                    return;
                }

                // Lọc đúng theo khối của lớp (server cũng kiểm tra điều này)
                const list = allClasses.filter(c => String(c.grade) === grade);

                if (list.length === 0) {
                    emptyGradeLabel.textContent = grade;
                    hintEmpty.classList.remove('hidden');
                    return;
                }

                list.forEach(function (c) {
                    const label = document.createElement('label');
                    label.className = 'flex items-center p-3 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition shadow-sm';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'class_ids[]';
                    checkbox.value = c.id;
                    checkbox.className = 'w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer';
                    if (keep.includes(String(c.id))) checkbox.checked = true;

                    const span = document.createElement('span');
                    span.className = 'ml-3 text-sm font-medium text-gray-700';
                    span.textContent = c.name;

                    label.appendChild(checkbox);
                    label.appendChild(span);
                    checkboxGroup.appendChild(label);
                });

                classTools.classList.remove('hidden');
                refreshCount();
            }

            toggleThptOption();
            updateClassSelection();

            gradeSelect.addEventListener('change', function () {
                toggleThptOption();
                updateClassSelection();
            });

            // Tick hoặc bỏ tick một lớp
            checkboxGroup.addEventListener('change', refreshCount);

            // Chọn tất cả / Bỏ chọn tất cả
            toggleAllBtn.addEventListener('click', function () {
                const all = boxes();
                const selectAll = !all.every(b => b.checked);
                all.forEach(b => { b.checked = selectAll; });
                refreshCount();
            });

            // Bấm Lưu mà chưa chọn lớp nào: báo ngay tại chỗ thay vì đợi server trả về
            const form = gradeSelect.closest('form');
            form.addEventListener('submit', function (e) {
                if (!form.checkValidity()) return; // để trình duyệt tự báo ô còn thiếu
                if (boxes().filter(b => b.checked).length === 0) {
                    e.preventDefault();
                    if (!gradeSelect.value) { hintPick.classList.remove('hidden'); }
                    else if (!allClasses.some(c => String(c.grade) === gradeSelect.value)) { /* đã có khung cảnh báo */ }
                    else { classBox.classList.add('border-red-300'); }
                    classBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    if (checkboxGroup.children.length) {
                        let msg = document.getElementById('classPickMsg');
                        if (!msg) {
                            msg = document.createElement('span');
                            msg.id = 'classPickMsg';
                            msg.className = 'text-red-500 text-sm mt-2 block';
                            classBox.appendChild(msg);
                        }
                        msg.textContent = 'Hãy tick chọn ít nhất một lớp để giao đề.';
                    }
                }
            });
        });
    </script>
@endsection