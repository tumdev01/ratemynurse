@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <h1>รายการ ผู้ใช้งาน (Employee)</h1>
        <a href="{{ route('employee.create') }}" class="text-blue-600 hover:underline">{{ __('เพิ่มใหม่') }}</a>
    </div>
</div>
@endsection

