@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <h1>
            <a class="underline" href="{{ route('nursinghome.index') }}">{{  __('กลับหน้ารายการ') }}</a>
        </h1>
    </div>

    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-col justify-between">
        <form action="{{ route('nursing-home.profile.update', $nursinghome->id) }}" method="POST" class="flex flex-col gap-[32px] w-full max-w-[870px] mx-auto mb-6">
            @csrf
            <div class="flex flex-col justify-start bg-[#F0F9F4] p-[16px] rounded-md">
                <span class="htitle text-[16px] md:text-lg text-[#286F51]">แก้ไขข้อมูล {{ $nursinghome->firstname }} {{$nursinghome->lastname}}</span>
                <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน</span>
            </div>

            @if (session('success'))
                <div class="alert alert-success absolute top-[15%] right-[15px] border border-[#286F51] bg-[#F0F9F4] text-[#286F51] px-[25px] py-[15px] rounded-lg shadow-md">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label for="firstname">ชื่อจริง <span class="req">*</span></label>
                    <input required type="text" name="firstname" id="firstname" maxlength="10" placeholder="ชื่อจริง"
                        class="border rounded-lg px-3 py-2" value="{{ old('firstname', $nursinghome->firstname ?? '') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label for="lastname">นามสกุล</label>
                    <input type="text" name="lastname" id="lastname" maxlength="10" placeholder="นามสกุล"
                        class="border rounded-lg px-3 py-2" value="{{ old('lastname', $nursinghome->lastname ?? '') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>
            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label for="email">อีเมล์ <span class="req">*</span></label>
                    <input required type="text" name="email" id="email" maxlength="10" placeholder="อีเมล์"
                        class="border rounded-lg px-3 py-2" value="{{ old('email', $nursinghome->email ?? '') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label for="phone">เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" id="phone" maxlength="10" placeholder="เบอร์โทรศัพท์"
                        class="border rounded-lg px-3 py-2" value="{{ old('phone', $nursinghome->phone ?? '') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>
            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label for="password">รหัสผ่าน <span class="req">*</span></label>
                    <input required type="password" name="password" id="password" placeholder="รหัสผ่าน"
                        class="border rounded-lg px-3 py-2" value=""/>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="ยืนยันรหัสผ่าน"
                        class="border rounded-lg px-3 py-2" value=""/>
                    <label class="error text-xs text-red-600"></label>
                </div>
  
            </div>

            <span class="w-full min-h-[1px] divider clear-both"></span>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] justify-center">
                <button type="submit" class="w-[200px] h-[48px] rounded-lg bg-[#286F51] text-white">บันทึก</button>
            </div>

            <span class="w-full min-h-[1px] divider clear-both"></span>

        </form>
        <div class="w-full max-w-[870px] mx-auto flex flex-col gap-[32px]">
            <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                    <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                        stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="text-md text-white font-semibold">รายการศูนย์</span>
            </span>
            @if($nursinghome->nursingHomes)
                <table id="nursingHomesTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3"></th>
                            <th class="px-6 py-3">รูปภาพ</th>
                            <th class="px-6 py-3">ชื่อ</th>
                            <th class="px-6 py-3"><span class="sr-only">แก้ไข</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @foreach($nursinghome->nursingHomes as $home)
                            
                            <tr>
                                <td class="px-6 py-4"></td>
                                <td class="px-6 py-4">
                                    @if ($home->coverImage?->full_path)
                                        <img 
                                            src="{{ $home->coverImage->full_path }}" 
                                            alt="{{ $home->name }}" 
                                            class="w-20 h-20 object-cover rounded-md shadow"
                                        >
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $home->name }}</td>
                                <td class="px-6 py-4 text-right"><a class="underline" href="{{route('nursing-home.edit', $home->id)}}">ดูรายละเอียด</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        
    </div>
</div>
@endsection

@section('style')
    <style>
        .swal-confirm-btn {
        background-color: #dc3545 !important; /* แดง */
        color: #fff !important;
        }

        .swal-cancel-btn {
        background-color: #6c757d !important; /* เทา */
        color: #fff !important;
        }
    </style>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const nursinghomeId = {{ $nursinghome->id }};
</script> 
@endsection