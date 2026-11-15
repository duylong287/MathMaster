<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolClass; // Cần import model SchoolClass

class TeacherController extends Controller
{
    public function index()
    {
        $teacherId = Auth::id();
        
        try {
            // Đếm số lượng đề thi của riêng giáo viên này
            $examsCount = DB::table('exams')
                            ->where('created_by', $teacherId)
                            ->select('status', DB::raw('count(*) as count'))
                            ->groupBy('status')
                            ->pluck('count', 'status');
                            
            $openExams = $examsCount['published'] ?? 0;
            $draftExams = $examsCount['draft'] ?? 0;
        } catch (\Exception $e) {
            // Nếu chưa có bảng exams thì mặc định là 0
            $openExams = 0;
            $draftExams = 0;
        }

        // ================= CẬP NHẬT THÊM TẠI ĐÂY =================
        // Lấy danh sách lớp do giáo viên này phụ trách kèm lịch và đếm sĩ số
        $classes = SchoolClass::with('schedules')
                    ->withCount('students')
                    ->where('teacher_id', $teacherId)
                    ->where('status', 'active')
                    ->get();

        // Đừng quên thêm 'classes' vào hàm compact
        return view('teacher.dashboard', compact('openExams', 'draftExams', 'classes'));
    }


    public function showClass(\App\Models\SchoolClass $class)
    {
        // 1. Bảo mật: Chặn nếu giáo viên cố tình xem lớp của người khác
        if ($class->teacher_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập vào lớp học này.');
        }

        // 2. Lấy thông tin lớp kèm lịch học
        $class->load('schedules');

        // 3. Lấy danh sách học sinh thuộc lớp này
        $students = \App\Models\User::where('class_id', $class->id)
                        ->where('role', 'student')
                        ->orderBy('name')
                        ->get();

        return view('teacher.classes.show', compact('class', 'students'));
    }

    /**
     * Giáo viên chỉ được xem học sinh thuộc lớp do chính mình phụ trách.
     */
    private function authorizeStudent(\App\Models\User $student): void
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        $teachesStudent = $student->class_id
            && SchoolClass::where('id', $student->class_id)
                ->where('teacher_id', Auth::id())
                ->exists();

