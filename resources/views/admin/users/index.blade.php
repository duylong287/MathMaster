@extends('layouts.app')

@section('title', 'Quản lý Tài khoản')

@section('content')
<div class="container mx-auto">
    
    <!-- Vùng hiển thị thông báo trạng thái -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded shadow-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded shadow-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- Bộ lọc & Thao tác -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6 flex flex-wrap justify-between items-center gap-4">
        <!-- Form lọc -->
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap gap-4 items-center w-full lg:w-auto">
            <!-- Vai trò -->
            <select name="role" class="border-gray-300 rounded-md text-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Tất cả vai trò --</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>Giáo viên</option>
                <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Học sinh</option>
            </select>

            <!-- Lớp học -->
            <select name="class_id" class="border-gray-300 rounded-md text-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Chọn lớp --</option>
                @foreach($classes as $cls)
                    <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                @endforeach
            </select>

            <!-- Tìm kiếm -->
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên hoặc email..." class="border-gray-300 rounded-md text-sm p-2 border focus:ring-blue-500 focus:border-blue-500">

            <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-slate-700 transition">Lọc</button>
            @if(request()->anyFilled(['role', 'class_id', 'search']))
                <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline">Xóa lọc</a>
            @endif
        </form>

        <!-- Các nút hành động -->
        <div class="flex gap-2 w-full lg:w-auto">
            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Thêm tài khoản
            </a>
        </div>
    </div>

    <!-- Bảng danh sách -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-sm uppercase tracking-wider">
                        <th class="p-4 font-semibold border-b">Họ và tên / Email</th>
                        <th class="p-4 font-semibold border-b text-center">Vai trò</th>
                        <th class="p-4 font-semibold border-b text-center">Lớp</th>
                        <th class="p-4 font-semibold border-b text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="{{ !$user->is_active ? 'bg-gray-100 opacity-70' : 'hover:bg-gray-50' }}">
                            <td class="p-4">
                                <div class="font-medium text-gray-900">
                                    {{ $user->name }}
                                    @if(!$user->is_active)
                                        <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 rounded text-[10px] font-bold uppercase">Đã khóa</span>
                                    @endif
                                </div>
                                <div class="text-gray-500 text-xs mt-0.5">{{ $user->email }}</div>
                            </td>
                            <td class="p-4 text-center">
                                @if($user->role === 'admin') <span class="text-red-600 font-medium">Admin</span>
                                @elseif($user->role === 'teacher') <span class="text-blue-600 font-medium">Giáo viên</span>
                                @else <span class="text-gray-700">Học sinh</span>
                                @endif
                            </td>
                            <td class="p-4 text-center text-gray-600">
                                {{ $user->schoolClass ? $user->schoolClass->name : '—' }}
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 hover:text-blue-900 font-medium mr-3">Sửa</a>
                                
                                <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <!-- Hiển thị nút Khóa/Mở khóa dựa vào trạng thái -->
                                    @if($user->is_active)
                                        <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn KHÓA tài khoản này không?')" class="text-red-600 hover:text-red-900 font-medium">Khóa</button>
                                    @else
                                        <button type="submit" onclick="return confirm('Mở khóa cho tài khoản này?')" class="text-green-600 hover:text-green-900 font-medium">Mở khóa</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-500">Không tìm thấy tài khoản nào phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <div class="p-4 border-t">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection