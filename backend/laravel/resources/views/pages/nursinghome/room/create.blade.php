@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <form id="createRoom" method="post" action="{{ route('nursing-home.room.store', $nursingHome->id) }}" class="flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ $nursingHome->id }}">
            @if(session('error'))
                <div class="flex flex-col justify-start bg-red-500 p-[16px] rounded-md text-white">
                    <span>
                        {{ session('error') }}
                    </span>
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
            <div class="flex flex-col justify-start bg-[#F0F9F4] p-[16px] rounded-md">
                <span class="htitle text-[16px] md:text-lg text-[#286F51]">สร้างห้องพัก {{ $nursingHome->profile->name }}</span>
                <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน</span>
            </div>
            <div id="frm" class="flex flex-col gap-[32px]">

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="name">ชื่อ <span class="req">*</span></label>
                        <input required type="text" name="name" id="name" placeholder="ชื่อห้องพัก"
                            class="border rounded-lg px-3 py-2" value="{{ old('name') }}"/>
                        <label class="error text-xs text-red-600"></label>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="type">ประเภทห้อง <span class="req">*</span></label>
                        <select name="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option selected value="SINGLE_ROOM">ห้องพักเดี่ยว Single Room</option>
                            <option value="TWIN_ROOM">ห้องพักคู่ Twin Room</option>
                        </select>
                        <label class="error text-xs text-red-600"></label>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label for="description">ข้อความ <span class="req">*</span></label>
                    <textarea required id="description" name="description" class="min-h-[150px] border rounded-lg px-3 py-2" placeholder="ข้อความรายละเอียด">{{ old('description') ?? '' }}</textarea>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="cost_per_day">ราคา/วัน <span class="req">*</span></label>
                        <input required type="number" name="cost_per_day" id="cost_per_day" placeholder="ราคาต่อวัน"
                            class="border rounded-lg px-3 py-2" value="{{ old('cost_per_day') }}"/>
                        <label class="error text-xs text-red-600"></label>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="cost_per_month">ราคา/เดือน <span class="req">*</span></label>
                        <input required type="number" name="cost_per_month" id="cost_per_month" placeholder="ราคาต่อเดือน"
                            class="border rounded-lg px-3 py-2" value="{{ old('cost_per_month') }}"/>
                        <label class="error text-xs text-red-600"></label>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label class="mb-2" for="address">รูปภาพ</label>
                    <div class="border border-dashed rounded-lg h-[130px] flex justify-center items-center">
                        <div id="certificate_upload" class="flex flex-row gap-[16px] justify-center">
                            <img id="avatar" src="https://ratemynurse.org/wp-content/uploads/2025/08/upload2.png" loading="lazy" width="70" height="67">
                            <div class="flex flex-col">
                                <label class="text-sm font-semibold">คลิกเพื่ออัปโหลดไฟล์</label>
                                <span class="text-xs">รองรับ .JPG, .PNG | ขนาดไม่เกิน 5 MB</span>
                                <input type="file" id="hiddenProfileUpload" name="images[]" multiple style="display:none">
                            </div>
                        </div>
                    </div>
                    @if ( old('coverImage') || old('images'))
                    <div id="image_listing" class="p-[16px] gap-[16px] bg-[#F8F8F8] rounded-[8px] mt-4">
                        
                    </div>
                    @endif
                </div>

                <div id="images_preview" class="flex flex-row gap-[24px]"></div>
                
                <span class="w-full min-h-[1px] divider clear-both"></span>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] justify-end">
                    <button type="submit" class="w-[200px] h-[48px] rounded-lg bg-[#286F51] text-white">บันทึกข้อมูล</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('style')
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> 
    <link href="{{ asset('flatpickr/flatpickr.min.css') }}" rel="stylesheet"/>
    <style>
        .req {color:red}
        .sub_topic:before {
            content: "";
            height:20px;
            width: 6px;
            border-radius: 4px;
            background-color: #286F51;
        }
        .select2-selection {
            border-radius: 0.5rem;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            padding-top: 0;
            padding-bottom: 0;
            height: 38px !important;
        }
        .select2-dropdown, .select2-selection {border-color: rgb(229, 231, 235) !important;}
        .select2-container--default .select2-selection--single .select2-selection__rendered {line-height: 38px !important;padding-left:0}
        .select2-container--default .select2-selection--single .select2-selection__arrow {height:38px !important;}

        /* Genel stil */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 24px;
            margin: 15px 10px 10px 0;
        }

        /* Giriş stil */
        .toggle-switch .toggle-input {display: none;}

        /* Anahtarın stilinin etrafındaki etiketin stil */
        .toggle-switch .toggle-label {
            position: absolute;
            top: 0;
            left: 0;
            width: 40px;
            height: 24px;
            background-color: #d5d5d5;
            border-radius: 34px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        /* Anahtarın yuvarlak kısmının stil */
        .toggle-switch .toggle-label::before {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            background-color: #fff;
            box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s;
        }

        /* Anahtarın etkin hale gelmesindeki stil değişiklikleri */
        .toggle-switch .toggle-input:checked + .toggle-label {
        background-color: #4CAF50;
        }

        .toggle-switch .toggle-input:checked + .toggle-label::before {
        transform: translateX(16px);
        }

        /* Light tema */
        .toggle-switch.light .toggle-label {
        background-color: #BEBEBE;
        }

        .toggle-switch.light .toggle-input:checked + .toggle-label {
        background-color: #9B9B9B;
        }

        .toggle-switch.light .toggle-input:checked + .toggle-label::before {
        transform: translateX(6px);
        }

        /* Dark tema */
        .toggle-switch.dark .toggle-label {
        background-color: #4B4B4B;
        }

        .toggle-switch.dark .toggle-input:checked + .toggle-label {
        background-color: #717171;
        }

        .toggle-switch.dark .toggle-input:checked + .toggle-label::before {
        transform: translateX(16px);
        }
        
    </style>
@endsection
@section('javascript')
    <script>
        document.getElementById('avatar').addEventListener('click', () => {
            document.getElementById('hiddenProfileUpload').click();
        });

        // จับ event เมื่อเลือกไฟล์
        document.getElementById('hiddenProfileUpload').addEventListener('change', (event) => {
            const files = event.target.files;
            let preview = document.getElementById('images_preview');
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