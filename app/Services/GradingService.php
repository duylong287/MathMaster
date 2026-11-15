<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Chấm điểm theo cấu trúc đề THPT 3 phần.
 * Dùng query builder nên không phụ thuộc vào tên model của dự án.
 */
class GradingService
{
    private const PART_I_POINT = 0.25;
    private const PART_III_POINT = 0.5;

    /** Điểm Phần II theo số ý đúng: 0, 1, 2, 3, 4 ý */
    private const PART_II_POINTS = [0.0, 0.1, 0.25, 0.5, 1.0];

    /**
     * Chấm một câu. $question cần có: part, correct_answer, decimal_places.
     */
    public function gradeAnswer(object $question, ?string $answer): float
    {
        if ($answer === null || trim($answer) === '' || blank($question->correct_answer)) {
            return 0.0;
        }

        return match ($question->part) {
            'I' => $this->gradePartI($question, $answer),
            'II' => $this->gradePartII($question, $answer),
            'III' => $this->gradePartIII($question, $answer),
            default => 0.0,
        };
    }

    /**
     * Chấm toàn bộ một lượt làm bài, cập nhật điểm từng câu và tổng điểm.
     * Câu học sinh chưa trả lời sẽ được tạo dòng với điểm 0.
     * Trả về tổng điểm.
     */
    public function gradeAttempt(int $attemptId, ?Collection $questions = null): float
    {
        if ($questions === null) {
            $examId = DB::table('attempts')->where('id', $attemptId)->value('exam_id');
            $questions = DB::table('exam_questions')->where('exam_id', $examId)->get()->keyBy('id');
        }

        $answers = DB::table('attempt_answers')
            ->where('attempt_id', $attemptId)
            ->get()
            ->keyBy('question_id');

        $total = 0.0;

        foreach ($questions as $question) {
            $row = $answers->get($question->id);
            $score = $this->gradeAnswer($question, $row->answer ?? null);

            if ($row) {
                DB::table('attempt_answers')
                    ->where('id', $row->id)
                    ->update(['score' => $score, 'updated_at' => now()]);
            } else {
                DB::table('attempt_answers')->insert([
                    'attempt_id' => $attemptId,
                    'question_id' => $question->id,
                    'answer' => null,
                    'score' => $score,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $total += $score;
        }

        $total = round($total, 2);

        DB::table('attempts')
            ->where('id', $attemptId)
            ->update(['total_score' => $total, 'updated_at' => now()]);

        return $total;
    }

    /**
     * Chấm lại tất cả lượt đã nộp của một đề (dùng khi giáo viên sửa đáp án).
     * Chạy trong một transaction: lỗi giữa chừng thì hoàn tác toàn bộ.
     * Trả về số lượt đã chấm lại.
     */
    public function regradeExam(int $examId): int
    {
        return DB::transaction(function () use ($examId) {
            $questions = DB::table('exam_questions')
                ->where('exam_id', $examId)
                ->get()
                ->keyBy('id');

            $attemptIds = DB::table('attempts')
                ->where('exam_id', $examId)
                ->where('status', 'submitted')
                ->pluck('id');

            foreach ($attemptIds as $attemptId) {
                $this->gradeAttempt((int) $attemptId, $questions);
            }

            return $attemptIds->count();
        });
    }

    /**
     * Tách chuỗi Đúng/Sai thành mảng true/false/null.
     * Chấp nhận "Đ-S-Đ-S", "Đ,S,Đ,S", "ĐSĐS", "D-S-D-S", "T-F-T-F"...
     * Ý bỏ trống hoặc không nhận diện được sẽ là null.
     */
    public function parseTrueFalse(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $value = trim($value);

        // Dạng JSON, ví dụ {"a":"Đ","b":"S","c":"Đ","d":"S"}: lấy theo thứ tự a, b, c, d
        if (str_starts_with($value, '{') || str_starts_with($value, '[')) {
            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                $items = (isset($decoded['a']) || isset($decoded['b']) || isset($decoded['c']) || isset($decoded['d']))
                    ? [$decoded['a'] ?? null, $decoded['b'] ?? null, $decoded['c'] ?? null, $decoded['d'] ?? null]
                    : array_values($decoded);

                return array_map(fn ($token) => $this->toBool($token), $items);
            }
        }

        $parts = str_contains($value, '-') ? explode('-', $value)
            : (str_contains($value, ',') ? explode(',', $value) : mb_str_split($value));

        return array_map(fn ($token) => $this->toBool($token), $parts);
    }

    private function toBool(mixed $token): ?bool
    {
        if (is_bool($token)) {
            return $token;
        }

        if ($token === null) {
            return null;
        }

        $token = mb_strtoupper(trim((string) $token));

        if (in_array($token, ['Đ', 'D', 'T', '1', 'TRUE', 'ĐÚNG', 'DUNG'], true)) {
            return true;
        }
        if (in_array($token, ['S', 'F', '0', 'FALSE', 'SAI'], true)) {
            return false;
        }

        return null;
    }

    /**
     * Chuyển chuỗi số (cho phép dấu phẩy hoặc dấu chấm thập phân) thành float.
     * Không hợp lệ thì trả về null.
     */
    public function parseNumber(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace(',', '.', trim($value));

        return preg_match('/^-?\d+(\.\d+)?$/', $value) ? (float) $value : null;
    }

    private function gradePartI(object $question, string $answer): float
    {
        $given = mb_strtoupper(trim($answer));
        $correct = mb_strtoupper(trim((string) $question->correct_answer));

        return $given === $correct ? self::PART_I_POINT : 0.0;
    }

    private function gradePartII(object $question, string $answer): float
    {
        $correct = $this->parseTrueFalse($question->correct_answer);
        $given = $this->parseTrueFalse($answer);

        if (count($correct) !== 4) {
            return 0.0;
        }

        $right = 0;
        for ($i = 0; $i < 4; $i++) {
            if ($correct[$i] !== null && ($given[$i] ?? null) === $correct[$i]) {
                $right++;
            }
        }

        return self::PART_II_POINTS[$right];
    }

    private function gradePartIII(object $question, string $answer): float
    {
        $given = $this->parseNumber($answer);
        $correct = $this->parseNumber($question->correct_answer);

        if ($given === null || $correct === null) {
            return 0.0;
        }

        // Chỉ làm tròn đáp án chuẩn theo cấu hình của câu hỏi (nếu có).
        // Đáp án của học sinh KHÔNG được làm tròn hộ: học sinh phải tự làm tròn
        // đúng yêu cầu của đề, ví dụ đáp án 3,14 thì "3,142" không được điểm.
        if ($question->decimal_places !== null) {
            $correct = round($correct, (int) $question->decimal_places);
        }

        return abs($given - $correct) < 1e-9 ? self::PART_III_POINT : 0.0;
    }
}
