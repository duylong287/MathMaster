@extends('layouts.teacher')

@section('title', 'Phân tích năng lực học sinh')

@section('content')
    <div class="max-w-7xl mx-auto mb-4">
        <a href="{{ route('teacher.analytics.list') }}" class="text-sm text-blue-600 hover:underline">&larr; Quay lại danh sách học sinh</a>
    </div>

    {{-- Cùng giao diện với trang học sinh tự xem --}}
    @include('analytics._report', [
        'viewer'       => 'teacher',
        'filterAction' => route('teacher.students.analytics', $student->id),
        'filterGrade'  => $student->grade,
        'titleAccent'  => $student->name,
    ])
@endsection