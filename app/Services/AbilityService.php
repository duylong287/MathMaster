<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\SystemSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AbilityService
{
    /** Số câu tối thiểu của một chuyên đề để được xếp loại */
    public const MIN_QUESTIONS = 5;

    /**
     * Ngưỡng xếp loại do Admin cấu hình ở trang "Tham số đánh giá năng lực":
     *   Yếu       : < average   (Mốc TRUNG BÌNH)
     *   Trung bình: từ average đến < good   (Mốc KHÁ)
     *   Khá       : từ good đến < excellent (Mốc GIỎI)
     *   Giỏi      : >= excellent
     */
    public function thresholds(): array
    {
        $rows = SystemSetting::whereIn('key', ['average_threshold', 'good_threshold', 'excellent_threshold'])
            ->pluck('value', 'key');

        return [
            'average'   => (float) ($rows['average_threshold'] ?? 50),
            'good'      => (float) ($rows['good_threshold'] ?? 65),
            'excellent' => (float) ($rows['excellent_threshold'] ?? 80),
        ];
    }

    /**
     * Lượt nộp GẦN NHẤT của mỗi đề (sau khi lọc thời gian và loại đề).
     * Đây là nguồn duy nhất cho "số đề đã làm", "điểm trung bình",
     * thống kê chuyên đề và thống kê mức độ, để các con số luôn khớp nhau.
     */
    public function getLatestAttempts($studentId, $startDate = null, $endDate = null, $typeFilter = null): Collection
    {
        $query = Attempt::with('exam:id,title')
            ->where('student_id', $studentId)
            ->where('status', 'submitted');

        if ($startDate) $query->where('submitted_at', '>=', $startDate);
        if ($endDate)   $query->where('submitted_at', '<=', $endDate);

        if ($typeFilter) {
            $query->whereHas('exam', fn ($q) => $q->where('type', $typeFilter));
        }

        // Mới nhất trước; unique() giữ phần tử đầu tiên gặp, tức lượt gần nhất của mỗi đề
        return $query->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get()
            ->unique('exam_id')
            ->values();
    }

    public function getStudentTopicStats($studentId, $startDate = null, $endDate = null, $typeFilter = null)
    {
        $attemptIds = $this->getLatestAttempts($studentId, $startDate, $endDate, $typeFilter)->pluck('id');
        $rows = $this->questionRows($attemptIds);

        // Gộp điểm theo chuyên đề
        $topicStats = [];
        foreach ($rows as $row) {
            $tId   = $row->topic_id ?: 'unclassified';
            $tName = $row->topic_name ?? 'Chưa phân loại';

            if (!isset($topicStats[$tId])) {
                $topicStats[$tId] = [
                    'id' => $tId, 'name' => $tName,
                    'earned_score' => 0.0, 'max_score' => 0.0, 'question_count' => 0,
                ];
            }

            $topicStats[$tId]['earned_score']   += (float) $row->score;
            $topicStats[$tId]['max_score']      += $this->maxScore($row->part);
            $topicStats[$tId]['question_count'] += 1;
        }

        $t = $this->thresholds();

        // Xếp loại
        $result = [];
        foreach ($topicStats as $key => $stat) {
            $stat['earned'] = round($stat['earned_score'], 2); // tên dùng ở giao diện
            $stat['max']    = round($stat['max_score'], 2);

            if ($stat['id'] === 'unclassified') {
                $stat['level'] = 'Chưa phân loại';
                $stat['percentage'] = 0;
            } elseif ($stat['question_count'] < self::MIN_QUESTIONS) {
                $stat['level'] = 'Chưa đủ dữ liệu';
                $stat['percentage'] = 0;
            } else {
                $percent = $stat['max_score'] > 0 ? ($stat['earned_score'] / $stat['max_score']) * 100 : 0;
                $stat['percentage'] = round($percent, 2);

                // Đúng bằng ngưỡng thì thuộc mức cao hơn
                if     ($percent < $t['average'])   $stat['level'] = 'Yếu';
                elseif ($percent < $t['good'])      $stat['level'] = 'Trung bình';
                elseif ($percent < $t['excellent']) $stat['level'] = 'Khá';
                else                                $stat['level'] = 'Giỏi';
            }

            $result[$key] = $stat;
        }

        return collect($result);
    }

    public function getCognitiveStats($studentId, $startDate = null, $endDate = null, $typeFilter = null)
    {
        $attemptIds = $this->getLatestAttempts($studentId, $startDate, $endDate, $typeFilter)->pluck('id');

        if ($attemptIds->isEmpty()) {
            return [];
        }

        $rows = $this->questionRows($attemptIds);

        // Khóa trùng với giá trị lưu trong cột exam_questions.difficulty
        $stats = [
            'recognize'  => ['name' => 'Nhận biết',    'earned' => 0.0, 'max' => 0.0, 'correct' => 0, 'total' => 0],
            'understand' => ['name' => 'Thông hiểu',   'earned' => 0.0, 'max' => 0.0, 'correct' => 0, 'total' => 0],
            'apply'      => ['name' => 'Vận dụng',     'earned' => 0.0, 'max' => 0.0, 'correct' => 0, 'total' => 0],
            'advanced'   => ['name' => 'Vận dụng cao', 'earned' => 0.0, 'max' => 0.0, 'correct' => 0, 'total' => 0],
        ];

        foreach ($rows as $row) {
            $lvl = $row->difficulty;
            if (!isset($stats[$lvl])) continue;

            $max    = $this->maxScore($row->part);
            $earned = (float) $row->score;

            $stats[$lvl]['earned'] += $earned;
            $stats[$lvl]['max']    += $max;
            $stats[$lvl]['total']  += 1;
            // "Đúng" = đạt điểm tối đa của câu (câu Phần II đúng một phần không tính)
            if ($max > 0 && round($earned, 2) >= round($max, 2)) {
                $stats[$lvl]['correct'] += 1;
            }
        }

        $radarData = [];
        foreach ($stats as $data) {
            $radarData[] = [
                'name'       => $data['name'],
                'percentage' => $data['max'] > 0 ? round(($data['earned'] / $data['max']) * 100, 2) : 0,
                'correct'    => $data['correct'],
                'total'      => $data['total'],
            ];
        }

        return $radarData;
    }

    /**
     * Mỗi dòng = một câu hỏi của đề trong một lượt làm.
     * Dùng LEFT JOIN từ danh sách câu hỏi của đề để câu bỏ trống (chưa có dòng trong
     * attempt_answers) vẫn được tính vào điểm tối đa, không làm tỷ lệ cao giả.
     */
    private function questionRows(Collection $attemptIds): Collection
    {
        if ($attemptIds->isEmpty()) {
            return collect();
        }

        return DB::table('attempts')
            ->join('exam_questions', 'exam_questions.exam_id', '=', 'attempts.exam_id')
            ->leftJoin('attempt_answers', function ($join) {
                $join->on('attempt_answers.attempt_id', '=', 'attempts.id')
                     ->on('attempt_answers.question_id', '=', 'exam_questions.id');
            })
            ->leftJoin('topics', 'topics.id', '=', 'exam_questions.topic_id')
            ->whereIn('attempts.id', $attemptIds)
            ->select(
                'topics.id as topic_id',
                'topics.name as topic_name',
                'exam_questions.part',
                'exam_questions.difficulty',
                DB::raw('COALESCE(attempt_answers.score, 0) as score')
            )
            ->get();
    }

    /** Điểm tối đa của một câu theo phần */
    private function maxScore($part): float
    {
        return match ($part) {
            'I'   => 0.25,
            'II'  => 1.0,
            'III' => 0.5,
            default => 0.0,
        };
    }
}