@extends('layouts.app')

@section('title', 'Nhật ký hoạt động hệ thống')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Thanh công cụ: Bộ lọc -->
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <form action="{{ route('admin.logs.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-4">
                
                <!-- Tìm theo người dùng -->
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <input type="text" name="user_search" value="{{ request('user_search') }}" 
                           class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 block w-full pl-10 p-2.5 outline-none shadow-sm" 
                           placeholder="Tìm tên, email người dùng...">
                </div>

                <!-- Lọc theo hành động -->
                <select name="action_type" onchange="this.form.submit()" 
                        class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 block w-full sm:w-auto p-2.5 outline-none cursor-pointer shadow-sm">
                    <option value="">-- Tất cả hoạt động --</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}" {{ request('action_type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
                
                <button type="submit" class="px-5 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition shadow-sm whitespace-nowrap">
                    Lọc dữ liệu
                </button>

                @if(request()->has('user_search') || request()->has('action_type'))
                    <a href="{{ route('admin.logs.index') }}" class="text-sm text-red-600 hover:text-red-800 font-medium">Xóa lọc</a>
                @endif
            </form>
        </div>
        
        <!-- Bảng danh sách Log -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-sm border-b border-gray-200">
                        <th class="py-3 px-6 font-medium">Thời gian</th>
                        <th class="py-3 px-6 font-medium">Người dùng</th>
                        <th class="py-3 px-6 font-medium">Hành động</th>
                        <th class="py-3 px-6 font-medium">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 font-mono">
                    @forelse($logs as $log)
                        <tr class="border-b border-gray-50 hover:bg-slate-50 transition">
                            <td class="py-3 px-6 whitespace-nowrap text-gray-500">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="py-3 px-6">
                                @if($log->user)
                                    <div class="font-semibold text-slate-800 font-sans">{{ $log->user->name }}</div>
                                    <div class="text-gray-400 text-xs font-sans">{{ $log->user->email }}</div>
                                @else
                                    <span class="text-gray-400 italic font-sans">Hệ thống / Khách</span>
                                @endif
                            </td>
                            <td class="py-3 px-6">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-md font-bold text-xs">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-gray-600 text-xs">
                                {{-- Cột trong CSDL tên là "detail" (không có "details" và không có ip_address) --}}
                                @if($log->detail)
                                    <div class="max-w-md break-words" title="{{ $log->detail }}">{{ \Illuminate\Support\Str::limit($log->detail, 160) }}</div>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-gray-500 font-sans">
                                Chưa có dữ liệu nhật ký hoạt động.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <div class="p-4 border-t border-gray-100">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
@endsection