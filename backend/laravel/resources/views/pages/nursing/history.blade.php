@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    @include('pages.nursing.components.navigation')
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <form id="registerNurse" class="flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" method="POST" action="{{ route('nursing.store') }}" enctype="multipart/form-data">
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
                        <option value="JHS">มัธยมศึกษาตอนต้น (ม.3)</option>
                        <option value="SHS">มัธยมศึกษาตอนปลาย (ม.6)</option>
                        <option value="VOC">ประกาศนียบัตรวิชาชีพ (ปวช.)</option>
                        <option value="HVC">ประกาศนียบัตรวิชาชีพชั้นสูง (ปวส.)</option>
                        <option value="AD">อนุปริญญา</option>
                        <option value="BA">ปริญญาตรี</option>
                    </select>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="edu_ins">สถานบันการศึกษา <span class="req">*</span></label>
                    <input type="text" name="edu_ins" id="edu_ins" maxlength="10" placeholder="สถาบันการศึกษา"
                        class="border rounded-lg px-3 py-2" value="{{ old('edu_ins') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="graducated_year">ปีที่จบการศึกษา <span class="req">*</span></label>
                    <select name="graducated_year" id="graducated_year" class="border rounded-lg px-3 py-2" required>
                        @for ($year = now()->year; $year >= now()->year - 20; $year--)
                            <option value="{{ $year + 543 }}">{{ $year + 543 }} ({{$year}})</option>
                        @endfor
                    </select>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="gpa">เกรดเฉลี่ยน (GPA) <span class="req">*</span></label>
                    <input type="text" name="gpa" id="gpa" maxlength="10" placeholder="เกรดเฉลี่ย (GPA)"
                        class="border rounded-lg px-3 py-2" value="{{ old('gpa') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="cert_no">เลขที่ใบประกอบวิชาชีพ <span class="req">*</span></label>
                    <input required type="text" name="cert_no" id="cert_no" maxlength="10" placeholder="เลขที่ใบประกอบวิชาชีพ"
                        class="border rounded-lg px-3 py-2" value="{{ old('cert_no') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="cert_date">วันที่ออกใบประกอบวิชาชีพ <span class="req">*</span></label>
                    <input required type="text" name="cert_date" id="cert_date" placeholder="วว/ดด/ปป"
                        class="border rounded-lg px-3 py-2" value="{{ old('cert_date') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label class="text-[#5A5A5A]" for="cert_expire">วันหมดอายุใบประกอบวิชาชีพ <span class="req">*</span></label>
                    <input required type="text" name="cert_expire" id="cert_expire" placeholder="วว/ดด/ปป"
                        class="border rounded-lg px-3 py-2" value="{{ old('cert_expire') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-col">
                <label class="mb-2 text-[#5A5A5A]" for="cvs_images">หลักฐานใบอนุญาตประกอบวิชาชีพ</label>
                <div class="border border-dashed rounded-lg h-[130px] flex justify-center items-center">
                    <div id="certificate_upload" class="flex flex-row gap-[16px] justify-center items-center">
                        <img id="avatar" src="https://ratemynurse.org/wp-content/uploads/2025/08/upload2.png" loading="lazy" width="70" height="67">
                        <div class="flex flex-col gap-[8px]">
                            <label class="text-sm text-[#286F51]">เพิ่มไฟล์เอกสาร</label>
                            <span class="text-xs text-[#8C8A94]">รองรับไฟล์ .pdf, jpg, ,jpeg, .png | ขนาดไม่เกิน 5 MB</span>
                            <input type="file" id="hiddenProfileUpload" name="cvs_images[]" multiple style="display:none">
                        </div>
                    </div>
                </div>
                @if ( old('coverImage') || old('images'))
                <div id="image_listing" class="p-[16px] gap-[16px] bg-[#F8F8F8] rounded-[8px] mt-4">
                    
                </div>
                @endif
            </div>

            <div class="flex flex-col gap-[8px]">
                <label class="text-[#5A5A5A]" for="cert_etc">ใบประกาศนียบัตรเพิ่มเติม (ไม่บังคับ) <span class="req">*</span></label>
                <textarea id="cert_etc" name="cert_etc" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="เช่น BLS, ACLS, CPN, หรือการอบรมพิเศษอื่นๆ">{{ old('address') }}</textarea>
            </div>

            <div class="flex flex-col gap-[8px]">
                <label class="text-[#5A5A5A]" for="extra_courses">การศึกษาต่อเนื่อง/การอบรม ในช่วง 2 ปีที่ผ่านมา (ไม่บังคับ) <span class="req">*</span></label>
                <textarea id="extra_courses" name="extra_courses" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="ระบุหลักสูตรอบรมสัมนา หรือการศึกษาต่อเนื่องที่เข้าร่วม">{{ old('extra_courses') }}</textarea>
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
                    <input required type="text" name="current_workplace" id="current_workplace" maxlength="10" placeholder="โรงพยาบาล/สถานพยาบาลปัจจุบัน"
                        class="border rounded-lg px-3 py-2" value="{{ old('current_workplace') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                <div class="flex flex-col">
                    <label class="text-[#5A5A5A]" for="department">แผนก/หน่วยงาน <span class="req">*</span></label>
                    <select id="department" name="department" class="border rounded-lg px-3 py-2" required>
                        <option value="">ศัลยกรรม</option>
                    <select>
                </div>
                <div class="flex flex-col">
                    <label class="text-[#5A5A5A]" for="position">ตำแหน่ง <span class="req">*</span></label>
                    <select id="position" name="position" class="border rounded-lg px-3 py-2" required>
                        <option value="RN">พยาบาลวิชาชีพ (RN)</option>
                        <option value="PN">ผู้ช่วยพยาบาล (PN)</option>
                        <option value="NA">พนักงานผู้ช่วยการพยาบาล (NA)</option>
                        <option value="CG">คนดูแล (CG)</option>
                        <option value="MAIN">แม่บ้าน (ดูแล ทำงานบ้านได้ด้วย)</option>
                        <option value="ETC">อื่น</option>
                    <select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                <div class="flex flex-col">
                    <label class="text-[#5A5A5A]" for="exp">ประสบการณ์ทำงาน (ปี) <span class="req">*</span></label>
                    <select id="exp" name="exp" class="border rounded-lg px-3 py-2" required>
                        @for ( $i = 1; $i <= 10; $i++)    
                            <option value="{{$i}}">{{ $i }} ปี</option>
                        @endfor
                    <select>
                </div>
                <div class="flex flex-col">
                    <label class="text-[#5A5A5A]" for="work_type">ลักษณะการทำงาน <span class="req">*</span></label>
                    <select id="work_type" name="work_type" class="border rounded-lg px-3 py-2" required>
                        <option value="FULL_TIME">Full-time</option>
                        <option value="PART_TIME">Part-time</option>
                    <select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                <div class="flex flex-col">
                    <label for="extra_shirft" class="text-[#5A5A5A]">ความพร้อมในการทำงานเวรพิเศษ <span class="req">*</span></label>
                    <select id="extra_shirft" name="extra_shirft" class="border rounded-lg px-3 py-2" required>
                        <option>สามารถทำเวรดึกและเวรเสาร์-อาทิตย์</option>
                    <select>
                </div>
                <div class="flex flex-col">
                    <label for="languages" class="text-[#5A5A5A]">ภาษาที่สามารถสื่อสารได้ <span class="req">*</span></label>
                    <input required type="text" name="languages" id="languages" placeholder="เช่น ไทย, อังกฤษ"
                        class="border rounded-lg px-3 py-2" value="{{ old('languages') }}"/>
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

        document.querySelector('.profile-upload').addEventListener('click', () => {
            document.getElementById('hiddenProfileUpload').click();
        });

        document.addEventListener('DOMContentLoaded', function () {
            const hiddenInput = document.getElementById('hiddenProfileUpload');
            const profilePreview = document.getElementById('profile-preview');
            const uploadSpan = document.querySelector('#profile_upload .profile-upload:not(#profile-preview)');
            const errorEl = document.querySelector('#profile_upload .upload_error');

            if (!hiddenInput || !profilePreview || !uploadSpan || !errorEl) return;

            // ✅ ให้คลิกได้แค่ปุ่ม "อัพโหลดรูปภาพ"
            uploadSpan.addEventListener('click', () => hiddenInput.click());

            hiddenInput.addEventListener('change', function () {
                errorEl.innerHTML = '';
                removePreview();

                const file = this.files[0];
                if (!file) return;

                const maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    errorEl.innerHTML = "❌ ไฟล์ต้องมีขนาดไม่เกิน 5MB";
                    this.value = "";
                    return;
                }

                const allowedTypes = ['image/jpeg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    errorEl.innerHTML = "❌ อนุญาตเฉพาะไฟล์ JPG และ PNG เท่านั้น";
                    this.value = "";
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement('img');
                    img.setAttribute('data-profile-preview', '1');
                    img.src = e.target.result;
                    img.alt = 'Profile preview';
                    img.className = 'absolute top-0 left-0 w-full h-full object-cover rounded-full';

                    profilePreview.insertBefore(img, profilePreview.firstChild);
                };
                reader.readAsDataURL(file);
            });

            function removePreview() {
                const existing = profilePreview.querySelector('img[data-profile-preview]');
                if (existing) existing.remove();
            }
        });

        flatpickr('#date_of_birth', {
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

        const phone = document.getElementById('phone');
        phone.addEventListener('input', function () {
            let typingTimer;
            const doneTypingInterval = 500; // 0.5 วินาที
            clearTimeout(typingTimer); // reset timer ทุกครั้งที่พิมพ์
            typingTimer = setTimeout(() => {
                validatePhone(this);
            }, doneTypingInterval);
        });

        ajaxCallDropdownOption('#province', '/api/provinces_list', 'กรุณาเลือกจังหวัด');

        function setOldValue(id, oldValue, oldText) {
            if(oldValue && oldText) {
                const option = new Option(oldText, oldValue, true, true);
                $(id).append(option).trigger('change');
            }
        }

        // เรียกหลัง initialize Select2
        setOldValue('#province', "{{ old('province_id') }}", "{{ old('province_name') }}");
        setOldValue('#district', "{{ old('district_id') }}", "{{ old('district_name') }}");
        setOldValue('#sub_district', "{{ old('sub_district_id') }}", "{{ old('sub_district_name') }}");
    
        let provinceTxt = $('#provinceTxt').val() ?? '';
        $('#province:selected').html(provinceTxt);

        let districtTxt = $('#districtTxt').val() ?? '';
        $('#district:selected').html(districtTxt);

        let subDistrictTxt = $('#subDistrictTxt').val() ?? '';
        $('#sub_district:selected').html(subDistrictTxt);
        

        function validatePhone(input) {
            const thaiPhonePattern = /^0[0-9]{9}$/;
            const errorElement = input.parentElement.querySelector('.error');

            if (errorElement) {
                if (!thaiPhonePattern.test(input.value)) {
                    errorElement.textContent = "เบอร์โทรต้องขึ้นต้นด้วย 0 และมีทั้งหมด 10 หลัก";
                } else {
                    errorElement.textContent = "";
                }
            }
        }

        function ajaxCallDropdownOption(id, url, placeholder) {
            $(id).select2({
                placeholder: placeholder,
                ajax: {
                    transport: function (params, success, failure) {
                        axios.get(url, {
                            params: params.data // ส่ง query ไปกับ request เช่น search, pagination
                        })
                        .then(function (response) {
                            const results = response.data.data.map(function (item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                };
                            });

                            // ถ้าอยากเซ็ตค่า textbox ตอนเลือก dropdown ให้ทำใน event ของ select2
                            $(id).on('select2:select', function (e) {
                                const data = e.params.data;
                                if(id === '#province') {
                                    $('#provinceTxt').val(data.text);
                                } else if (id === '#district') {
                                    $('#districtTxt').val(data.text);
                                } else if(id === '#sub_district') {
                                    $('#subDistrictTxt').val(data.text);
                                }
                            });

                            success({ results: results });
                        })
                        .catch(function (error) {
                            failure(error);
                        });
                    },
                    delay: 250,
                    cache: true
                }
            });
        }

        function handleSelectProvince() {
            let province = $('#province option:selected');
            provinceTxt = province.text() ?? '';
            ajaxCallDropdownOption('#district', '/api/districts_list/' + $('#province').val() , 'เลือกอำเภอ/เขต');
        }
        function handleSelectDistrict() {
            let district = $('#district');
            districtTxt = $('#district option:selected').text() ?? '';
            ajaxCallDropdownOption('#sub_district', '/api/sub_districts_list/' + $('#district').val(), 'เลือกตำบล/แขวง');
        }

        function handleSelectSubDistrict() {
            let subDistrict = $('#sub_district');
            subDistrictTxt = $('#sub_district option:selected').text() ?? '';
        }
    </script>
@endsection