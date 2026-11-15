<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // Hiển thị form cấu hình
    public function edit()
    {
        // Lấy giá trị từ DB, gán giá trị mặc định theo đúng chuẩn 50 - 65 - 80
        $average = SystemSetting::where('key', 'average_threshold')->value('value') ?? 50;
        $good = SystemSetting::where('key', 'good_threshold')->value('value') ?? 65;
        $excellent = SystemSetting::where('key', 'excellent_threshold')->value('value') ?? 80;

        return view('admin.settings.edit', compact('average', 'good', 'excellent'));
    }

    // Xử lý cập nhật
    public function update(Request $request)
    {
        // Validate các giá trị % tăng dần hợp lý
        $request->validate([
            'average_threshold' => 'required|numeric|min:0|max:100',
            'good_threshold' => 'required|numeric|min:0|max:100|gt:average_threshold',
            'excellent_threshold' => 'required|numeric|min:0|max:100|gt:good_threshold',
        ], [
            'good_threshold.gt' => 'Mốc KHÁ phải LỚN HƠN mốc Trung bình.',
            'excellent_threshold.gt' => 'Mốc GIỎI phải LỚN HƠN mốc Khá.',
            'max' => 'Giá trị không được vượt quá 100%.',
            'min' => 'Giá trị không được nhỏ hơn 0%.'
        ]);

        // Cập nhật hoặc tạo mới vào database
        SystemSetting::updateOrCreate(['key' => 'average_threshold'], ['value' => $request->average_threshold]);
        SystemSetting::updateOrCreate(['key' => 'good_threshold'], ['value' => $request->good_threshold]);
        SystemSetting::updateOrCreate(['key' => 'excellent_threshold'], ['value' => $request->excellent_threshold]);

        return redirect()->route('admin.settings.edit')->with('success', 'Đã lưu cấu hình hệ thống thành công!');
    }
}