<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Exam;
use App\Services\GradingService;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Dung sai kỹ thuật khi nhận bài nộp sau mốc hết giờ (giây).
     * Trình duyệt tự nộp đúng lúc đồng hồ về 0 nên yêu cầu tới máy chủ luôn trễ
     * vài giây do tải trang và đường truyền. Đặt 0 nếu muốn chặn tuyệt đối.
     */
    private const SUBMIT_TOLERANCE_SECONDS = 10;

    // ------------------------------------------------------------------
    // TRANG CHỦ: bài đang làm dở + các đề của lớp mà học sinh chưa làm lần nào
    // ------------------------------------------------------------------
    public function index()
    {
        $user = Auth::user();
        $class = $user->schoolClass;

        $inProgress = collect();
        $newExams = collect();
        $newExamsTotal = 0;

        if ($class) {
            $classExams = Exam::where('status', 'published')
                ->whereHas('classes', fn ($q) => $q->where('classes.id', $class->id));

            $attempts = Attempt::where('student_id', $user->id)->get();

            // Bài đang làm dở: kèm số phút còn lại tính theo đồng hồ máy chủ
            $inProgress = $attempts->where('status', 'in_progress')
                ->sortByDesc('id')
                ->unique('exam_id')
                ->map(function ($attempt) use ($classExams) {
                    $exam = (clone $classExams)->find($attempt->exam_id);
                    if (!$exam) {
                        return null; // đề đã bị rút khỏi lớp
                    }
                    $remaining = now()->diffInSeconds($this->deadline($attempt, $exam), false);

                    return (object) [
                        'exam' => $exam,
                        'remaining_minutes' => $remaining > 0 ? (int) ceil($remaining / 60) : 0,
                    ];
                })
                ->filter()
                ->values();

            // Đề chưa làm lần nào (chưa có lượt nào, kể cả lượt dở dang)
            $newQuery = (clone $classExams)->whereNotIn('id', $attempts->pluck('exam_id')->unique());
            $newExamsTotal = (clone $newQuery)->count();
            $newExams = $newQuery->latest()->take(3)->get();
        }

        return view('student.dashboard', compact('class', 'inProgress', 'newExams', 'newExamsTotal'));
    }

    // ------------------------------------------------------------------
    // DANH SÁCH ĐỀ: chỉ đề đã công bố VÀ được giao cho lớp của học sinh
    // ------------------------------------------------------------------
    public function exams(Request $request)
    {
        $user = Auth::user();
        $class = $user->schoolClass;

        // Chưa có lớp: danh sách rỗng (vẫn truyền đủ biến cho view)
        if (!$class) {
            $exams = collect();
            $grade = $user->grade;
            $attempts = collect();

            return view('student.exams.index', compact('exams', 'grade', 'class', 'attempts'));
        }

        $grade = $class->grade;

        $query = Exam::where('status', 'published')
            ->whereHas('classes', fn ($q) => $q->where('classes.id', $class->id));

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $exams = $query->latest()->paginate(12)->withQueryString();

        $attempts = Attempt::where('student_id', $user->id)->get()->groupBy('exam_id');

        return view('student.exams.index', compact('exams', 'grade', 'class', 'attempts'));
    }

    // ------------------------------------------------------------------
    // HÀM DÙNG CHUNG
    // ------------------------------------------------------------------

    /** Đề phải đã công bố và được giao cho lớp của học sinh đang đăng nhập. */
    private function authorizeExam(Exam $exam): void
    {
        $classId = Auth::user()->class_id;

        $allowed = $classId
            && $exam->status === 'published'
            && $exam->classes()->where('classes.id', $classId)->exists();

        if (!$allowed) {
            abort(403, 'Đề thi này không dành cho bạn hoặc chưa được công bố.');
        }
    }

    private function ownsAttempt(Exam $exam, Attempt $attempt): bool
    {
        return (int) $attempt->student_id === (int) Auth::id()
            && (int) $attempt->exam_id === (int) $exam->id;
    }

    /** Mốc hết giờ tính theo đồng hồ máy chủ. */
    private function deadline(Attempt $attempt, Exam $exam): CarbonInterface
    {
        return $attempt->started_at->copy()->addMinutes((int) $exam->duration_minutes);
    }

    /**
     * Đóng một lượt đã hết giờ mà chưa nộp: chấm với 0 câu trả lời (0 điểm),
     * ghi giờ nộp đúng bằng mốc hết giờ.
     */
    private function closeExpiredAttempt(Attempt $attempt, CarbonInterface $deadline, GradingService $grading): void
    {
        DB::transaction(function () use ($attempt, $deadline, $grading) {
            $locked = DB::table('attempts')->where('id', $attempt->id)->lockForUpdate()->first();

            if (!$locked || $locked->status === 'submitted') {
                return;
            }

            $grading->gradeAttempt($attempt->id);

            DB::table('attempts')->where('id', $attempt->id)->update([
                'status' => 'submitted',
                'submitted_at' => $deadline,
                'updated_at' => now(),
            ]);
        });
    }

    private function redirectToResult(Exam $exam, Attempt $attempt, string $type, string $message)
    {
        return redirect()
            ->route('student.exams.result', ['exam' => $exam->id, 'attempt' => $attempt->id])
            ->with($type, $message);
    }

    // ------------------------------------------------------------------
    // BẮT ĐẦU / VÀO PHÒNG THI
    // ------------------------------------------------------------------
    public function startExam(Request $request, Exam $exam, GradingService $grading)
    {
        $this->authorizeExam($exam);

        $userId = Auth::id();

        $attempt = Attempt::where('student_id', $userId)
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();

        // Lượt dở dang đã quá giờ: đóng lại, báo kết quả, để học sinh chủ động bấm "Làm lại"
        if ($attempt) {
            $deadline = $this->deadline($attempt, $exam);

            if (now()->greaterThanOrEqualTo($deadline)) {
                $this->closeExpiredAttempt($attempt, $deadline, $grading);

                return $this->redirectToResult(
                    $exam,
                    $attempt,
                    'error',
                    'Lượt làm bài trước đã hết giờ và được đóng tự động (không có bài nộp nên 0 điểm). Em có thể bấm "Làm lại đề này" để làm lượt mới.'
                );
            }
        }

        if (!$attempt) {
            $attempt = Attempt::create([
                'student_id' => $userId,
                'exam_id' => $exam->id,
                'started_at' => now(),
                'status' => 'in_progress',
            ]);
        }

        return redirect()->route('student.exams.take', [
            'exam' => $exam->id,
            'attempt' => $attempt->id,
        ]);
    }

    public function takeExam(Exam $exam, Attempt $attempt, GradingService $grading)
    {
        $this->authorizeExam($exam);

        if (!$this->ownsAttempt($exam, $attempt)) {
            abort(403, 'Bạn không có quyền truy cập lượt làm bài này.');
        }

        if ($attempt->status === 'submitted') {
            return $this->redirectToResult($exam, $attempt, 'error', 'Bài này đã được nộp.');
        }

        $deadline = $this->deadline($attempt, $exam);
        $remainingSeconds = $deadline->getTimestamp() - now()->getTimestamp();

        if ($remainingSeconds <= 0) {
            $this->closeExpiredAttempt($attempt, $deadline, $grading);

            return $this->redirectToResult(
                $exam,
                $attempt,
                'error',
                'Đã hết giờ làm bài. Lượt này được đóng tự động.'
            );
        }

        return view('student.exams.take', compact('exam', 'attempt', 'remainingSeconds'));
    }

    // ------------------------------------------------------------------
    // XEM PDF: chỉ khi đề được giao cho mình và đã bấm "Bắt đầu" (có lượt làm)
    // ------------------------------------------------------------------
    public function viewPdf(Request $request, Exam $exam)
    {
        $this->authorizeExam($exam);

        $hasAttempt = Attempt::where('student_id', Auth::id())
            ->where('exam_id', $exam->id)
            ->exists();

        if (!$hasAttempt) {
            abort(403, 'Hãy bấm "Bắt đầu làm bài" trước khi xem đề.');
        }

        if (empty($exam->pdf_path) || !Storage::exists($exam->pdf_path)) {
            abort(404, 'Đề thi này chưa có file PDF đính kèm.');
        }

        $path = Storage::path($exam->pdf_path);

        if ($request->has('download')) {
            $fileName = 'De_Thi_' . \Illuminate\Support\Str::slug($exam->title) . '.pdf';

            return response()->download($path, $fileName);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // ------------------------------------------------------------------
    // NỘP BÀI: lưu đáp án, chấm bằng GradingService (cùng logic với chấm lại)
    // ------------------------------------------------------------------
    public function submitExam(Request $request, Exam $exam, Attempt $attempt, GradingService $grading)
    {
        if (!$this->ownsAttempt($exam, $attempt)) {
            abort(403, 'Bài thi không hợp lệ.');
        }

        if ($attempt->status === 'submitted') {
            return $this->redirectToResult($exam, $attempt, 'error', 'Bài thi này đã được nộp trước đó.');
        }

        // Quá hạn (kể cả dung sai): không nhận đáp án, đóng lượt với 0 điểm
        $deadline = $this->deadline($attempt, $exam);
        if (now()->greaterThan($deadline->copy()->addSeconds(self::SUBMIT_TOLERANCE_SECONDS))) {
            $this->closeExpiredAttempt($attempt, $deadline, $grading);

            return $this->redirectToResult(
                $exam,
                $attempt,
                'error',
                'Đã quá thời gian làm bài nên hệ thống không nhận bài nộp này.'
            );
        }

        $part1 = (array) $request->input('part1', []);
        $part2 = (array) $request->input('part2', []);
        $part3 = (array) $request->input('part3', []);

        $questions = DB::table('exam_questions')->where('exam_id', $exam->id)->get();

        $total = DB::transaction(function () use ($attempt, $questions, $part1, $part2, $part3, $deadline, $grading) {
            // Khóa dòng để hai lần bấm nộp đồng thời không chấm hai lần
            $locked = DB::table('attempts')->where('id', $attempt->id)->lockForUpdate()->first();
            if (!$locked || $locked->status === 'submitted') {
                return null;
            }

            $now = now();
            $rows = [];
            foreach ($questions as $question) {
                $rows[] = [
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'answer' => $this->extractAnswer($question, $part1, $part2, $part3),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows) {
                DB::table('attempt_answers')->upsert($rows, ['attempt_id', 'question_id'], ['answer', 'updated_at']);
            }

            $total = $grading->gradeAttempt($attempt->id, $questions->keyBy('id'));

            DB::table('attempts')->where('id', $attempt->id)->update([
                'status' => 'submitted',
                'submitted_at' => $now->greaterThan($deadline) ? $deadline : $now,
                'updated_at' => $now,
            ]);

            return $total;
        });

        if ($total === null) {
            return $this->redirectToResult($exam, $attempt, 'error', 'Bài thi này đã được nộp trước đó.');
        }

        return $this->redirectToResult(
            $exam,
            $attempt,
            'success',
            'Nộp bài thành công! Tổng điểm của bạn là: ' . number_format($total, 2)
        );
    }

    /**
     * Chuẩn hóa đáp án học sinh gửi lên thành chuỗi lưu trong attempt_answers.answer.
     *  - Phần I  : "A" | "B" | "C" | "D"
     *  - Phần II : JSON {"a":"Đ","b":"S",...} (chỉ các ý đã chọn)
     *  - Phần III: chuỗi học sinh gõ (cắt tối đa 50 ký tự); không làm tròn hộ
     * Trống hoặc không hợp lệ thì trả về null.
     */
    private function extractAnswer(object $question, array $part1, array $part2, array $part3): ?string
    {
        $number = $question->number;

        switch ($question->part) {
            case 'I':
                $value = $part1[$number] ?? null;
                $value = is_string($value) ? strtoupper(trim($value)) : null;

                return in_array($value, ['A', 'B', 'C', 'D'], true) ? $value : null;

            case 'II':
                $row = $part2[$number] ?? null;
                if (!is_array($row)) {
                    return null;
                }

                $picked = [];
                foreach (['a', 'b', 'c', 'd'] as $sub) {
                    $value = $row[$sub] ?? null;
                    if (is_string($value) && in_array(trim($value), ['Đ', 'S'], true)) {
                        $picked[$sub] = trim($value);
                    }
                }

                return $picked ? json_encode($picked, JSON_UNESCAPED_UNICODE) : null;

            case 'III':
                $value = $part3[$number] ?? null;
                if (!is_string($value)) {
                    return null;
                }
                $value = mb_substr(trim($value), 0, 50);

                return $value === '' ? null : $value;
        }

        return null;
    }

    // ------------------------------------------------------------------
    // KẾT QUẢ: chỉ xem được khi bài đã nộp
    // ------------------------------------------------------------------
    public function result(Exam $exam, Attempt $attempt)
    {
        if (!$this->ownsAttempt($exam, $attempt)) {
            abort(403, 'Bạn không có quyền xem kết quả này.');
        }

        // Bài chưa nộp thì không được xem đáp án chuẩn
        if ($attempt->status !== 'submitted') {
            return redirect()
                ->route('student.exams.take', ['exam' => $exam->id, 'attempt' => $attempt->id])
                ->with('error', 'Bạn cần nộp bài trước khi xem kết quả.');
        }

        $questions = DB::table('exam_questions')
            ->where('exam_id', $exam->id)
            ->orderBy('part')
            ->orderBy('number')
            ->get();

        $answers = DB::table('attempt_answers')
            ->where('attempt_id', $attempt->id)
            ->get()
            ->keyBy('question_id');

        return view('student.exams.result', compact('exam', 'attempt', 'questions', 'answers'));
    }

    // --- LỊCH SỬ LÀM BÀI (UC-STU08) ---
    public function history(Request $request)
    {
        $user = Auth::user();
        $grade = $user->grade; // lấy theo lớp nếu cột grade trống (xem User::grade)

        $query = Attempt::with('exam')->where('student_id', $user->id);

        if ($request->filled('search')) {
            $query->whereHas('exam', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->whereHas('exam', function ($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        // Lượt đang làm dở lên đầu, sau đó mới nhất (theo mã lượt, không bị đổi chỗ khi bài được chấm lại).
        // withQueryString(): sang trang 2 vẫn giữ bộ lọc.
        $attempts = $query->orderByRaw("CASE WHEN status = 'in_progress' THEN 1 ELSE 2 END")
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('student.history', compact('attempts', 'grade'));
    }

    public function analytics(Request $request, \App\Services\AbilityService $abilityService)
    {
        $userId = Auth::id();

        $timeFilter = $request->input('time', '30');
        $startDate = null;
        if ($timeFilter === '30') $startDate = \Carbon\Carbon::now()->subDays(30);
        elseif ($timeFilter === '90') $startDate = \Carbon\Carbon::now()->subDays(90);

        $typeFilter = $request->input('type', '');

        // 1. Học sinh đã từng nộp bài nào chưa (quyết định có hiện bộ lọc hay không)
        $hasAnyAttempt = Attempt::where('student_id', $userId)->where('status', 'submitted')->exists();
        if (!$hasAnyAttempt) {
            return view('student.analytics', ['hasAnyAttempt' => false]);
        }

        // 2. Mọi lượt làm sau khi lọc, dùng cho biểu đồ tiến bộ
        $progressQuery = Attempt::with('exam:id,title,type')
            ->where('student_id', $userId)
            ->where('status', 'submitted');
        if ($startDate) $progressQuery->where('submitted_at', '>=', $startDate);
        if ($typeFilter) {
            $progressQuery->whereHas('exam', fn ($q) => $q->where('type', $typeFilter));
        }
        $allAttempts = $progressQuery->orderBy('submitted_at')->orderBy('id')->get();

        // 3. Lọc xong không còn bài nào
        if ($allAttempts->isEmpty()) {
            return view('student.analytics', [
                'hasAnyAttempt' => true,
                'hasData'       => false,
                'timeFilter'    => $timeFilter,
                'typeFilter'    => $typeFilter,
            ]);
        }

        // 4. Tính toán
        $hasData = true;
        $progressLabels = $allAttempts->map(fn ($a) => \Carbon\Carbon::parse($a->submitted_at)->format('d/m'));
        $progressScores = $allAttempts->map(fn ($a) => (float) $a->total_score);
        $progressTitles = $allAttempts->map(fn ($a) => $a->exam->title ?? '');

        // Lượt GẦN NHẤT của mỗi đề: cùng nguồn với thống kê chuyên đề và mức độ
        $latestAttempts  = $abilityService->getLatestAttempts($userId, $startDate, null, $typeFilter);
        $examsTakenCount = $latestAttempts->count();
        $averageScore    = $examsTakenCount > 0 ? round($latestAttempts->avg('total_score'), 2) : 0;

        // Bảng điểm: từng lượt làm trong bộ lọc hiện tại, mới nhất lên đầu
        $latestIds = $latestAttempts->pluck('id')->all();
        $attemptRows = $allAttempts->map(function ($a) use ($latestIds) {
            $minutes = null;
            if ($a->started_at && $a->submitted_at) {
                $minutes = (int) round(abs(\Carbon\Carbon::parse($a->submitted_at)->diffInSeconds(\Carbon\Carbon::parse($a->started_at))) / 60);
            }
            return [
                'id'           => $a->id,
                'exam_id'      => $a->exam_id,
                'title'        => $a->exam->title ?? '',
                'type'         => $a->exam->type ?? '',
                'submitted_at' => $a->submitted_at,
                'score'        => (float) $a->total_score,
                'minutes'      => $minutes,
                'counted'      => in_array($a->id, $latestIds),
            ];
        })->reverse()->values();

        $topicStats = $abilityService->getStudentTopicStats($userId, $startDate, null, $typeFilter);
        $levelStats = $abilityService->getCognitiveStats($userId, $startDate, null, $typeFilter);
        $radarData  = $levelStats; // giữ tên cũ cho view

        $validTopics = $topicStats->filter(
            fn ($s) => !in_array($s['level'], ['Chưa đủ dữ liệu', 'Chưa phân loại'])
        );

        $strengths = $validTopics->filter(fn ($s) => in_array($s['level'], ['Khá', 'Giỏi']))
            ->sortByDesc('percentage')->take(3);

        // Chỉ chuyên đề Yếu hoặc Trung bình mới là "cần chú ý"
        $weaknesses = $validTopics->filter(fn ($s) => in_array($s['level'], ['Yếu', 'Trung bình']))
            ->sortBy('percentage')->take(3);

        $thresholds   = $abilityService->thresholds();
        $minQuestions = \App\Services\AbilityService::MIN_QUESTIONS;

        return view('student.analytics', compact(
            'hasAnyAttempt', 'hasData', 'timeFilter', 'typeFilter',
            'examsTakenCount', 'averageScore',
            'topicStats', 'strengths', 'weaknesses',
            'progressLabels', 'progressScores', 'progressTitles',
            'radarData', 'levelStats', 'thresholds', 'minQuestions', 'attemptRows'
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        if ($user->class_id) {
            $user->load('class');
        }

        return view('student.profile', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        Auth::user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return back()->with('success', 'Đã cập nhật mật khẩu thành công!');
    }
}