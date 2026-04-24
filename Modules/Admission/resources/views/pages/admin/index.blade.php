@extends('Admin::layouts.master')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Danh sách Đơn đăng ký Nhập học</h2>
        <a href="{{ route('admin.admission.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Thêm đơn mới</a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        @livewire('admission.admin.applications.index')
    </div>
</div>

@endsection
