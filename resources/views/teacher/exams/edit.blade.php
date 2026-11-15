@extends('layouts.teacher')

<?php 
/** 
 * @var \App\Models\Exam $exam 
 * @var int $submittedCount 
 */ 
?>

@section('title', 'Sửa đề thi: ' . $exam->title)
@section('content')

    <!-- Header & Breadcrumb -->
    <div class="container mx-auto px-4 mt-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Sửa đề thi</h2>
            <a href="{{ route('teacher.exams.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 transition font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Trở về danh sách
            </a>
        </div>
    </div>

    <!-- Hiển thị lỗi nếu có -->
    @if(session('error'))
        <div class="container mx-auto px-4 mt-4">
            <div class="w-full bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg shadow-sm flex items-start">
                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p class="font-bold">Hệ thống từ chối lưu!</p>
                    <p class="text-sm mt-1">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="container mx-auto px-4 mt-4">
            <div class="w-full bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg shadow-sm">
                <p class="font-bold">Dữ liệu chưa hợp lệ:</p>
                <ul class="list-disc list-inside text-sm mt-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- THẺ FORM BỌC TOÀN BỘ -->
    <form id="editExamForm" action="{{ route('teacher.exams.update', $exam->id) }}" method="POST" enctype="multipart/form-data" class="container mx-auto px-4 py-6">
        @csrf
        @method('PUT')
        
        <!-- PHẦN 1: THÔNG TIN CHUNG (NẰM NGANG TRÊN CÙNG) -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6">
            <h3 class="text-base font-bold text-gray-800 mb-4 border-b pb-2">Thông tin thiết lập</h3>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên đề thi <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ $exam->title }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Khối lớp <?= $submittedCount > 0 ? '<span class="text-red-500 text-xs">(Khóa)</span>' : '' ?></label>
                    <select name="grade" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm <?= $submittedCount > 0 ? 'bg-gray-100 cursor-not-allowed' : '' ?>" <?= $submittedCount > 0 ? 'disabled' : '' ?>>
                        <option value="10" <?= $exam->grade == 10 ? 'selected' : '' ?>>Khối 10</option>
                        <option value="11" <?= $exam->grade == 11 ? 'selected' : '' ?>>Khối 11</option>
                        <option value="12" <?= $exam->grade == 12 ? 'selected' : '' ?>>Khối 12</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại đề <?= $submittedCount > 0 ? '<span class="text-red-500 text-xs">(Khóa)</span>' : '' ?></label>
                    <select name="type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm <?= $submittedCount > 0 ? 'bg-gray-100 cursor-not-allowed' : '' ?>" <?= $submittedCount > 0 ? 'disabled' : '' ?>>
                        <option value="Giữa kỳ I" <?= $exam->type == 'Giữa kỳ I' ? 'selected' : '' ?>>Giữa kỳ I</option>
                        <option value="Cuối kỳ I" <?= $exam->type == 'Cuối kỳ I' ? 'selected' : '' ?>>Cuối kỳ I</option>
                        <option value="Giữa kỳ II" <?= $exam->type == 'Giữa kỳ II' ? 'selected' : '' ?>>Giữa kỳ II</option>
                        <option value="Cuối kỳ II" <?= $exam->type == 'Cuối kỳ II' ? 'selected' : '' ?>>Cuối kỳ II</option>
                        <option value="Thi thử THPT QG" <?= $exam->type == 'Thi thử THPT QG' ? 'selected' : '' ?>>Thi thử THPT QG</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian (phút)</label>
                    <input type="number" name="duration_minutes" value="{{ $exam->duration_minutes }}" min="15" max="300" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                        <option value="draft" <?= $exam->status == 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                        <option value="published" <?= $exam->status == 'published' ? 'selected' : '' ?>>Đang mở</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- PHẦN 2: WORKSPACE (PDF BÊN TRÁI - ĐÁP ÁN BÊN PHẢI) -->
        <div class="flex flex-col lg:flex-row gap-6 items-start">
            
            <!-- CỘT TRÁI: KHUNG PDF VÀ UPLOAD (Dùng Sticky để ghim khi cuộn) -->
            <div class="w-full lg:w-1/2 space-y-4 lg:sticky lg:top-4 z-10">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between mb-3 border-b pb-2">
                        <h3 class="text-lg font-bold text-gray-800">Nội dung đề thi (PDF)</h3>
                        @if($exam->pdf_path)
                        <a href="{{ route('teacher.exams.preview-pdf', $exam->id) }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 flex items-center font-semibold bg-blue-50 px-2 py-1 rounded">
                            Mở tab mới
                        </a>
                        @endif
                    </div>

                    @if($exam->pdf_path)
                    <!-- Tăng chiều cao iframe để dễ đọc đề -->
                    <div class="border border-gray-300 rounded-lg overflow-hidden bg-gray-50 mb-4 shadow-inner">
                        <iframe src="{{ route('teacher.exams.preview-pdf', $exam->id) }}#toolbar=0" class="w-full h-[600px]" title="Preview PDF"></iframe>
                    </div>
                    @endif

                    <!-- Upload PDF mới -->
                    <div>
                        <input type="file" name="pdf_file" id="pdf_file_input" accept=".pdf" class="hidden">
                        
                        <div id="upload-trigger-area" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:bg-blue-50 hover:border-blue-400 transition-all flex items-center justify-center gap-3" onclick="document.getElementById('pdf_file_input').click()">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <div class="text-left">
                                <p class="text-gray-700 font-medium text-sm">Tải lên file PDF mới để thay thế</p>
                                <p class="text-xs text-gray-500">Tối đa 10MB (Bỏ qua nếu giữ nguyên)</p>
                            </div>
                        </div>

                        <div id="file-selected-card" class="hidden border border-green-200 bg-green-50 rounded-lg p-4 flex items-center justify-between shadow-sm transition-all">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <svg class="w-8 h-8 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p id="file-name-display" class="text-sm text-green-700 font-bold truncate"></p>
                            </div>
                            <button type="button" id="btn-reselect" class="text-xs px-3 py-1.5 bg-white border border-gray-300 rounded text-gray-700 hover:bg-gray-100 font-medium cursor-pointer transition-colors shadow-sm whitespace-nowrap">Đổi file</button>
                        </div>

                        <?php if($submittedCount > 0): ?>
                            <div class="mt-3 bg-orange-50 border border-orange-200 rounded-lg p-3 flex items-start">
                                <svg class="w-5 h-5 text-orange-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <p class="text-xs text-orange-700 font-medium">Đã có <strong><?= $submittedCount ?></strong> lượt nộp. Đổi file PDF có thể làm lệch ma trận đáp án hiện tại!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: MA TRẬN ĐÁP ÁN (Cuộn tự do) -->
            <div class="w-full lg:w-1/2 bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                <div class="flex items-center justify-between border-b pb-3 mb-5">
                    <h3 class="text-xl font-bold text-gray-800">Ma trận đáp án</h3>
                </div>
                
                <!-- Phần I -->
                <div class="mb-8">
                    <h4 class="font-bold text-blue-700 mb-4 flex items-center bg-blue-50 p-2 rounded">
                        <span class="bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded-full text-sm mr-2">I</span> 
                        Trắc nghiệm nhiều phương án
                    </h4>
                    <div class="grid grid-cols-1 gap-2">
                        <?php for($i = 1; $i <= 12; $i++): ?>
                            <?php $oldAns = isset($part1[$i]) ? $part1[$i]->correct_answer : ''; ?>
                            <div class="flex items-center bg-gray-50 border border-gray-100 rounded p-2 ans-row hover:bg-gray-100 transition-colors" data-part="Phần I" data-q="Câu <?= $i ?>">
                                <span class="font-bold text-gray-700 w-20 whitespace-nowrap">Câu <?= $i ?>:</span>
                                <div class="flex gap-6">
                                    <?php foreach(['A', 'B', 'C', 'D'] as $opt): ?>
                                        <label class="flex items-center cursor-pointer group">
                                            <input type="radio" name="part1[<?= $i ?>]" value="<?= $opt ?>" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 mr-1.5 ans-input" data-old="<?= $oldAns ?>" <?= $oldAns == $opt ? 'checked' : '' ?>> 
                                            <span class="text-gray-700 font-medium group-hover:text-blue-600"><?= $opt ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Phần II -->
                <div class="mb-8">
                    <h4 class="font-bold text-blue-700 mb-4 flex items-center bg-blue-50 p-2 rounded">
                        <span class="bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded-full text-sm mr-2">II</span> 
                        Trắc nghiệm Đúng/Sai
                    </h4>
                    <div class="grid grid-cols-1 gap-4">
                        <?php for($i = 1; $i <= 4; $i++): ?>
                            <?php $oldJson = isset($part2[$i]) ? json_decode($part2[$i]->correct_answer, true) : []; ?>
                            <div class="border border-gray-200 p-3 rounded-lg shadow-sm">
                                <span class="font-bold text-gray-800 block mb-2">Câu <?= $i ?></span>
                                <div class="grid grid-cols-2 gap-3">
                                    <?php foreach(['a', 'b', 'c', 'd'] as $sub): ?>
                                        <?php $oldSubVal = $oldJson[$sub] ?? ''; ?>
                                        <div class="flex justify-between items-center ans-row bg-gray-50 px-2 py-1.5 rounded" data-part="Phần II" data-q="Câu <?= $i ?> ý <?= $sub ?>">
                                            <span class="font-medium text-gray-700 text-sm">Ý <?= $sub ?>)</span>
                                            <select name="part2[<?= $i ?>][<?= $sub ?>]" class="border-gray-300 rounded text-sm py-1 focus:ring-blue-500 focus:border-blue-500 ans-input" data-old="<?= $oldSubVal ?>">
                                                <option value="">--</option>
                                                <option value="Đ" <?= $oldSubVal == 'Đ' ? 'selected' : '' ?>>Đúng</option>
                                                <option value="S" <?= $oldSubVal == 'S' ? 'selected' : '' ?>>Sai</option>
                                            </select>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Phần III -->
                <div class="mb-6">
                    <h4 class="font-bold text-blue-700 mb-4 flex items-center bg-blue-50 p-2 rounded">
                        <span class="bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded-full text-sm mr-2">III</span> 
                        Trả lời ngắn
                    </h4>
                    <div class="grid grid-cols-1 gap-3">
                        <?php for($i = 1; $i <= 6; $i++): ?>
                            <?php $oldVal = isset($part3[$i]) ? $part3[$i]->correct_answer : ''; ?>
                            <div class="flex items-center bg-gray-50 border border-gray-100 rounded p-2 ans-row" data-part="Phần III" data-q="Câu <?= $i ?>">
                                <span class="font-bold text-gray-700 w-20 whitespace-nowrap">Câu <?= $i ?>:</span>
                                <input type="text" name="part3[<?= $i ?>]" value="{{ $oldVal }}" class="border-gray-300 flex-1 p-1.5 rounded shadow-sm focus:border-blue-500 focus:ring-blue-500 ans-input text-sm" data-old="{{ $oldVal }}" placeholder="Nhập đáp án số, VD: 3.14">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>

       <!-- PHẦN 3: THANH CÔNG CỤ NẰM Ở CUỐI TRANG -->
