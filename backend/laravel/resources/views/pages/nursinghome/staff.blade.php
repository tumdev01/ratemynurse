@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <h1>
            <a class="underline" href="{{ route('nursing-home.edit', $nursinghome->id) }}">{{  __('ทีมงาน') }} ( {{ $nursinghome->profile->name }} )</a>
        </h1>
    </div>

    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <form id="nursingHomeStaff" class="flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="flex flex-col justify-start bg-[#F0F9F4] p-[16px] rounded-md">
                <span class="htitle text-[16px] md:text-lg text-[#286F51]">สร้างทีมงาน บ้านพักดูแลผู้สูงอายุ</span>
                <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน</span>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label for="name">ชื่อ-สกุล <span class="req">*</span></label>
                    <input required type="text" name="name" id="name" maxlength="10" placeholder="ชื่อ-สกุล ทีมงาน"
                        class="border rounded-lg px-3 py-2"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label for="responsibility">ตำแหน่งรับผิดชอบ</label>
                    <input required type="text" name="responsibility" id="responsibility" maxlength="10" placeholder="เบอร์โทรศัพท์สำรอง"
                        class="border rounded-lg px-3 py-2"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="w-full flex flex-col">
                <label class="mb-2" for="address">เพิ่มรูปภาพ</label>
                <div class="border border-dashed rounded-lg h-[130px] flex justify-center items-center">
                    <div id="certificate_upload" class="flex flex-row gap-[16px] justify-center">
                        <img src="https://ratemynurse.org/wp-content/uploads/2025/08/upload2.png" loading="lazy" width="70" height="67">
                        <div class="flex flex-col">
                            <label class="text-sm font-semibold">คลิกเพื่ออัปโหลดไฟล์</label>
                            <span class="text-xs">รองรับ .JPG, .PNG | ขนาดไม่เกิน 5 MB</span>
                            <input type="file" id="hiddenProfileUpload" name="image" style="display:none">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="relative overflow-x-auto shadow-md opacity-100 transition-opacity duration-300">
        @if ( $nursinghome->staffs )
            <table id="nursingHomesTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 w-[150px]">รูปภาพ</th>
                        <th class="px-6 py-3">ชื่อ</th>
                        <th class="px-6 py-3">ตำแหน่ง</th>
                        <th class="px-6 py-3"><span class="sr-only">ลบ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nursinghome->staffs as $staff)
                        <tr data-id="{{ $staff->id }}" class="hover:bg-gray-100">
                            <th class="px-6 py-3 w-[150px] font-normal">รูปภาพ</th>
                            <th class="px-6 py-3 font-normal">{{ $staff->name }}</th>
                            <th class="px-6 py-3 font-normal">{{ $staff->responsibility }}</th>
                            <th class="px-6 py-3 font-normal">
                                <svg class="cursor-pointer w-4 h-4 text-red-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/> </svg>
                            </th>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>{{ __('ยังไม่มีทีมงาน') }}
        @endif
    </div>

@endsection
@section('javascript')
<script>
    const nursinghomeId = {{ $nursinghome->id }};
    document.getElementById('avatar').addEventListener('click', () => {
        document.getElementById('hiddenProfileUpload').click();
    });

    // จับ event เมื่อเลือกไฟล์
    document.getElementById('hiddenProfileUpload').addEventListener('change', (event) => {
        const files = event.target.files;
        let preview = document.getElementById('profiles_preview');
        if (files.length > 0) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith("image/")) {
                    let reader = new FileReader();
                    reader.onload = (e) => {
                        let img = document.createElement("img");
                        img.src = e.target.result;
                        img.style.width = "100px";
                        img.style.height = "100px";
                        img.style.objectFit = "cover";
                        img.style.borderRadius = "8px";
                        preview.appendChild(img); // เพิ่มเข้าไปเรื่อยๆ
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script> 
@endsection