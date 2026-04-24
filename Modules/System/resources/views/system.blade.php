@extends('Admin::layouts.master')

@section('title', 'System')

@section('content')
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">System module</h1>
        <p class="text-sm text-slate-500">Loai module: shell.</p>

        @include('System::components.placeholder')
    </div>
@endsection