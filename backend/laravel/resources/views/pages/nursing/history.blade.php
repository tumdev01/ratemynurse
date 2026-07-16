@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    @include('pages.nursing.components.navigation')
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <form id="registerNurse" class="flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" method="POST" action="{{ route('nursing.history.store', $nursing->id) }}" enctype="multipart/form-data">
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
            <div class="flex flex-col justify-start bg-[#F0F9F4] p-[16px] rounded-md">
                <span class="htitle text-[16px] md:text-lg text-[#286F51]">เพิ่มข้อมูลพยาบาล / ผู้ดูแล</span>
                <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน</span>
            </div>

            <div class="flex flex-row justify-between bg-[#F7FCF9] px-[12px] py-[8px] rounded-md">
                <div class="flex flex-row gap-[8px] items-center">
                    <svg class="w-6 h-6 text-[#286F51]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <span class="text-md text-[#286F51] font-medium">
                        ข้อมูลการศึกษาและใบประกอบวิชาชีพ
                    </span>
                </div>
                <div class="flex flex-row gap-[8px] items-center">
                    <svg class="w-4 h-4 text-gray-200 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm9.408-5.5a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2h-.01ZM10 10a1 1 0 1 0 0 2h1v3h-1a1 1 0 1 0 0 2h4a1 1 0 1 0 0-2h-1v-4a1 1 0 0 0-1-1h-2Z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน</span>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="graducated">วุฒิการศึกษา <span class="req">*</span></label>
                    <select name="graducated" id="graducated" class="border rounded-lg px-3 py-2" required>
                        <option value="">-- เลือกระดับการศึกษา --</option>

                        @php
                            $selectedGrad = old('graducated', optional($nursing->cvs)->graducated);
                        @endphp

                        <option value="JHS" @selected($selectedGrad === 'JHS')>
                            มัธยมศึกษาตอนต้น (ม.3)
                        </option>
                        <option value="SHS" @selected($selectedGrad === 'SHS')>
                            มัธยมศึกษาตอนปลาย (ม.6)
                        </option>
                        <option value="VOC" @selected($selectedGrad === 'VOC')>
                            ประกาศนียบัตรวิชาชีพ (ปวช.)
                        </option>
                        <option value="HVC" @selected($selectedGrad === 'HVC')>
                            ประกาศนียบัตรวิชาชีพชั้นสูง (ปวส.)
                        </option>
                        <option value="AD" @selected($selectedGrad === 'AD')>
                            อนุปริญญา
                        </option>
                        <option value="BA" @selected($selectedGrad === 'BA')>
                            ปริญญาตรี
                        </option>
                    </select>

                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="edu_ins">สถานบันการศึกษา <span class="req">*</span></label>
                    <input type="text" name="edu_ins" id="edu_ins" placeholder="สถาบันการศึกษา"
                        class="border rounded-lg px-3 py-2" value="{{ old('edu_ins', optional($nursing->cvs)->edu_ins) }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="graducated_year">ปีที่จบการศึกษา <span class="req">*</span></label>
                    @php
                        $selectedYear = old(
                            'graducated_year',
                            optional($nursing->cvs)->graducated_year
                        );
                    @endphp

                    <select name="graducated_year" id="graducated_year" class="border rounded-lg px-3 py-2" required>
                        <option value="">-- เลือกปีที่จบ --</option>

                        @for ($year = now()->year; $year >= now()->year - 20; $year--)
                            @php
                                $thaiYear = $year + 543;
                            @endphp
                            <option value="{{ $thaiYear }}" @selected($selectedYear == $thaiYear)>
                                {{ $thaiYear }} ({{ $year }})
                            </option>
                        @endfor
                    </select>

                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="gpa">เกรดเฉลี่ยน (GPA) <span class="req">*</span></label>
                    <input type="text" name="gpa" id="gpa" placeholder="เกรดเฉลี่ย (GPA)"
                        class="border rounded-lg px-3 py-2" value="{{ old('gpa', optional($nursing->cvs)->gpa) }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="cert_no">เลขที่ใบประกอบวิชาชีพ <span class="req">*</span></label>
                    <input required
                        type="text"
                        name="cert_no"
                        id="cert_no"
                        placeholder="เลขที่ใบประกอบวิชาชีพ"
                        class="border rounded-lg px-3 py-2"
                        value="{{ old('cert_no', optional($nursing->cvs)->cert_no) }}"
                    />
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="cert_date">วันที่ออกใบประกอบวิชาชีพ <span class="req">*</span></label>
                    <input required type="text" name="cert_date" id="cert_date" placeholder="วว/ดด/ปป"
                        class="border rounded-lg px-3 py-2" value="{{ old('cert_date', optional($nursing->cvs)->cert_date) }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="cert_expire">วันหมดอายุใบประกอบวิชาชีพ <span class="req">*</span></label>
                    <input required type="text" name="cert_expire" id="cert_expire" placeholder="วว/ดด/ปป"
                        class="border rounded-lg px-3 py-2" value="{{ old('cert_expire', optional($nursing->cvs)->cert_expire) }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-col">
                <label class="mb-2 text-[#5A5A5A]" for="cvs_images">หลักฐานใบอนุญาตประกอบวิชาชีพ</label>
                <div class="border border-dashed rounded-lg h-[130px] flex justify-center items-center">
                    <div id="certificate_upload" class="flex flex-row gap-[16px] justify-center items-center">
                        <img id="certificate_avatar" src="https://ratemynurse.org/wp-content/uploads/2025/08/upload2.png" loading="lazy" width="70" height="67">
                        <div class="flex flex-col gap-[8px]">
                            <label class="text-sm text-[#286F51]">เพิ่มไฟล์เอกสาร</label>
                            <span class="text-xs text-[#8C8A94]">รองรับไฟล์ .pdf, jpg, ,jpeg, .png | ขนาดไม่เกิน 5 MB</span>
                            <input type="file" id="certificateUpload" name="cvs_images[]" multiple style="display:none">
                        </div>
                    </div>
                </div>
                <div id="cv_preview" class="mt-2">
                    @if($nursing->cvs && $nursing->cvs->images->count())
                        @foreach($nursing->cvs->images as $image)
                            <div data-cv-id="{{ $image->id }}" class="file-item flex flex-row justify-between bg-[#FBFBFB] rounded-md p-[12px] mb-2">
                                <div class="file-info flex flex-row gap-[8px]">
                                    @if($image->filetype =='application/pdf')
                                        <img class="file-icon w-16 h-16 object-cover" src="https://cdn-icons-png.flaticon.com/512/337/337946.png">
                                    @else
                                        <img class="file-icon w-16 h-16 object-cover" src="{{ $image->full_path }}">
                                    @endif
                                <div>
                                    <div class="file-name text-sm font-medium">{{ $image->name }}</div>
                                </div>
                            </div>
                            <button type="button" class="remove-btn" onclick="removeCvFile({{ $image->id }})">
                                <svg class="w-6 h-6 text-[#D62416]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166
                                    m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084
                                    a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0
                                    a48.108 48.108 0 0 0-3.478-.397m-12 .562
                                    c.34-.059.68-.114 1.022-.165m0 0
                                    a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916
                                    c0-1.18-.91-2.164-2.09-2.201
                                    a51.964 51.964 0 0 0-3.32 0
                                    c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0
                                    a48.667 48.667 0 0 0-7.5 0"></path>
                        </svg>
                    </button></div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="flex flex-col gap-[8px]">
                <label class="text-[#5A5A5A]" for="cert_etc">ใบประกาศนียบัตรเพิ่มเติม (ไม่บังคับ)</label>
                <textarea id="cert_etc" name="cert_etc" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="เช่น BLS, ACLS, CPN, หรือการอบรมพิเศษอื่นๆ">{{ old('cert_etc', optional($nursing->cvs)->cert_etc) }}</textarea>
            </div>

            <div class="flex flex-col gap-[8px]">
                <label class="text-[#5A5A5A]" for="extra_courses">การศึกษาต่อเนื่อง/การอบรม ในช่วง 2 ปีที่ผ่านมา (ไม่บังคับ)</label>
                <textarea id="extra_courses" name="extra_courses" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="ระบุหลักสูตรอบรมสัมนา หรือการศึกษาต่อเนื่องที่เข้าร่วม">{{ old('extra_courses', optional($nursing->cvs)->extra_courses) }}</textarea>
            </div>

            <div class="flex flex-row justify-between bg-[#F7FCF9] px-[12px] py-[8px] rounded-md">
                <div class="flex flex-row gap-[8px] items-center">
                    <svg class="w-6 h-6 text-[#286F51]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <span class="text-md text-[#286F51] font-medium">
                        ข้อมูลการทำงาน
                    </span>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="current_workplace">โรงพยาบาล/สถานพยาบาลปัจจุบัน <span class="req">*</span></label>
                    <input required type="text" name="current_workplace" id="current_workplace" placeholder="โรงพยาบาล/สถานพยาบาลปัจจุบัน"
                        class="border rounded-lg px-3 py-2" value="{{ old('current_workplace', optional($nursing->cvs)->current_workplace) }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                <div class="flex flex-col">
                    <label class="text-[#5A5A5A]" for="department">แผนก/หน่วยงาน <span class="req">*</span></label>
                    <input required type="text" name="department" id="department" placeholder="แผนก/หน่วยงาน"
                        class="border rounded-lg px-3 py-2" value="{{ old('department', optional($nursing->cvs)->department) }}"/>
                </div>
                <div class="flex flex-col">
                    <label class="text-[#5A5A5A]" for="position">ตำแหน่ง <span class="req">*</span></label>
                    <select name="position" id="position" class="border rounded-lg px-3 py-2" required>
                        <option value="">-- เลือกระดับการศึกษา --</option>
                        @php
                            $seletedPosition = old('position', optional($nursing->cvs)->position);
                        @endphp

                        <option value="RN" @selected($seletedPosition === 'RN')>
                            พยาบาลวิชาชีพ (RN)
                        </option>
                        <option value="PN" @selected($seletedPosition === 'PN')>
                            ผู้ช่วยพยาบาล (PN)
                        </option>
                        <option value="NA" @selected($seletedPosition === 'NA')>
                            พนักงานผู้ช่วยการพยาบาล (NA)
                        </option>
                        <option value="CG" @selected($seletedPosition === 'CG')>
                            คนดูแล (CG)
                        </option>
                        <option value="MAIN" @selected($seletedPosition === 'MAIN')>
                            แม่บ้าน (ดูแล ทำงานบ้านได้ด้วย)
                        </option>
                        <option value="ETC" @selected($seletedPosition === 'ETC')>
                            อื่น
                        </option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                <div class="flex flex-col">
                    <label class="text-[#5A5A5A]" for="exp">ประสบการณ์ทำงาน (ปี) <span class="req">*</span></label>
                    <select id="exp" name="exp" class="border rounded-lg px-3 py-2" required>
                        @php
                            $selectedExp = old('exp', optional($nursing->cvs)->exp);
                        @endphp
                        @for ( $i = 1; $i <= 10; $i++)    
                            <option value="{{$i}}" @selected($selectedExp === $i)>{{ $i }} ปี</option>
                        @endfor
                    <select>
                </div>
                <div class="flex flex-col">
                    <label class="text-[#5A5A5A]" for="work_type">ลักษณะการทำงาน <span class="req">*</span></label>
                    <select id="work_type" name="work_type" class="border rounded-lg px-3 py-2" required>
                        @php
                            $selectedWorkType = old('work_type', optional($nursing->cvs)->work_type);
                        @endphp
                        <option value="FULLTIME" @selected($selectedWorkType === 'FULLTIME')>เต็มเวลา</option>
                        <option value="PARTTIME" @selected($selectedWorkType === 'PARTTIME')>ไม่เต็มเวลา</option>
                        <option value="DEPEND_ON" @selected($selectedWorkType === 'DEPEND_ON')>ตามงาน</option>
                        <option value="ROUND_TRIP" @selected($selectedWorkType === 'ROUND_TRIP')>ไป-กลับ</option>
                        <option value="STAY" @selected($selectedWorkType === 'STAY')>ค้างคืน</option>
                    <select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                <div class="flex flex-col">
                    <label for="extra_shirft" class="text-[#5A5A5A]">ความพร้อมในการทำงานเวรพิเศษ <span class="req">*</span></label>
                    <select id="extra_shirft" name="extra_shirft" class="border rounded-lg px-3 py-2" required>
                        @php
                            $selectedExtraShirft = old('extra_shirft', optional($nursing->cvs)->extra_shirft);
                        @endphp
                        <option value="NIGHT_WEEKEND" @selected($selectedExtraShirft === 'NIGHT_WEEKEND')>สามารถทำเวรดึกและเวรเสาร์-อาทิตย์</option>
                        <option value="NIGHT" @selected($selectedExtraShirft === 'NIGHT')>สามารถทำเวรดึก</option>
                        <option value="WEEKEND" @selected($selectedExtraShirft === 'WEEKEND')>สามารถทำเวรเสาร์-อาทิตย์</option>
                        <option value="ROUND_TRIP" @selected($selectedExtraShirft === 'ROUND_TRIP')>สามารถทำเวรไป-กลับ</option>
                        <option value="STAY" @selected($selectedExtraShirft === 'STAY')>สามารถทำเวรค้างคืน</option>
                        <option value="OTHER" @selected($selectedExtraShirft === 'OTHER')>สามารถทำเวรอื่นๆ</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="languages" class="text-[#5A5A5A]">ภาษาที่สามารถสื่อสารได้ <span class="req">*</span></label>
                    <input required type="text" name="languages" id="languages" placeholder="เช่น ไทย, อังกฤษ"
                        class="border rounded-lg px-3 py-2" value="{{ old('languages', optional($nursing->cvs)->languages) }}"/>
                    <label class="error text-xs text-red-600"></label>
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
        .swal2-confirm-custom {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
            padding: 10px 24px !important;
            font-weight: 500 !important;
            border-radius: 0.25rem !important;
        }

        .swal2-cancel-custom {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
            padding: 10px 24px !important;
            font-weight: 500 !important;
            border-radius: 0.25rem !important;
        }

        .swal2-confirm-custom:hover,
        .swal2-confirm-custom:focus {
            background-color: #bb2d3b !important;
            border-color: #b02a37 !important;
        }

        .swal2-cancel-custom:hover,
        .swal2-cancel-custom:focus {
            background-color: #5c636a !important;
            border-color: #565e64 !important;
        }
    </style>
@endsection
@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('flatpickr/monthSelect/index.js') }}"></script>
    <script src="{{ asset('flatpickr/th.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let CVSselectedFiles = [];

        document.addEventListener('DOMContentLoaded', () => {
            const certificate_upload = document.getElementById('certificate_upload');
            const certificateUpload = document.getElementById('certificateUpload');
            const cv_preview = document.getElementById('cv_preview');
            const imgallowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

            if (!certificate_upload || !certificateUpload || !cv_preview) return;

            // ⭐ sync files กลับเข้า input
            function updateFileInput() {
                const dataTransfer = new DataTransfer();
                CVSselectedFiles.forEach(file => dataTransfer.items.add(file));
                certificateUpload.files = dataTransfer.files;
            }

            certificate_upload.addEventListener('click', () => certificateUpload.click());

            certificateUpload.addEventListener('change', (event) => {
                const files = Array.from(event.target.files);

                files.forEach(file => {
                    if (!imgallowedTypes.includes(file.type)) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            text: 'อนุญาตเฉพาะ WEBP, JPG, JPEG, PNG, PDF เท่านั้น',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                        return;
                    }

                    // ป้องกันไฟล์ซ้ำ
                    if (CVSselectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                        return;
                    }

                    CVSselectedFiles.push(file);
                    updateFileInput(); // ✅ สำคัญ

                    const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                    const listItem = document.createElement('div');
                    listItem.className = 'file-item flex flex-row justify-between bg-[#FBFBFB] rounded-md p-[12px] mb-2';

                    let fileInfo = document.createElement('div');
                    fileInfo.className = 'file-info flex flex-row gap-[8px]';

                    let icon = document.createElement('img');
                    icon.className = 'file-icon w-16 h-16 object-cover';
                    icon.src = file.type === 'application/pdf'
                        ? 'https://cdn-icons-png.flaticon.com/512/337/337946.png'
                        : URL.createObjectURL(file);

                    let details = document.createElement('div');
                    details.innerHTML = `
                        <div class="file-name text-base font-medium">${file.name}</div>
                        <div class="file-size text-sm">ขนาดไฟล์: ${fileSize} MB</div>
                    `;

                    let removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'remove-btn';
                    removeBtn.innerHTML = `
                        <svg class="w-6 h-6 text-[#D62416]" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166
                                m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084
                                a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0
                                a48.108 48.108 0 0 0-3.478-.397m-12 .562
                                c.34-.059.68-.114 1.022-.165m0 0
                                a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916
                                c0-1.18-.91-2.164-2.09-2.201
                                a51.964 51.964 0 0 0-3.32 0
                                c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0
                                a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    `;

                    // 🗑 ลบไฟล์ + sync input
                    removeBtn.addEventListener('click', () => {
                        listItem.remove();
                        CVSselectedFiles = CVSselectedFiles.filter(f => f !== file);
                        updateFileInput(); // ✅ แก้ปัญหา cvs_images[] ยังส่ง
                        console.log('เหลือไฟล์:', CVSselectedFiles.length);
                    });

                    fileInfo.appendChild(icon);
                    fileInfo.appendChild(details);
                    listItem.appendChild(fileInfo);
                    listItem.appendChild(removeBtn);
                    cv_preview.appendChild(listItem);
                });

                console.log('ไฟล์ทั้งหมด:', CVSselectedFiles.length);
            });
        });


        flatpickr('#cert_date, #cert_expire', {
            yearModifier: 543,
            altInput: true,
            altFormat: 'd F B',
            locale: 'th',
            dateFormat: 'Y-m-d',
            defaultDate: null,
            onChange (_, d) {
                month = d
            },
            onReady (_, d) {
                month = null
            },
        });

        function removeCvFile(cv_id) {
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "คุณต้องการลบไฟล์นี้ใช่หรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    confirmButton: 'swal2-confirm-custom',
                    cancelButton: 'swal2-cancel-custom'
                },
                buttonsStyling: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังลบ...',
                        html: 'กรุณารอสักครู่',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    axios.delete(`/nursing/cv/${cv_id}/delete`, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => {
                        if (response.data.success) {
                            Swal.fire({
                                title: 'ลบสำเร็จ!',
                                text: 'ไฟล์ถูกลบเรียบร้อยแล้ว',
                                icon: 'success',
                                confirmButtonText: 'ตกลง',
                                customClass: {
                                    confirmButton: 'swal2-confirm-custom'
                                }
                            }).then(() => {
                                const element = document.querySelector(`[data-cv-id="${cv_id}"]`);
                                if (element) {
                                    element.style.transition = 'opacity 0.3s';
                                    element.style.opacity = '0';
                                    setTimeout(() => element.remove(), 300);
                                }
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด!',
                            text: error.response?.data?.message || 'ไม่สามารถลบไฟล์ได้',
                            icon: 'error',
                            confirmButtonText: 'ตกลง',
                            customClass: {
                                confirmButton: 'swal2-confirm-custom'
                            }
                        });
                    });
                }
            });
        }

    </script>
@endsection