        if (!$teachesStudent) {
            abort(403, 'Học sinh này không thuộc lớp bạn phụ trách.');
        }
    }

    public function studentHistory(\App\Models\User $student)
    {
        $this->authorizeStudent($student);

        // Lấy danh sách các bài làm của học sinh này (chỉ lấy bài đã nộp)
        $attempts = \App\Models\Attempt::with('exam') // Load kèm thông tin đề thi
            ->where('student_id', $student->id)
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'desc')
            ->get();

        return view('teacher.students.history', compact('student', 'attempts'));
    }
    public function profile()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return view('teacher.profile', compact('user'));
    }

    public function updatePassword(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.'
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password)
        ]);

        return back()->with('success', 'Đã cập nhật mật khẩu thành công!');
    }

    public function studentAnalytics(\Illuminate\Http\Request $request, $id, \App\Services\AbilityService $abilityService)
    {
        // 1. Chỉ xem được học sinh thuộc lớp mình dạy
        $student = \App\Models\User::findOrFail($id);
        $this->authorizeStudent($student);
        $userId = $student->id;

        $timeFilter = $request->input('time', '30');
        $startDate = null;
        if ($timeFilter === '30') $startDate = \Carbon\Carbon::now()->subDays(30);
        elseif ($timeFilter === '90') $startDate = \Carbon\Carbon::now()->subDays(90);

        $typeFilter = $request->input('type', '');

        // 2. Học sinh đã từng nộp bài nào chưa
        $hasAnyAttempt = \App\Models\Attempt::where('student_id', $userId)->where('status', 'submitted')->exists();
        if (!$hasAnyAttempt) {
            return view('teacher.student_analytics', ['hasAnyAttempt' => false, 'student' => $student]);
        }

        // 3. Mọi lượt làm sau khi lọc, dùng cho biểu đồ tiến bộ (giống hệt phía học sinh)
        $progressQuery = \App\Models\Attempt::with('exam:id,title,type')
            ->where('student_id', $userId)
            ->where('status', 'submitted');
        if ($startDate) $progressQuery->where('submitted_at', '>=', $startDate);
        if ($typeFilter) {
            $progressQuery->whereHas('exam', fn ($q) => $q->where('type', $typeFilter));
        }
        $allAttempts = $progressQuery->orderBy('submitted_at')->orderBy('id')->get();

        // 4. Lọc xong không còn bài nào
        if ($allAttempts->isEmpty()) {
            return view('teacher.student_analytics', [
                'hasAnyAttempt' => true,
                'hasData'       => false,
                'timeFilter'    => $timeFilter,
                'typeFilter'    => $typeFilter,
                'student'       => $student,
            ]);
        }

        // 5. Tính toán: cùng công thức với StudentController::analytics
        $hasData = true;
        $progressLabels = $allAttempts->map(fn ($a) => \Carbon\Carbon::parse($a->submitted_at)->format('d/m'));
        $progressScores = $allAttempts->map(fn ($a) => (float) $a->total_score);
        $progressTitles = $allAttempts->map(fn ($a) => $a->exam->title ?? '');

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
        $radarData  = $levelStats;

        $validTopics = $topicStats->filter(
            fn ($s) => !in_array($s['level'], ['Chưa đủ dữ liệu', 'Chưa phân loại'])
        );

        $strengths = $validTopics->filter(fn ($s) => in_array($s['level'], ['Khá', 'Giỏi']))
            ->sortByDesc('percentage')->take(3);

        $weaknesses = $validTopics->filter(fn ($s) => in_array($s['level'], ['Yếu', 'Trung bình']))
            ->sortBy('percentage')->take(3);

        $thresholds   = $abilityService->thresholds();
        $minQuestions = \App\Services\AbilityService::MIN_QUESTIONS;

        return view('teacher.student_analytics', compact(
            'student', 'hasAnyAttempt', 'hasData', 'timeFilter', 'typeFilter',
            'examsTakenCount', 'averageScore',
            'topicStats', 'strengths', 'weaknesses',
            'progressLabels', 'progressScores', 'progressTitles',
            'radarData', 'levelStats', 'thresholds', 'minQuestions', 'attemptRows'
        ));
    }

    public function analyticsList(\Illuminate\Http\Request $request)
    {
        $teacherId = \Illuminate\Support\Facades\Auth::id();

        // 1. Lấy danh sách các lớp mà giáo viên này phụ trách
        // THAY ĐỔI 'App\Models\ClassRoom' thành Model lớp học thực tế của bạn
        $teacherClasses = \App\Models\SchoolClass::where('teacher_id', $teacherId)->get();
        $teacherClassIds = $teacherClasses->pluck('id');

        // 2. Khởi tạo query lấy học sinh (chỉ lấy những em thuộc lớp của GV này)
        $query = \App\Models\User::where('role', 'student')
                                 ->whereIn('class_id', $teacherClassIds);

        // 3. Nhận dữ liệu từ bộ lọc
        $classFilter = $request->input('class_id');
        $searchKeyword = $request->input('search');

        // 4. Áp dụng bộ lọc nếu có
        if ($classFilter) {
            $query->where('class_id', $classFilter);
        }

        if ($searchKeyword) {
            $query->where(function($q) use ($searchKeyword) {
                $q->where('name', 'like', '%' . $searchKeyword . '%')
                  ->orWhere('email', 'like', '%' . $searchKeyword . '%');
            });
        }

        // Lấy danh sách cuối cùng kèm theo thông tin lớp
        $students = $query->with('class')->orderBy('name')->get();

        return view('teacher.analytics_list', compact('students', 'teacherClasses', 'classFilter', 'searchKeyword'));
    }
}