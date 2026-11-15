<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    /** Các loại đề được phép lưu (cột exams.type là VARCHAR chứa nhãn tiếng Việt). */
    private const EXAM_TYPES = [
        'Giữa kỳ I',
        'Cuối kỳ I',
        'Giữa kỳ II',
        'Cuối kỳ II',
        'Thi thử THPT QG',
    ];

    /** Mức độ nhận thức: nhãn trên form → giá trị 1..4 (chỉ số của ENUM difficulty). */
    private const LEVEL_MAP = [
        'Nhận biết' => 1, 'Thông hiểu' => 2, 'Vận dụng' => 3, 'Vận dụng cao' => 4,
        '1' => 1, '2' => 2, '3' => 3, '4' => 4,
    ];

    /** Đáp án Phần III phải là số; chấp nhận dấu . hoặc , ở phần thập phân. */
    private const NUMERIC_REGEX = '/^-?\d+([.,]\d+)?$/';

    private function isBlank($value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    private function ensureOwner(Exam $exam): void
    {
        if ((int) $exam->created_by !== (int) Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đề thi này.');
        }
    }

    // 1. Hiển thị form Bước 2.1
    public function create()
    {
        $classes = SchoolClass::where('teacher_id', Auth::id())->get();

        return view('teacher.exams.create', compact('classes'));
    }

    // 2. Lưu thông tin chung (tạo bản nháp)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'grade' => 'required|in:10,11,12',
            'exam_type' => ['required', Rule::in(self::EXAM_TYPES)],
            'duration' => 'required|integer|min:15|max:300',
            'class_ids' => 'required|array|min:1',
            // Chỉ được giao cho lớp do chính mình phụ trách
            'class_ids.*' => [Rule::exists('classes', 'id')->where('teacher_id', Auth::id())],
        ], [
            'class_ids.required' => 'Vui lòng chọn ít nhất một lớp để giao đề.',
            'class_ids.*.exists' => 'Chỉ được giao đề cho lớp do bạn phụ trách.',
        ]);

        if ($request->exam_type === 'Thi thử THPT QG' && $request->grade !== '12') {
            return back()->withErrors(['exam_type' => 'Loại đề Thi thử THPT QG chỉ dành cho khối 12.'])->withInput();
        }

        $gradeMismatch = SchoolClass::whereIn('id', $request->class_ids)
            ->where('grade', '!=', $request->grade)
            ->exists();
        if ($gradeMismatch) {
            return back()->withErrors(['class_ids' => 'Có lớp được chọn không thuộc khối ' . $request->grade . '.'])->withInput();
        }

        $exam = Exam::create([
            'title' => $request->title,
            'grade' => $request->grade,
            'type' => $request->exam_type,
            'duration_minutes' => $request->duration,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        // Giao đề cho các lớp đã chọn (bảng class_exam)
        $exam->classes()->attach($request->class_ids);

        return redirect()->route('teacher.exams.upload-pdf', $exam->id);
    }

    // 3. Form tải PDF (Bước 2.2)
    public function uploadPdf(Exam $exam)
    {
        $this->ensureOwner($exam);

        if ($this->configLocked($exam)) {
            return redirect()->route('teacher.exams.edit', $exam->id)
                ->with('error', 'Đề đã công bố hoặc đã có bài nộp. Hãy đổi file PDF tại trang Sửa đề.');
        }

        return view('teacher.exams.upload-pdf', compact('exam'));
    }

    // 4. Lưu file PDF
    public function storePdf(Request $request, Exam $exam)
    {
        $this->ensureOwner($exam);

        if ($this->configLocked($exam)) {
            return redirect()->route('teacher.exams.edit', $exam->id)
                ->with('error', 'Đề đã công bố hoặc đã có bài nộp. Hãy đổi file PDF tại trang Sửa đề.');
        }

        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
        ], [
            'pdf_file.required' => 'Vui lòng chọn file PDF.',
            'pdf_file.mimes' => 'File tải lên phải có định dạng .pdf.',
            'pdf_file.max' => 'Dung lượng file không được vượt quá 10MB.',
        ]);

        $oldPdf = $exam->pdf_path;
        $path = $request->file('pdf_file')->store('private/exams');
        $exam->update(['pdf_path' => $path]);

        if ($oldPdf) {
            Storage::delete($oldPdf);
        }

        return redirect()->route('teacher.exams.config-answers', $exam->id)
            ->with('success', 'Đã lưu PDF thành công!');
    }

    // 5. Trả file PDF để xem trước (chỉ người tạo đề)
    public function previewPdf(Exam $exam)
    {
        $this->ensureOwner($exam);

        if (empty($exam->pdf_path) || !Storage::exists($exam->pdf_path)) {
            abort(404, 'Không tìm thấy file PDF trên hệ thống.');
        }

        return response()->file(Storage::path($exam->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="de-thi.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Đề đã công bố hoặc đã có bài nộp thì chỉ được sửa đáp án ở trang Sửa đề
     * (nơi có cảnh báo, ghi nhật ký và chấm lại), không được ghi đè qua Bước 3.
     */
    private function configLocked(Exam $exam): bool
    {
        if ($exam->status === 'published') {
            return true;
        }

        return DB::table('attempts')->where('exam_id', $exam->id)->where('status', 'submitted')->exists();
    }

    // Bước 2.3: Cấu hình đáp án
    public function configAnswers(Exam $exam)
    {
        $this->ensureOwner($exam);

        if ($this->configLocked($exam)) {
            return redirect()->route('teacher.exams.edit', $exam->id)
                ->with('error', 'Đề đã công bố hoặc đã có bài nộp. Hãy chỉnh đáp án tại trang Sửa đề.');
        }

        $query = DB::table('topics')->where('is_active', true);

        // Đề thi thử THPT QG được chọn chuyên đề của mọi khối; đề khác chỉ chọn đúng khối
        if ($exam->type !== 'Thi thử THPT QG') {
            $query->where('grade', $exam->grade);
        }

        $topics = $query->orderBy('grade')->orderBy('name')->get();
        $levels = ['Nhận biết', 'Thông hiểu', 'Vận dụng', 'Vận dụng cao'];

        // Nạp lại cấu hình đã lưu nháp để form không bị trống khi mở lại.
        // Cùng cấu trúc với dữ liệu form (part1[số câu][answer|topic_id|level], ...)
        $levelLabels = [
            'recognize' => 'Nhận biết', 'understand' => 'Thông hiểu',
            'apply' => 'Vận dụng', 'advanced' => 'Vận dụng cao',
        ];
        $partKeys = ['I' => 'part1', 'II' => 'part2', 'III' => 'part3'];
        $saved = ['part1' => [], 'part2' => [], 'part3' => []];

        foreach (DB::table('exam_questions')->where('exam_id', $exam->id)->get() as $q) {
            $key = $partKeys[$q->part] ?? null;
            if ($key === null) continue;

            $row = [
                'topic_id' => $q->topic_id,
                'level' => $levelLabels[$q->difficulty] ?? '',
            ];

            if ($q->part === 'II') {
                $row += (array) (json_decode($q->correct_answer ?? '', true) ?: []);
            } else {
                $row['answer'] = $q->correct_answer;
            }
            if ($q->part === 'III') {
                $row['rounding'] = $q->decimal_places;
            }

            $saved[$key][(int) $q->number] = $row;
        }

        return view('teacher.exams.config-answers', compact('exam', 'topics', 'levels', 'saved'));
    }

    public function storeAnswers(Request $request, Exam $exam)
    {
        $this->ensureOwner($exam);

        if ($this->configLocked($exam)) {
            return redirect()->route('teacher.exams.edit', $exam->id)
                ->with('error', 'Đề đã công bố hoặc đã có bài nộp. Hãy chỉnh đáp án tại trang Sửa đề.');
        }

        $action = $request->input('action') === 'publish' ? 'publish' : 'draft';

        if (empty($exam->pdf_path)) {
            return back()->with('error', 'Hệ thống không tìm thấy file PDF của đề thi này. Vui lòng tải lên đề thi trước.');
        }

        // ==========================================
        // 1. KIỂM TRA DỮ LIỆU
        //    Lưu nháp: ô nào có nhập thì phải đúng định dạng.
        //    Công bố : bắt buộc đủ 22 câu (đáp án, chuyên đề, mức độ).
        //    Số câu trong từng phần đánh số 1..12, 1..4, 1..6 (khớp với form và phòng thi).
        // ==========================================
        $publish = $action === 'publish';
        $req = $publish ? 'required' : 'nullable';
        $levelRule = Rule::in(array_keys(self::LEVEL_MAP));
        $rules = [];
        $messages = [];

        $fieldRules = function (string $key, string $label, array $extra = []) use (&$rules, &$messages, $req) {
            $rules[$key] = array_merge([$req], $extra);
            $messages["$key.required"] = "$label: chưa nhập.";
        };

        for ($i = 1; $i <= 12; $i++) {
            $fieldRules("part1.$i.answer", "Phần I - Câu $i (đáp án)", ['in:A,B,C,D']);
            $fieldRules("part1.$i.topic_id", "Phần I - Câu $i (chuyên đề)", ['integer', 'exists:topics,id']);
            $fieldRules("part1.$i.level", "Phần I - Câu $i (mức độ)", [$levelRule]);
        }

        for ($i = 1; $i <= 4; $i++) {
            foreach (['a', 'b', 'c', 'd'] as $sub) {
                $fieldRules("part2.$i.$sub", "Phần II - Câu $i ý $sub", ['in:Đ,S']);
            }
            $fieldRules("part2.$i.topic_id", "Phần II - Câu $i (chuyên đề)", ['integer', 'exists:topics,id']);
            $fieldRules("part2.$i.level", "Phần II - Câu $i (mức độ)", [$levelRule]);
        }

        for ($i = 1; $i <= 6; $i++) {
            $fieldRules("part3.$i.answer", "Phần III - Câu $i (đáp án)", ['regex:' . self::NUMERIC_REGEX]);
            $messages["part3.$i.answer.regex"] = "Phần III - Câu $i: đáp án phải là một số (ví dụ 3.14 hoặc -2).";
            $fieldRules("part3.$i.topic_id", "Phần III - Câu $i (chuyên đề)", ['integer', 'exists:topics,id']);
            $fieldRules("part3.$i.level", "Phần III - Câu $i (mức độ)", [$levelRule]);
            $rules["part3.$i.rounding"] = ['nullable', 'in:0,1,2'];
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('error', 'Vui lòng hoàn thiện cấu hình các câu hỏi bị đánh dấu lỗi.');
        }

        // ==========================================
        // 2. LƯU VÀO CSDL (trong một transaction)
        // ==========================================
        $part1 = (array) $request->input('part1', []);
        $part2 = (array) $request->input('part2', []);
        $part3 = (array) $request->input('part3', []);

        DB::transaction(function () use ($exam, $action, $part1, $part2, $part3) {
            foreach (range(1, 12) as $qNum) {
                $data = $part1[$qNum] ?? null;
                if (!is_array($data)) continue;

                // Lưu nháp: bỏ qua dòng hoàn toàn trống
                if ($action === 'draft' && $this->isBlank($data['answer'] ?? null) && $this->isBlank($data['topic_id'] ?? null)) continue;

                DB::table('exam_questions')->updateOrInsert(
                    ['exam_id' => $exam->id, 'part' => 'I', 'number' => $qNum],
                    [
                        'topic_id' => $data['topic_id'] ?? null,
                        'difficulty' => self::LEVEL_MAP[$data['level'] ?? ''] ?? null,
                        'correct_answer' => $this->isBlank($data['answer'] ?? null) ? null : $data['answer'],
                        'updated_at' => now(),
                    ]
                );
            }

            foreach (range(1, 4) as $qNum) {
                $data = $part2[$qNum] ?? null;
                if (!is_array($data)) continue;

                $picked = [];
                foreach (['a', 'b', 'c', 'd'] as $sub) {
                    if (!$this->isBlank($data[$sub] ?? null)) {
                        $picked[$sub] = $data[$sub];
                    }
                }

                if ($action === 'draft' && empty($picked) && $this->isBlank($data['topic_id'] ?? null)) continue;

                DB::table('exam_questions')->updateOrInsert(
                    ['exam_id' => $exam->id, 'part' => 'II', 'number' => $qNum],
                    [
                        'topic_id' => $data['topic_id'] ?? null,
                        'difficulty' => self::LEVEL_MAP[$data['level'] ?? ''] ?? null,
                        'correct_answer' => $picked ? json_encode($picked) : null,
                        'updated_at' => now(),
                    ]
                );
            }

            foreach (range(1, 6) as $qNum) {
                $data = $part3[$qNum] ?? null;
                if (!is_array($data)) continue;

                if ($action === 'draft' && $this->isBlank($data['answer'] ?? null) && $this->isBlank($data['topic_id'] ?? null)) continue;

                // Ô "Làm tròn" của form gửi lên tên "rounding" (0 = nguyên, 1, 2 chữ số thập phân)
                $rounding = $data['rounding'] ?? $data['decimal_places'] ?? null;

                DB::table('exam_questions')->updateOrInsert(
                    ['exam_id' => $exam->id, 'part' => 'III', 'number' => $qNum],
                    [
                        'topic_id' => $data['topic_id'] ?? null,
                        'difficulty' => self::LEVEL_MAP[$data['level'] ?? ''] ?? null,
                        'correct_answer' => $this->isBlank($data['answer'] ?? null)
                            ? null
                            : str_replace(',', '.', trim($data['answer'])),
                        'decimal_places' => $this->isBlank($rounding) ? 2 : (int) $rounding,
                        'updated_at' => now(),
                    ]
                );
            }

            $exam->update(['status' => $action === 'publish' ? 'published' : 'draft']);
        });

        $message = $action === 'publish'
            ? 'Đã cấu hình đáp án và công bố đề thi thành công.'
            : 'Đã lưu nháp cấu hình. Bạn có thể tiếp tục chỉnh sửa sau.';

        // Lưu nháp: ở lại trang cấu hình để làm tiếp; công bố: về danh sách đề
        return $action === 'publish'
            ? redirect()->route('teacher.exams.index')->with('success', $message)
            : redirect()->route('teacher.exams.config-answers', $exam->id)->with('success', $message);
    }

    // Danh sách đề thi kèm bộ lọc
    public function index(Request $request)
    {
        $query = Exam::where('created_by', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $exams = $query->orderByDesc('created_at')->paginate(10);

        return view('teacher.exams.index', compact('exams'));
    }

    // Thống kê phổ điểm và tỷ lệ đúng/sai
    public function statistics(Exam $exam)
    {
        $this->ensureOwner($exam);

        $assignedClassIds = $exam->classes()->pluck('classes.id');

        $totalStudents = \App\Models\User::where('role', 'student')
            ->whereIn('class_id', $assignedClassIds)
            ->count();

        $totalAttempts = Attempt::where('exam_id', $exam->id)->count();

        // Tỷ lệ tham gia: số HỌC SINH đã bắt đầu làm / sĩ số (không đếm trùng nhiều lượt)
        $participants = Attempt::where('exam_id', $exam->id)->distinct()->count('student_id');
        $attemptRate = $totalStudents > 0 ? min(100, round(($participants / $totalStudents) * 100)) : 0;

        $submittedStudentIds = Attempt::where('exam_id', $exam->id)
            ->where('status', 'submitted')
            ->pluck('student_id')
            ->unique();

        $notFinishedCount = max(0, $totalStudents - $submittedStudentIds->count());

        $scoreUnder1 = Attempt::where('exam_id', $exam->id)
            ->where('status', 'submitted')
            ->where('total_score', '<', 1)
            ->count();

        $scoreOver5 = Attempt::where('exam_id', $exam->id)
            ->where('status', 'submitted')
            ->where('total_score', '>=', 5)
            ->count();

        $attempts = Attempt::with('user')
            ->where('exam_id', $exam->id)
            ->where('status', 'submitted')
            ->orderBy('total_score', 'desc')
            ->get();

        $scoreDistribution = [
            '< 1' => 0, '< 2' => 0, '< 3' => 0, '< 4' => 0, '< 5' => 0,
            '< 6' => 0, '< 7' => 0, '< 8' => 0, '< 9' => 0, '<= 10' => 0,
        ];

        $totalScoreSum = 0;
        $validAttemptsCount = $attempts->count();

        foreach ($attempts as $attempt) {
            $score = (float) $attempt->total_score;
            $totalScoreSum += $score;

            if ($score < 1) $scoreDistribution['< 1']++;
            elseif ($score < 2) $scoreDistribution['< 2']++;
            elseif ($score < 3) $scoreDistribution['< 3']++;
            elseif ($score < 4) $scoreDistribution['< 4']++;
            elseif ($score < 5) $scoreDistribution['< 5']++;
            elseif ($score < 6) $scoreDistribution['< 6']++;
            elseif ($score < 7) $scoreDistribution['< 7']++;
            elseif ($score < 8) $scoreDistribution['< 8']++;
            elseif ($score < 9) $scoreDistribution['< 9']++;
            else $scoreDistribution['<= 10']++;
        }

        $averageScore = $validAttemptsCount > 0 ? round($totalScoreSum / $validAttemptsCount, 2) : 0;

        $maxCount = max($scoreDistribution);
        $mostFrequentScore = $maxCount > 0 ? array_search($maxCount, $scoreDistribution) : '-';

        // Tỷ lệ đúng theo từng câu: "đúng" = đạt ĐIỂM TỐI ĐA của câu
        // (Phần I 0,25; Phần II 1,0 tức đúng cả 4 ý; Phần III 0,5)
        $questionStats = DB::table('exam_questions')
            ->where('exam_questions.exam_id', $exam->id)
            ->leftJoin('attempt_answers', 'exam_questions.id', '=', 'attempt_answers.question_id')
            ->select(
                'exam_questions.id',
                'exam_questions.part',
                'exam_questions.number',
                DB::raw('COUNT(attempt_answers.id) as total_answers'),
                DB::raw("SUM(CASE WHEN attempt_answers.score >= (CASE exam_questions.part WHEN 'I' THEN 0.25 WHEN 'II' THEN 1 ELSE 0.5 END) THEN 1 ELSE 0 END) as correct_answers")
            )
            ->groupBy('exam_questions.id', 'exam_questions.part', 'exam_questions.number')
            ->orderBy('exam_questions.part')
            ->orderBy('exam_questions.number')
            ->get()
            ->map(function ($q) {
                $q->correct_answers = (int) $q->correct_answers;
                $q->incorrect_answers = $q->total_answers - $q->correct_answers;
                $q->correct_rate = $q->total_answers > 0 ? round(($q->correct_answers / $q->total_answers) * 100, 1) : 0;

                return $q;
            });

        return view('teacher.exams.statistics', compact(
            'exam',
            'totalStudents',
            'totalAttempts',
            'attemptRate',
            'notFinishedCount',
            'scoreUnder1',
            'scoreOver5',
            'scoreDistribution',
            'averageScore',
            'mostFrequentScore',
            'maxCount',
            'questionStats',
            'attempts'
        ));
    }

    public function edit(Exam $exam)
    {
        $this->ensureOwner($exam);

        $submittedCount = DB::table('attempts')
            ->where('exam_id', $exam->id)
            ->where('status', 'submitted')
            ->count();

        $questions = DB::table('exam_questions')
            ->where('exam_id', $exam->id)
            ->get()
            ->groupBy('part');

        $part1 = $questions->get('I', collect())->keyBy('number');
        $part2 = $questions->get('II', collect())->keyBy('number');
        $part3 = $questions->get('III', collect())->keyBy('number');

        return view('teacher.exams.edit', compact('exam', 'part1', 'part2', 'part3', 'submittedCount'));
    }

    public function update(Request $request, Exam $exam, GradingService $gradingService)
    {
        $this->ensureOwner($exam);

        $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'status' => ['required', Rule::in(['draft', 'published'])],
            'grade' => ['nullable', Rule::in(['10', '11', '12'])],
            'type' => ['nullable', Rule::in(self::EXAM_TYPES)],
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
            'part1.*' => ['nullable', 'in:A,B,C,D'],
            'part2.*.*' => ['nullable', 'in:Đ,S'],
            'part3.*' => ['nullable', 'regex:' . self::NUMERIC_REGEX],
        ], [
            'title.required' => 'Tên đề thi không được để trống.',
            'duration_minutes.min' => 'Thời gian làm bài tối thiểu là 15 phút.',
            'duration_minutes.max' => 'Thời gian làm bài tối đa là 300 phút.',
            'pdf_file.mimes' => 'File tải lên phải có định dạng .pdf.',
            'pdf_file.max' => 'Dung lượng file không được vượt quá 10MB.',
            'part3.*.regex' => 'Đáp án Phần III phải là một số (ví dụ 3.14 hoặc -2).',
        ]);

        $submittedAttempts = DB::table('attempts')
            ->where('exam_id', $exam->id)
            ->where('status', 'submitted')
            ->pluck('id');

        $submittedCount = $submittedAttempts->count();

        // Khối và loại đề chỉ đổi được khi chưa có ai nộp
        $newGrade = ($submittedCount == 0 && $request->filled('grade')) ? $request->grade : $exam->grade;
        $newType = ($submittedCount == 0 && $request->filled('type')) ? $request->type : $exam->type;

        if ($newType === 'Thi thử THPT QG' && (string) $newGrade !== '12') {
            return back()->with('error', 'Loại đề Thi thử THPT QG chỉ dành cho khối 12.')->withInput();
        }

        // Điều kiện công bố: đủ 22 đáp án (số 0 là đáp án hợp lệ nên không dùng empty())
        if ($request->status === 'published') {
            $missing = [];
            for ($i = 1; $i <= 12; $i++) {
                if ($this->isBlank($request->input("part1.$i"))) $missing[] = "Phần I - Câu $i";
            }
            for ($i = 1; $i <= 4; $i++) {
                foreach (['a', 'b', 'c', 'd'] as $sub) {
                    if ($this->isBlank($request->input("part2.$i.$sub"))) $missing[] = "Phần II - Câu $i ý $sub";
                }
            }
            for ($i = 1; $i <= 6; $i++) {
                if ($this->isBlank($request->input("part3.$i"))) $missing[] = "Phần III - Câu $i";
            }

            if (count($missing) > 0) {
                return back()->with('error', 'Không thể xuất bản! Chưa điền đáp án cho: ' . implode(', ', $missing))->withInput();
            }
        }

        $oldPdf = $exam->pdf_path;
        $newPdf = null;

        DB::beginTransaction();

        try {
            $exam->title = $request->title;
            $exam->duration_minutes = $request->duration_minutes;
            $exam->status = $request->status;
            $exam->grade = $newGrade;
            $exam->type = $newType;

            if ($request->hasFile('pdf_file')) {
                $newPdf = $request->file('pdf_file')->store('private/exams');
                $exam->pdf_path = $newPdf;
            }
            $exam->save();

            $changes = [];

            // So sánh với đáp án cũ; chỉ ghi và ghi nhật ký khi có thay đổi thật
            $updateAnswer = function (string $part, int $num, $newAnswer) use ($exam, &$changes) {
                $new = $newAnswer === null ? '' : trim((string) $newAnswer);

                $oldQ = DB::table('exam_questions')
                    ->where('exam_id', $exam->id)->where('part', $part)->where('number', $num)->first();
                $old = $oldQ ? (string) ($oldQ->correct_answer ?? '') : '';

                $oldCompare = $old;
                if ($part === 'III') {
                    $new = str_replace(',', '.', $new);
                    $oldCompare = str_replace(',', '.', $old);
                }

                if ($oldCompare !== $new) {
                    $changes[] = [
                        'question' => "Câu $num ($part)",
                        'old' => $old === '' ? null : $old,
                        'new' => $new === '' ? null : $new,
                    ];

                    DB::table('exam_questions')->updateOrInsert(
                        ['exam_id' => $exam->id, 'part' => $part, 'number' => $num],
                        ['correct_answer' => $new === '' ? null : $new, 'updated_at' => now()]
                    );
                }
            };

            foreach ((array) $request->input('part1', []) as $qNum => $answer) {
                if (in_array((int) $qNum, range(1, 12), true)) $updateAnswer('I', (int) $qNum, $answer);
            }

            foreach ((array) $request->input('part2', []) as $qNum => $subs) {
                if (!in_array((int) $qNum, range(1, 4), true) || !is_array($subs)) continue;

                $picked = [];
                foreach (['a', 'b', 'c', 'd'] as $s) {
                    if (!$this->isBlank($subs[$s] ?? null)) $picked[$s] = $subs[$s];
                }
                $updateAnswer('II', (int) $qNum, $picked ? json_encode($picked) : null);
            }

            foreach ((array) $request->input('part3', []) as $qNum => $answer) {
                if (in_array((int) $qNum, range(1, 6), true)) $updateAnswer('III', (int) $qNum, $answer);
            }

            // Có đáp án thay đổi và đã có bài nộp: chấm lại từng lượt bằng GradingService
            if (count($changes) > 0 && $submittedCount > 0) {
                foreach ($submittedAttempts as $attemptId) {
                    $gradingService->gradeAttempt((int) $attemptId);
                }
            }

            if (count($changes) > 0) {
                DB::table('activity_logs')->insert([
                    'user_id' => Auth::id(),
                    'action' => 'update_exam_answers',
                    'detail' => json_encode([
                        'exam_id' => $exam->id,
                        'changes' => $changes,
                        'regraded_attempts' => $submittedCount,
                    ], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            // Chỉ xóa file PDF cũ SAU KHI đã commit thành công
            if ($newPdf && $oldPdf) {
                Storage::delete($oldPdf);
            }

            return redirect()->route('teacher.exams.index')->with('success', 'Cập nhật đề thi thành công!');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($newPdf) {
                Storage::delete($newPdf);
            }

            report($e);

            return back()->with('error', 'Có lỗi xảy ra, hệ thống đã hoàn tác. Vui lòng thử lại.')->withInput();
        }
    }

    public function destroy(Exam $exam)
    {
        $this->ensureOwner($exam);

        // Khóa ngoại RESTRICT: đề đã có lượt làm thì không xóa được (và không nên xóa điểm của học sinh)
        $attemptCount = DB::table('attempts')->where('exam_id', $exam->id)->count();
        if ($attemptCount > 0) {
            return back()->with('error', "Đề này đã có {$attemptCount} lượt làm bài nên không thể xóa. Nếu không muốn học sinh làm tiếp, hãy chuyển đề về Bản nháp.");
        }

        $pdf = $exam->pdf_path;
        $exam->delete(); // câu hỏi và liên kết lớp tự xóa theo (ON DELETE CASCADE)

        if ($pdf) {
            Storage::delete($pdf);
        }

        return redirect()->route('teacher.exams.index')->with('success', 'Đã xóa đề thi thành công!');
    }
}