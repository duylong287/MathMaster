@extends('layouts.student')

@section('title', 'Phân tích năng lực')

@section('content')
    @include('analytics._report', [
        'viewer'       => 'student',
        'filterAction' => route('student.analytics'),
        'filterGrade'  => auth()->user()->grade,
        'titleAccent'  => null,
    ])
@endsection