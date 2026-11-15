<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\Teacher\ExamController;
use App\Http\Controllers\StudentController;

// ==========================================
// 1. KHU VỰC KHÁCH (CHƯA ĐĂNG NHẬP)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
// Xử lý gửi mật khẩu mới
    // Giới hạn 5 yêu cầu / 10 phút / địa chỉ IP để không bị lợi dụng làm vô hiệu mật khẩu người khác
    Route::post('/forgot-password', [AuthController::class, 'sendNewPassword'])->middleware('throttle:5,10')->name('password.email');
});

// ==========================================
// 2. KHU VỰC THÀNH VIÊN (ĐÃ ĐĂNG NHẬP)
// ==========================================
Route::middleware('auth')->group(function () {

    // Xử lý đăng xuất chung cho mọi vai trò
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Nút giao thông (Trang chủ): Tự động kiểm tra role và rẽ nhánh
    Route::get('/', function () {
        $role = \Illuminate\Support\Facades\Auth::user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($role === 'teacher') {
            return redirect()->route('teacher.dashboard');
        }

        return redirect()->route('student.dashboard');
    })->name('home');

    // ------------------------------------------
    // Tuyến đường ADMIN (Chỉ Admin mới vào được)
    // ------------------------------------------
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        // Gọi hàm index() để hiển thị Dashboard
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');


        // Code đúng khi đặt bên trong Route::group có sẵn name('admin.')
Route::get('/profile', [\App\Http\Controllers\AdminController::class, 'profile'])->name('profile');
Route::put('/profile/password', [\App\Http\Controllers\AdminController::class, 'updatePassword'])->name('update_password');


        Route::prefix('topics')->name('topics.')->group(function () {
            Route::get('/', [TopicController::class, 'index'])->name('index');
            Route::get('/create', [TopicController::class, 'create'])->name('create');
            Route::post('/', [TopicController::class, 'store'])->name('store');
            Route::get('/{topic}/edit', [TopicController::class, 'edit'])->name('edit');
            Route::put('/{topic}', [TopicController::class, 'update'])->name('update');
            Route::patch('/{topic}/deactivate', [TopicController::class, 'deactivate'])->name('deactivate');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');

            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');


            Route::patch('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('toggle-active');
            Route::patch('/{user}/role', [UserController::class, 'updateRole'])->name('update-role');
        });

        Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'edit'])->name('edit');
            Route::put('/', [SettingController::class, 'update'])->name('update');
        });
        Route::resource('classes', \App\Http\Controllers\Admin\ClassController::class);
        Route::post('classes/{class}/close', [\App\Http\Controllers\Admin\ClassController::class, 'close'])->name('classes.close');






        // Sau này bạn thêm các route quản lý tài khoản, cấu hình hệ thống ở đây...
    });

    // ------------------------------------------
    // Tuyến đường GIÁO VIÊN (Chỉ Giáo viên mới vào được)
    // ------------------------------------------
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {



        Route::get('/classes/{class}', [\App\Http\Controllers\TeacherController::class, 'showClass'])->name('classes.show');


        Route::get('/students/{student}/history', [\App\Http\Controllers\TeacherController::class, 'studentHistory'])->name('students.history');

       
        

        Route::get('/analytics/students', [\App\Http\Controllers\TeacherController::class, 'analyticsList'])->name('analytics.list');
        Route::get('/students/{id}/analytics', [\App\Http\Controllers\TeacherController::class, 'studentAnalytics'])->name('students.analytics');


        // 1. Quản lý danh sách đề thi (Có bộ lọc)
        Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');

        // 2. Xem thống kê phổ điểm
        Route::get('/exams/{exam}/statistics', [ExamController::class, 'statistics'])->name('exams.statistics');


        Route::get('/exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
        Route::put('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update');

        Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');




        // Trang chủ Giáo viên
        Route::get('/dashboard', [TeacherController::class, 'index'])->name('dashboard');



        Route::get('/profile', [TeacherController::class, 'profile'])->name('profile');
        Route::put('/profile/password', [TeacherController::class, 'updatePassword'])->name('update_password');


        // Quản lý Đề thi
        Route::prefix('exams')->name('exams.')->group(function () {
            Route::get('/create', [ExamController::class, 'create'])->name('create');
            Route::post('/', [ExamController::class, 'store'])->name('store');

            // Bước 2.2: Tải PDF
            Route::get('/{exam}/upload-pdf', [ExamController::class, 'uploadPdf'])->name('upload-pdf');
            Route::post('/{exam}/upload-pdf', [ExamController::class, 'storePdf'])->name('store-pdf');
            Route::get('/{exam}/preview-pdf', [ExamController::class, 'previewPdf'])->name('preview-pdf');

            // Bước 2.3: Cấu hình đáp án (Đưa 2 dòng này vào BÊN TRONG nhóm)
            Route::get('/{exam}/config-answers', [ExamController::class, 'configAnswers'])->name('config-answers');
            Route::post('/{exam}/config-answers', [ExamController::class, 'storeAnswers'])->name('store-answers');
        });
        // <--- Dấu đóng ngoặc của nhóm exams phải nằm ở tuốt dưới cùng này!

    });
    // ------------------------------------------
    // Tuyến đường HỌC SINH (Chỉ Học sinh mới vào được)
    // ------------------------------------------
    Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {

        // Trang chủ / Tổng quan Học sinh
        Route::get('/dashboard', [StudentController::class, 'index'])->name('dashboard');

        Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
        Route::put('/profile/password', [StudentController::class, 'updatePassword'])->name('update_password');

        // Danh sách đề thi
        Route::get('/exams', [StudentController::class, 'exams'])->name('exams.index');



        // Khởi tạo lượt làm bài (Nhấn nút Bắt đầu)
        Route::post('/exams/{exam}/start', [StudentController::class, 'startExam'])->name('exams.start');

        // Xem PDF đề thi an toàn (Chỉ học sinh mới được xem đề đang mở)
        Route::get('/exams/{exam}/pdf', [StudentController::class, 'viewPdf'])->name('exams.pdf');

        // Giao diện phòng thi (Bước 3.2)
        Route::get('/exams/{exam}/take/{attempt}', [StudentController::class, 'takeExam'])->name('exams.take');




        Route::post('/exams/{exam}/take/{attempt}/submit', [App\Http\Controllers\StudentController::class, 'submitExam'])->name('exams.submit');


        Route::get('/exams/{exam}/result/{attempt}', [App\Http\Controllers\StudentController::class, 'result'])->name('exams.result');


        Route::get('/history', [StudentController::class, 'history'])->name('history');


        Route::get('/analytics', [StudentController::class, 'analytics'])->name('analytics');
    });
});
