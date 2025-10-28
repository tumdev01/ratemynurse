@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    @include('pages.nursing.components.navigation')
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <form id="detailNurse" class="text-[16px] flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" method="POST" action="{{ route('nursing.detail.update', $nursing->id) }}" enctype="multipart/form-data">
            @csrf
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
            <input type="hidden" name="user_type" value="NURSING">

            <div class="flex flex-col gap-[8px]">
                <label class="text-[#5A5A5A] text-medium" for="about">รายละเอียดเกี่ยวกับบริการของคุณ<span class="req">*</span></label>
                <textarea id="about" name="about" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="คำอธิบายรายละเอียดเกี่ยวกับบริการของคุณเพื่อให้ผู้ใช้บริการสนใจ">{{ old('about', $nursing->detail->about) }}</textarea>
                <span class="text-sm text-[#8C8A94]">คำอธิบายจะแสดงในหน้าข้อมูลบริการของคุณ กรุณาอธิบายรายละเอียดบริการของคุณ</span>
            </div>

            <div class="flex flex-col">
                <label class="text-[#5A5A5A] text-medium mb-2" for="address">รูปภาพเพิ่มเติม</label>
                <span class="text-sm text-[#8C8A94] mb-2">รูปภาพที่อัปโหลดจะแสดงในหน้าข้อมูลบริการของคุณ</span>
                <div class="border border-dashed rounded-lg h-[130px] flex justify-center items-center">
                    <div id="certificate_upload" class="flex flex-row gap-[16px] justify-center">
                        <img id="avatar" src="https://ratemynurse.org/wp-content/uploads/2025/08/upload2.png" loading="lazy" width="70" height="67">
                        <div class="flex flex-col">
                            <label class="text-sm font-semibold">เพิ่มรูปภาพ</label>
                            <span class="text-xs">รองรับ .JPG, .PNG | ขนาดไม่เกิน 5 MB</span>
                            <input type="file" id="hiddenProfileUpload" name="images[]" multiple style="display:none">
                        </div>
                    </div>
                </div>
                @if ( old('images', $nursing->detail->images))
                <div id="image_listing" class="p-[16px] gap-[16px] bg-[#F8F8F8] rounded-[8px] mt-4 flex flex-row gap-[24px] flex-wrap">
                    @foreach($nursing->detail->images as $image)
                        <div class="rounded-md w-[92px] h-[92px] relative">
                            <img class="object-cover w-full h-full rounded-md w-[92px] h-[92px]" src="{{ $image->full_path }}">
                            <span onclick="imageDelete({{ $image->id }})" class="absolute w-[24px] h-[24px] bottom-2 right-2 rounded-full bg-red-500 flex items-center justify-center cursor-pointer">
                                <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/>
                                </svg>
                            </span>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div id="images_preview" class="flex flex-row gap-[24px]"></div>

            <div class="flex flex-row justify-between bg-[#F7FCF9] px-[12px] py-[8px] rounded-md">
                <div class="flex flex-row gap-[8px] items-center">
                    <svg class="w-6 h-6 text-[#286F51]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <span class="text-md text-[#286F51] font-medium">
                        ลักษณะการทำงาน
                    </span>
                </div>
            </div>

            <div class="gap-[16px] flex flex-col">
                <div class="flex flex-row gap-[8px] items-center font-medium text-black">
                    เลือกอย่างน้อย 1 รายการ
                </div>
                <div class="flex flex-col flex-wrap gap-[16px]">
                    @php
                        $hirerule_check = old('hire_rules', $nursing->detail->hire_rules) ?? [];
                    @endphp
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="hire_rules[]" value="FULL_STAY"
                            {{ in_array('FULL_STAY', $hirerule_check) ? 'checked' : '' }}>
                        อยู่ประจำ ค้างคืน พักอาศัยกับคนไข้
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="hire_rules[]" value="FULL_ROUND"
                            {{ in_array('FULL_ROUND', $hirerule_check) ? 'checked' : '' }}>
                        อยู่ประจำ ไปกลับ
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="hire_rules[]" value="PART_STAY"
                            {{ in_array('PART_STAY', $hirerule_check) ? 'checked' : '' }}>
                        ชั่วคราว ค้างคืน พักอาศัยกับคนไข้
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="hire_rules[]" value="PART_ROUND"
                            {{ in_array('PART_ROUND', $hirerule_check) ? 'checked' : '' }}>
                        ชั่วคราว ไปกลับ
                    </div>
                </div>
            </div>

            <div class="flex flex-row justify-between bg-[#F7FCF9] px-[12px] py-[8px] rounded-md">
                <div class="flex flex-row gap-[8px] items-center">
                    <svg class="w-6 h-6 text-[#286F51]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <span class="text-md text-[#286F51] font-medium">
                        ความเชี่ยวชาญพิเศษ
                    </span>
                </div>
            </div>

            <div class="gap-[16px] flex flex-col">
                <div class="flex flex-row gap-[8px] items-center font-medium text-black">
                    เลือกอย่างน้อย 1 รายการ
                </div>
                <span class="text-sm text-[#1F1F1F]">เพิ่มทักษะความเชี่ยวชาญพิเศษ ช่วยเพิ่มความน่าสนใจให้กับประกาศของคุณ</span>
                <div class="flex flex-col flex-wrap gap-[16px]">
                    @php
                        $skills_check = old('skills', $nursing->detail->skills) ?? [];
                    @endphp
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="BASIC_PHYSIOTHERAPY"
                            {{ in_array('BASIC_PHYSIOTHERAPY', $skills_check) ? 'checked' : '' }}>
                        กายภาพบำบัดเบื้องต้น
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="EATEN"
                            {{ in_array('EATEN', $skills_check) ? 'checked' : '' }}>
                        การทานอาหาร
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="MEDICATION_VITALSIGNS"
                            {{ in_array('MEDICATION_VITALSIGNS', $skills_check) ? 'checked' : '' }}>
                        จัดยา และวัดสัญญาณชีพ
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="BOWELCARE"
                            {{ in_array('BOWELCARE', $skills_check) ? 'checked' : '' }}>
                        การขับถ่าย/ชําระร่างกาย
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="FEEDING"
                            {{ in_array('FEEDING', $skills_check) ? 'checked' : '' }}>
                        ป้อนอาหาร
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="GLUCOSE_INSULIN"
                            {{ in_array('GLUCOSE_INSULIN', $skills_check) ? 'checked' : '' }}>
                        เจาะตรวจน้ำตาลและฉีดอินซูลิน
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="TUBE_FEEDING"
                            {{ in_array('TUBE_FEEDING', $skills_check) ? 'checked' : '' }}>
                        ให้อาหารทางสายยาง (ติดเตียง)
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="URINARY_CATHETER"
                            {{ in_array('URINARY_CATHETER', $skills_check) ? 'checked' : '' }}>
                        ใส่สายสวนปัสสาวะ
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="SUCTION_SECRETION"
                            {{ in_array('SUCTION_SECRETION', $skills_check) ? 'checked' : '' }}>
                        ดูดเสมหะ (ติดเตียง)
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="BEDRIDDEN_CARE"
                            {{ in_array('BEDRIDDEN_CARE', $skills_check) ? 'checked' : '' }}>
                        การดูแลผู้ป่วยติดเตียง
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="MEDICATION_AND_INJECTION"
                            {{ in_array('MEDICATION_AND_INJECTION', $skills_check) ? 'checked' : '' }}>
                        การให้ยาและการฉีดยา
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="DAILY_ACTIVITY_ASSISTANCE"
                            {{ in_array('DAILY_ACTIVITY_ASSISTANCE', $skills_check) ? 'checked' : '' }}>
                        การช่วยเหลือกิจกรรมประจำวัน (ADL)
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="WOUND_CARE"
                            {{ in_array('WOUND_CARE', $skills_check) ? 'checked' : '' }}>
                        การดูแลแผลและแต่งแผล
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="MEDICAL_EQUIPMENT_USE"
                            {{ in_array('MEDICAL_EQUIPMENT_USE', $skills_check) ? 'checked' : '' }}>
                        การใช้เครื่องทางการแพทย์
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="CHRONIC_CARE"
                            {{ in_array('CHRONIC_CARE', $skills_check) ? 'checked' : '' }}>
                        การดูแลผู้ป่วยโรคเรื้อรัง
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="DEMENTIA_CARE"
                            {{ in_array('DEMENTIA_CARE', $skills_check) ? 'checked' : '' }}>
                        การดูแลผู้ป่วยโรคอัลไซเมอร์/สมองเสื่อม
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="skills[]" value="OTHER_SKILLS"
                            {{ in_array('OTHER_SKILLS', $skills_check) ? 'checked' : '' }}>
                        ทักษะอื่นๆ
                    </div>
                    <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                        <div class="w-full flex flex-col gap-[8px]">
                            <label for="other_skills">ทักษะอื่นๆ <span class="req">*</span></label>
                            <input required type="text" name="other_skills" id="other_skills" maxlength="255" placeholder="ทักษะอื่นๆ คั่นด้วย , เช่น การอ่าน, การเล่าเรื่อง"
                                class="border rounded-lg px-3 py-2" value="{{ old('other_skills', $nursing->detail->other_skills) }}"/>
                            <label class="error text-xs text-red-600"></label>
                        </div>
                    </div>
                </div>
            </div>

            <span class="w-full min-h-[1px] divider clear-both"></span>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] justify-end">
                <button type="submit" class="w-[200px] h-[48px] rounded-lg bg-[#286F51] text-white">บันทึกข้อมูล</button>
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
        #map_show iframe {width: 100%!important; height: 100% !important;}
    </style>
@endsection
@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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