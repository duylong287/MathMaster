<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    // --- DANH SÁCH LỚP: lọc theo khối, trạng thái và tìm theo tên ---
    public function index(Request $request)
    {
        $status = (string) $request->input('status', '');
        $grade = (string) $request->input('grade', '');
        $keyword = trim((string) $request->input('q', ''));

        $query = SchoolClass::with(['teacher', 'schedules'])
            ->withCount('students'); // Tự động đếm số học sinh trong lớp

        $this->applyStatus($query, $status);

        if (in_array($grade, ['10', '11', '12'], true)) {
            $query->where('grade', $grade);
        }
        if ($keyword !== '') {
            $query->where('name', 'like', '%' . addcslashes($keyword, '%_\\') . '%');
        }

        // withQueryString(): sang trang 2 vẫn giữ bộ lọc
        $classes = $query->orderBy('grade')->orderBy('name')->paginate(15)->withQueryString();

        // Mỗi buổi học một dòng, xếp theo thứ trong tuần rồi giờ bắt đầu (VD: "Thứ 2: 17g30-19g")
        $dayOrder = array_flip(self::DAYS);
        foreach ($classes as $cls) {
            $lines = [];
            $sorted = $cls->schedules->sort(function ($a, $b) use ($dayOrder) {
                return [$dayOrder[$a->day_of_week] ?? 9, $a->start_time] <=> [$dayOrder[$b->day_of_week] ?? 9, $b->start_time];
            });
            foreach ($sorted as $sch) {
                // Đổi định dạng giờ, ví dụ: 17:30:00 -> 17g30; phút 00 thì chỉ hiện giờ (19g00 -> 19g)
                $start = str_replace('g00', 'g', \Carbon\Carbon::parse($sch->start_time)->format('H\gi'));
                $end = str_replace('g00', 'g', \Carbon\Carbon::parse($sch->end_time)->format('H\gi'));
                $lines[] = "{$sch->day_of_week}: {$start}-{$end}";
            }
            $cls->schedule_lines = $lines;
            $cls->formatted_schedule = implode('; ', $lines);
        }

        // Số lớp theo từng khối trên các tab (theo trạng thái đang xem, không phụ thuộc khối và từ khóa)
        $countQuery = SchoolClass::query();
        $this->applyStatus($countQuery, $status);
        $gradeCounts = $countQuery->selectRaw('grade, COUNT(*) as total')->groupBy('grade')->pluck('total', 'grade');

        return view('admin.classes.index', compact('classes', 'gradeCounts'));
    }

    // Mặc định (không chọn) chỉ hiện lớp đang hoạt động; "closed" là lớp đã đóng; "all" là tất cả
    private function applyStatus($query, string $status): void
    {
        if ($status === 'closed') {
            $query->where('status', 'closed');
        } elseif ($status !== 'all') {
            $query->where('status', 'active');
        }
    }

    // --- HIỂN THỊ FORM THÊM MỚI LỚP HỌC ---
    public function create()
    {
        // Chỉ cho chọn giáo viên đang hoạt động (chưa bị khóa)
        $teachers = \App\Models\User::where('role', 'teacher')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.classes.create', compact('teachers'));
    }

    // --- XỬ LÝ LƯU DỮ LIỆU ---
    public function store(Request $request)
    {
        // 1. Validate dữ liệu cơ bản
        $request->validate($this->rules(), $this->messages());

        // 2. Kiểm tra tên lớp không trùng với lớp ĐANG HOẠT ĐỘNG khác
        $isDuplicate = \App\Models\SchoolClass::where('name', $request->name)
            ->where('status', 'active')
            ->exists();
            
        if ($isDuplicate) {
            return back()->withErrors(['name' => 'Tên lớp này đã tồn tại và đang hoạt động.'])->withInput();
        }

        // 3. Kiểm tra các buổi học trong CÙNG MỘT LỚP không được chồng giờ nhau
        $days = [];
        foreach ($request->schedules as $sch) {
            $days[$sch['day_of_week']][] = [
                'start' => $sch['start_time'],
                'end' => $sch['end_time']
            ];
        }
        foreach ($days as $day => $times) {
            // Sắp xếp các buổi trong cùng 1 ngày theo giờ bắt đầu
            usort($times, function($a, $b) { return strcmp($a['start'], $b['start']); });
            for ($i = 0; $i < count($times) - 1; $i++) {
                // Nếu giờ bắt đầu của ca sau < giờ kết thúc của ca trước -> Chồng giờ
                if ($times[$i + 1]['start'] < $times[$i]['end']) {
                    return back()->withErrors(['schedules' => "Lịch học ngày $day bị chồng giờ lên nhau. Vui lòng kiểm tra lại!"])->withInput();
                }
            }
        }

        $warnings = [];

        // 4. Báo nhắc: Tên lớp không bắt đầu bằng số khối
        if (!str_starts_with($request->name, (string)$request->grade)) {
            $warnings[] = "Tên lớp '{$request->name}' không bắt đầu bằng số khối đã chọn (Khối {$request->grade}).";
        }

       // 5. Kiểm tra: Giáo viên bị trùng lịch với lớp khác (CHẶN CỨNG, KHÔNG CHO LƯU)
        if ($request->filled('teacher_id')) {
            $existingSchedules = \App\Models\ClassSchedule::whereHas('schoolClass', function($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id)->where('status', 'active');
            })->with('schoolClass')->get();

            foreach ($request->schedules as $newSch) {
                foreach ($existingSchedules as $exSch) {
                    if ($newSch['day_of_week'] == $exSch->day_of_week) {
                        $newStart = substr($newSch['start_time'], 0, 5);
                        $newEnd = substr($newSch['end_time'], 0, 5);
                        $exStart = substr($exSch->start_time, 0, 5);
                        $exEnd = substr($exSch->end_time, 0, 5);

                        // Công thức logic kiểm tra 2 khoảng thời gian có giao nhau (chồng lấp) không
                        if ($newStart < $exEnd && $exStart < $newEnd) {
                            $className = $exSch->schoolClass->name;
                            return back()->withErrors([
                                'teacher_overlap' => "Lỗi: Giáo viên này đang kẹt lịch dạy lớp $className vào {$exSch->day_of_week} ({$exStart}-{$exEnd}). Vui lòng chọn giáo viên khác hoặc đổi lại giờ học!"
                            ])->withInput();
                        }
                    }
                }
            }
        }

        // 6. Lưu dữ liệu an toàn với Transaction
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Tạo lớp học
            $schoolClass = \App\Models\SchoolClass::create([
                'name' => $request->name,
                'grade' => $request->grade,
                'teacher_id' => $request->teacher_id,
                'status' => 'active',
            ]);

            // Lưu các lịch học
            foreach ($request->schedules as $sch) {
                \App\Models\ClassSchedule::create([
                    'class_id' => $schoolClass->id,
                    'day_of_week' => $sch['day_of_week'],
                    'start_time' => $sch['start_time'],
                    'end_time' => $sch['end_time'],
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            // Xử lý thông báo thành công kèm theo cảnh báo (nếu có)
            $successMsg = 'Thêm lớp học thành công!';
            if (count($warnings) > 0) {
                $request->session()->flash('warning_list', $warnings);
            }

            return redirect()->route('admin.classes.index')->with('success', $successMsg);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi lưu: ' . $e->getMessage())->withInput();
        }
    }

    // --- HIỂN THỊ FORM SỬA LỚP HỌC ---
    public function edit(\App\Models\SchoolClass $class)
    {
        $teachers = \App\Models\User::where('role', 'teacher')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Nạp sẵn lịch học, giáo viên hiện tại (có thể đã bị khóa) và số học sinh của lớp
        $class->load(['schedules', 'teacher'])->loadCount('students');

        return view('admin.classes.edit', compact('class', 'teachers'));
    }

    // --- XỬ LÝ CẬP NHẬT LỚP HỌC ---
    public function update(Request $request, \App\Models\SchoolClass $class)
    {
        // 1. Validate dữ liệu cơ bản
        $request->validate($this->rules(), $this->messages());

        // 2. Kiểm tra trùng tên (Loại trừ chính lớp đang sửa)
        $isDuplicate = \App\Models\SchoolClass::where('name', $request->name)
            ->where('status', 'active')
            ->where('id', '!=', $class->id)
            ->exists();
            
        if ($isDuplicate) {
            return back()->withErrors(['name' => 'Tên lớp này đã tồn tại và đang hoạt động.'])->withInput();
        }

        // 3. Ràng buộc khắt khe: Không cho đổi khối nếu lớp đã có học sinh
        $studentCount = $class->students()->count();
        if ($studentCount > 0 && $request->grade != $class->grade) {
            return back()->withErrors(['grade' => "Lớp đang có $studentCount học sinh. Không thể đổi khối để tránh sai lệch dữ liệu khối lớp của các em!"])->withInput();
        }

        // 4. Kiểm tra các buổi học trong CÙNG MỘT LỚP không được chồng giờ nhau
        $days = [];
        foreach ($request->schedules as $sch) {
            $days[$sch['day_of_week']][] = [
                'start' => $sch['start_time'],
                'end' => $sch['end_time']
            ];
        }
        foreach ($days as $day => $times) {
            usort($times, function($a, $b) { return strcmp($a['start'], $b['start']); });
            for ($i = 0; $i < count($times) - 1; $i++) {
                if ($times[$i + 1]['start'] < $times[$i]['end']) {
                    return back()->withErrors(['schedules' => "Lịch học ngày $day bị chồng giờ lên nhau. Vui lòng kiểm tra lại!"])->withInput();
                }
            }
        }

        $warnings = [];

        // Cảnh báo: Tên lớp không bắt đầu bằng số khối
        if (!str_starts_with($request->name, (string)$request->grade)) {
            $warnings[] = "Tên lớp '{$request->name}' không bắt đầu bằng số khối đã chọn (Khối {$request->grade}).";
        }

        // 5. Kiểm tra kẹt lịch giáo viên (Loại trừ lịch CỦA CHÍNH LỚP NÀY)
        if ($request->filled('teacher_id')) {
            $existingSchedules = \App\Models\ClassSchedule::whereHas('schoolClass', function($q) use ($request, $class) {
                $q->where('teacher_id', $request->teacher_id)
                  ->where('status', 'active')
                  ->where('id', '!=', $class->id); // Điều kiện quan trọng: Bỏ qua lớp hiện tại
            })->with('schoolClass')->get();

            foreach ($request->schedules as $newSch) {
                foreach ($existingSchedules as $exSch) {
                    if ($newSch['day_of_week'] == $exSch->day_of_week) {
                        $newStart = substr($newSch['start_time'], 0, 5);
                        $newEnd = substr($newSch['end_time'], 0, 5);
                        $exStart = substr($exSch->start_time, 0, 5);
                        $exEnd = substr($exSch->end_time, 0, 5);

                        if ($newStart < $exEnd && $exStart < $newEnd) {
                            $className = $exSch->schoolClass->name;
                            return back()->withErrors([
                                'teacher_overlap' => "Lỗi: Giáo viên này đang kẹt lịch dạy lớp $className vào {$exSch->day_of_week} ({$exStart}-{$exEnd}). Vui lòng chọn giáo viên khác hoặc đổi lại giờ học!"
                            ])->withInput();
                        }
                    }
                }
            }
        }

        // 6. Lưu dữ liệu an toàn với Transaction
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Cập nhật thông tin lớp
            $class->update([
                'name' => $request->name,
                'grade' => $request->grade,
                'teacher_id' => $request->teacher_id,
            ]);

            // XÓA lịch học cũ và TẠO LẠI lịch học mới
            $class->schedules()->delete();
            foreach ($request->schedules as $sch) {
                \App\Models\ClassSchedule::create([
                    'class_id' => $class->id,
                    'day_of_week' => $sch['day_of_week'],
                    'start_time' => $sch['start_time'],
                    'end_time' => $sch['end_time'],
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            $successMsg = 'Cập nhật lớp học thành công!';
            if (count($warnings) > 0) {
                $request->session()->flash('warning_list', $warnings);
            }

            return redirect()->route('admin.classes.index')->with('success', $successMsg);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi lưu: ' . $e->getMessage())->withInput();
        }
    }

    // --- XỬ LÝ ĐÓNG LỚP HỌC ---
    public function close(\App\Models\SchoolClass $class)
    {
        // 1. Kiểm tra bảo mật ở Backend: Tuyệt đối không đóng nếu còn học sinh
        $studentCount = $class->students()->count();
        
        if ($studentCount > 0) {
            return back()->with('error', "Hệ thống từ chối: Lớp {$class->name} vẫn còn $studentCount học sinh. Hãy chuyển các em sang lớp khác trước khi đóng!");
        }

        // 2. Nếu đã an toàn, chuyển trạng thái lớp thành 'closed' (đã đóng)
        try {
            $class->update([
                'status' => 'closed'
            ]);

            return redirect()->route('admin.classes.index')->with('success', "Đã đóng lớp {$class->name} thành công! Lớp này sẽ không còn hiển thị ở trạng thái hoạt động nữa.");
            
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi đóng lớp: ' . $e->getMessage());
        }
    }

    // --- XEM CHI TIẾT LỚP (DANH SÁCH HỌC SINH) ---
    public function show(\App\Models\SchoolClass $class)
    {
        // Tải trước thông tin giáo viên, lịch học và phân trang danh sách học sinh
        $class->load(['teacher', 'schedules']);
        
        // Lấy danh sách học sinh thuộc lớp này
        $students = $class->students()->orderBy('name')->paginate(20);

        return view('admin.classes.show', compact('class', 'students'));
    }

    // --- QUY TẮC VÀ THÔNG BÁO KIỂM TRA DỮ LIỆU (DÙNG CHUNG CHO THÊM VÀ SỬA) ---
    private const DAYS = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|in:10,11,12',
            // Phải là tài khoản giáo viên (không nhận id của học sinh/admin gửi lên)
            'teacher_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'teacher')],
            'schedules' => 'required|array|min:1',
            'schedules.*.day_of_week' => ['required', Rule::in(self::DAYS)],
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Tên lớp không được để trống.',
            'name.max' => 'Tên lớp tối đa 255 ký tự.',
            'grade.required' => 'Hãy chọn khối.',
            'grade.in' => 'Khối phải là 10, 11 hoặc 12.',
            'teacher_id.exists' => 'Giáo viên được chọn không hợp lệ. Hãy chọn người khác hoặc để trống.',
            'schedules.required' => 'Phải có ít nhất một buổi học.',
            'schedules.min' => 'Phải có ít nhất một buổi học.',
            'schedules.*.day_of_week.required' => 'Hãy chọn thứ cho mỗi buổi học.',
            'schedules.*.day_of_week.in' => 'Thứ trong lịch học không hợp lệ.',
            'schedules.*.start_time.required' => 'Hãy nhập giờ bắt đầu cho mỗi buổi học.',
            'schedules.*.start_time.date_format' => 'Giờ bắt đầu không đúng định dạng (giờ:phút).',
            'schedules.*.end_time.required' => 'Hãy nhập giờ kết thúc cho mỗi buổi học.',
            'schedules.*.end_time.date_format' => 'Giờ kết thúc không đúng định dạng (giờ:phút).',
            'schedules.*.end_time.after' => 'Giờ kết thúc phải diễn ra sau giờ bắt đầu.',
        ];
    }
}