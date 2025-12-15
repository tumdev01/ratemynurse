@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    @include('pages.nursing.components.navigation')
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <form id="detailNurse" class="text-[16px] flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" method="POST" action="{{ route('nursing.store') }}" enctype="multipart/form-data">
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
                <label class="text-[#5A5A5A] text-medium" for="extra_courses">รายละเอียดเกี่ยวกับบริการของคุณ<span class="req">*</span></label>
                <textarea id="extra_courses" name="extra_courses" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="คำอธิบายรายละเอียดเกี่ยวกับบริการของคุณเพื่อให้ผู้ใช้บริการสนใจ">{{ old('extra_courses') }}</textarea>
                <span class="text-sm text-[#8C8A94]">คำอธิบายจะแสดงในหน้าข้อมูลบริการของคุณ กรุณาอธิบายรายละเอียดบริการของคุณ</span>
            </div>

            <div class="flex flex-col">
                <label class="mb-2 text-[#5A5A5A] text-medium" for="cvs_images">รูปภาพเพิ่มเติม</label>
                <span class="text-sm text-[#8C8A94] mb-2">รูปภาพที่อัปโหลดจะแสดงในหน้าข้อมูลบริการของคุณ</span>
                <div class="border border-dashed rounded-lg h-[130px] flex justify-center items-center">
                    <div id="certificate_upload" class="flex flex-row gap-[16px] justify-center items-center">
                        <img id="avatar" src="https://ratemynurse.org/wp-content/uploads/2025/08/upload2.png" loading="lazy" width="70" height="67">
                        <div class="flex flex-col gap-[8px]">
                            <label class="text-sm text-[#286F51]">เพิ่มรูปภาพ</label>
                            <span class="text-xs text-[#8C8A94]">รองรับไฟล์ jpg, ,jpeg, .png | ขนาดไม่เกิน 5 MB</span>
                            <input type="file" id="hiddenProfileUpload" name="cvs_images[]" multiple style="display:none">
                        </div>
                    </div>
                </div>
                @if ( old('coverImage') || old('images'))
                <div id="image_listing" class="p-[16px] gap-[16px] bg-[#F8F8F8] rounded-[8px] mt-4">
                    
                </div>
                @endif
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
                        $facilities_check = old('facilities') ?? [];
                    @endphp
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="facilities[]" value="ELEVATOR"
                            {{ in_array('ELEVATOR', $facilities_check) ? 'checked' : '' }}>
                        การดูแลผู้ป่วยติดเตียง
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="facilities[]" value="WHEELCHAIR_RAMP"
                            {{ in_array('WHEELCHAIR_RAMP', $facilities_check) ? 'checked' : '' }}>
                        การให้ยาและการฉีดยา
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="facilities[]" value="BATHROOM_GRAB_BAR"
                            {{ in_array('BATHROOM_GRAB_BAR', $facilities_check) ? 'checked' : '' }}>
                        การช่วยเหลือกิจกรรมประจำวัน (ADL)
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="facilities[]" value="EMERGENCY_BELL"
                            {{ in_array('EMERGENCY_BELL', $facilities_check) ? 'checked' : '' }}>
                        การดูแลแผลและแต่งแผล
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="facilities[]" value="CAMERA"
                            {{ in_array('CAMERA', $facilities_check) ? 'checked' : '' }}>
                        การใช้เครื่องทางการแพทย์
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="facilities[]" value="FIRE_SYSTEM"
                            {{ in_array('FIRE_SYSTEM', $facilities_check) ? 'checked' : '' }}>
                        การดูแลผู้ป่วยโรคเรื้อรัง
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="facilities[]" value="BACKUP_GENERATOR"
                            {{ in_array('BACKUP_GENERATOR', $facilities_check) ? 'checked' : '' }}>
                        การดูแลผู้ป่วยโรคอัลไซเมอร์/สมองเสื่อม
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="facilities[]" value="AIR_CONDITIONER"
                            {{ in_array('AIR_CONDITIONER', $facilities_check) ? 'checked' : '' }}>
                        การนวดและกายภาพบำบัด
                    </div>
                    <div class="flex flex-row gap-[8px] items-center text-[#1F1F1F]">
                        <input class="rounded-md border border-gray-200 w-5 h-5" type="checkbox" name="facilities[]" value="GARDEN_AREA"
                            {{ in_array('GARDEN_AREA', $facilities_check) ? 'checked' : '' }}>
                        ทักษะอื่นๆ
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