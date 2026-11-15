<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TopicController extends Controller
{
    // Trang danh sách: lọc theo khối, trạng thái và tìm theo tên
    public function index(Request $request)
    {
        $query = Topic::query();

        $grade = (string) $request->input('grade', '');
        if (in_array($grade, ['10', '11', '12'], true)) {
            $query->where('grade', $grade);
        }

        $status = (string) $request->input('status', '');
        if ($status === 'active') {
            $query->where('is_active', 1);
        } elseif ($status === 'inactive') {
            $query->where('is_active', 0);
        }

        $keyword = trim((string) $request->input('q', ''));
        if ($keyword !== '') {
            $query->where('name', 'like', '%' . addcslashes($keyword, '%_\\') . '%');
        }

        // Theo khối, chuyên đề đang hoạt động đứng trước, rồi theo tên
        $topics = $query->orderBy('grade')->orderByDesc('is_active')->orderBy('name')->get();

        // Số chuyên đề theo từng khối (không phụ thuộc bộ lọc) để hiện trên các tab
        $gradeCounts = Topic::select('grade', DB::raw('COUNT(*) as total'))->groupBy('grade')->pluck('total', 'grade');

        return view('admin.topics.index', compact('topics', 'gradeCounts'));
    }

    // Hiển thị form Thêm
    public function create()
    {
        return view('admin.topics.form');
    }

    // Xử lý lưu Thêm mới
    public function store(Request $request)
    {
        $request->validate([
            // Cột name trong CSDL dài 191 ký tự
            'name' => ['required', 'string', 'max:191',
                Rule::unique('topics', 'name')->where('grade', $request->grade)],
            'grade' => 'required|in:10,11,12',
        ], $this->messages());

        Topic::create([
            'name' => $request->name,
            'grade' => $request->grade,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('admin.topics.index')->with('success', 'Thêm chuyên đề thành công!');
    }

    // Hiển thị form Sửa (dùng chung file view với form Thêm)
    public function edit(Topic $topic)
    {
        $usedCount = $this->usedCount($topic);

        return view('admin.topics.form', compact('topic', 'usedCount'));
    }

    // Xử lý Cập nhật
    public function update(Request $request, Topic $topic)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:191',
                Rule::unique('topics', 'name')->where('grade', $request->grade)->ignore($topic->id)],
            'grade' => 'required|in:10,11,12',
        ], $this->messages());

        // Chuyên đề đã gắn vào câu hỏi thì không đổi khối: đề thi chọn chuyên đề theo khối nên sẽ bị lệch
        $usedCount = $this->usedCount($topic);
        if ($usedCount > 0 && (int) $request->grade !== (int) $topic->grade) {
            return back()->withErrors([
                'grade' => "Chuyên đề này đang gắn với $usedCount câu hỏi trong các đề nên không thể đổi khối. Hãy tạo chuyên đề mới ở khối cần thiết.",
            ])->withInput();
        }

        $topic->update([
            'name' => $request->name,
            'grade' => $request->grade,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        // Nút "Bật lại" ở trang danh sách gửi cờ này để hiện thông báo đúng việc vừa làm
        $message = $request->boolean('_reactivate')
            ? "Đã bật lại chuyên đề “{$topic->name}”."
            : 'Cập nhật chuyên đề thành công!';

        return redirect()->route('admin.topics.index')->with('success', $message);
    }

    // Xử lý Vô hiệu hóa (Soft Deactivate theo chuẩn BRADM05-1)
    public function deactivate(Topic $topic)
    {
        $topic->update(['is_active' => 0]);

        return redirect()->route('admin.topics.index')->with('success', "Đã vô hiệu hóa chuyên đề “{$topic->name}”.");
    }

    private function usedCount(Topic $topic): int
    {
        return DB::table('exam_questions')->where('topic_id', $topic->id)->count();
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Tên chuyên đề không được để trống.',
            'name.max' => 'Tên chuyên đề tối đa 191 ký tự.',
            'name.unique' => 'Chuyên đề này đã tồn tại trong khối đã chọn.',
            'grade.required' => 'Hãy chọn khối lớp.',
            'grade.in' => 'Khối lớp phải là 10, 11 hoặc 12.',
        ];
    }
}