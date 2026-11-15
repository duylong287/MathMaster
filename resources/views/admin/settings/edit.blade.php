@extends('layouts.app')

@section('title', 'Cấu hình Hệ thống')

@section('content')
    <div class="max-w-4xl bg-white p-8 rounded-xl shadow-sm border border-gray-100 mx-auto">
        
        <div class="mb-6 border-b border-gray-100 pb-4">
            <h3 class="text-xl font-bold text-gray-800">Tham số đánh giá năng lực</h3>
            <p class="text-sm text-gray-500 mt-1">Cấu hình 3 mốc điểm phần trăm (%) để phân loại 4 mức độ: Yếu, Trung bình, Khá, Giỏi.</p>
            <p class="text-sm text-indigo-600 font-medium mt-1 italic">* Nguyên tắc: Điểm đúng bằng ngưỡng thì thuộc mức cao hơn.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg font-medium flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg font-medium text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Mốc Trung bình -->
                <div class="p-4 bg-orange-50 rounded-lg border border-orange-100">
                    <label class="block text-sm font-bold text-orange-800 mb-1">Mốc TRUNG BÌNH</label>
                    <p class="text-xs text-orange-600 mb-3">(Dưới mốc này là Yếu)</p>
                    <div class="relative">
                        <!-- Giá trị mặc định là 50 -->
                        <input type="number" name="average_threshold" value="{{ old('average_threshold', $average ?? 50) }}" required min="0" max="100"
                               class="w-full px-4 py-2.5 bg-white border border-orange-200 rounded-md text-sm focus:ring-2 focus:ring-orange-500 outline-none font-mono text-center text-lg font-semibold">
                        <span class="absolute right-4 top-2.5 text-gray-400 font-bold">%</span>
                    </div>
                </div>

                <!-- Mốc Khá -->
                <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-100">
                    <label class="block text-sm font-bold text-yellow-800 mb-1">Mốc KHÁ</label>
                    <p class="text-xs text-yellow-600 mb-3">(Từ mốc này trở lên)</p>
                    <div class="relative">
                        <!-- Giá trị mặc định là 65 -->
                        <input type="number" name="good_threshold" value="{{ old('good_threshold', $good ?? 65) }}" required min="0" max="100"
                               class="w-full px-4 py-2.5 bg-white border border-yellow-200 rounded-md text-sm focus:ring-2 focus:ring-yellow-500 outline-none font-mono text-center text-lg font-semibold">
                        <span class="absolute right-4 top-2.5 text-gray-400 font-bold">%</span>
                    </div>
                </div>

                <!-- Mốc Giỏi -->
                <div class="p-4 bg-green-50 rounded-lg border border-green-100">
                    <label class="block text-sm font-bold text-green-800 mb-1">Mốc GIỎI</label>
                    <p class="text-xs text-green-600 mb-3">(Từ mốc này trở lên)</p>
                    <div class="relative">
                        <!-- Giá trị mặc định là 80 -->
                        <input type="number" name="excellent_threshold" value="{{ old('excellent_threshold', $excellent ?? 80) }}" required min="0" max="100"
                               class="w-full px-4 py-2.5 bg-white border border-green-200 rounded-md text-sm focus:ring-2 focus:ring-green-500 outline-none font-mono text-center text-lg font-semibold">
                        <span class="absolute right-4 top-2.5 text-gray-400 font-bold">%</span>
                    </div>
                </div>

            </div>

            <div class="pt-6 border-t border-gray-100 flex items-center justify-end">
                <button type="submit" 
                        class="px-6 py-2.5 bg-slate-900 text-white font-medium rounded-lg hover:bg-slate-800 transition transform hover:-translate-y-0.5">
                    Lưu cấu hình
                </button>
            </div>
        </form>
    </div>
@endsection