<div class="mt-6 bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex justify-end gap-3 items-center w-full">
            <a href="{{ route('teacher.exams.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors">Hủy thao tác</a>
            <button type="submit" class="px-8 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-md transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                LƯU ĐỀ THI
            </button>
        </div>
    </form>

<script>
    const fileInput = document.getElementById('pdf_file_input');
    const triggerArea = document.getElementById('upload-trigger-area');
    const fileCard = document.getElementById('file-selected-card');
    const fileNameDisplay = document.getElementById('file-name-display');
    const btnReselect = document.getElementById('btn-reselect');

    fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            const fileName = this.files[0].name;
            fileNameDisplay.textContent = fileName;
            triggerArea.classList.add('hidden');
            fileCard.classList.remove('hidden');
        }
    });

    btnReselect.addEventListener('click', function(e) {
        e.stopPropagation();
        fileInput.value = '';
        fileCard.classList.add('hidden');
        triggerArea.classList.remove('hidden');
    });

    document.getElementById('editExamForm').addEventListener('submit', function(e) {
        const submittedCount = <?= $submittedCount ?>;
        if (submittedCount === 0) return;

        let changes = [];
        const rows = document.querySelectorAll('.ans-row');

        rows.forEach(row => {
            const part = row.getAttribute('data-part');
            const qName = row.getAttribute('data-q');
            const inputs = row.querySelectorAll('.ans-input');
            
            let oldVal = '';
            let newVal = '';

            if (inputs[0].type === 'radio') {
                oldVal = inputs[0].getAttribute('data-old');
                let checkedInput = row.querySelector('.ans-input:checked');
                newVal = checkedInput ? checkedInput.value : '';
            } else {
                oldVal = inputs[0].getAttribute('data-old');
                newVal = inputs[0].value;
            }

            if (oldVal !== newVal) {
                let displayOld = oldVal ? oldVal : '(Trống)';
                let displayNew = newVal ? newVal : '(Trống)';
                changes.push(`• ${part} - ${qName}: ${displayOld} ➔ ${displayNew}`);
            }
        });

        if (changes.length > 0) {
            e.preventDefault();
            const confirmMsg = `CẢNH BÁO: Đề này đã có ${submittedCount} học sinh nộp bài.\nViệc thay đổi đáp án sẽ làm thay đổi điểm số của các em.\n\nCác thay đổi phát hiện:\n${changes.join('\n')}\n\nBạn có chắc chắn muốn lưu những thay đổi này?`;
            
            if (confirm(confirmMsg)) {
                e.target.submit(); 
            }
        }
    });
</script>
@endsection