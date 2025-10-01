@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    
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
                <span class="htitle text-[16px] md:text-lg text-[#286F51]">แก้ไขข้อมูล {{ $nursing->profile->name }}</span>
                <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน</span>
            </div>

            <div class="flex flex-row justify-between bg-[#F7FCF9] px-[12px] py-[8px] rounded-md">
                <div class="flex flex-row gap-[8px] items-center">
                    <svg class="w-6 h-6 text-[#286F51]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <span class="text-md text-[#286F51] font-medium">
                        ข้อมูลส่วนตัว
                    </span>
                </div>
                <div class="flex flex-row gap-[8px] items-center">
                    <svg class="w-4 h-4 text-gray-200 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm9.408-5.5a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2h-.01ZM10 10a1 1 0 1 0 0 2h1v3h-1a1 1 0 1 0 0 2h4a1 1 0 1 0 0-2h-1v-4a1 1 0 0 0-1-1h-2Z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน</span>
                </div>
            </div>

            <div class="flex flex-col">
                <div class="rounded-lg h-[130px] flex items-center">
                    <div id="profile_upload" class="flex flex-row gap-[16px] items-center">
                        <div id="profile-preview" class="profile-upload w-[100px] h-[100px] relative rounded-full bg-gray-100">
                            @if($nursing->coverImage)
                                <img class="rounded-full w-full h-full cover" src="{{ asset($nursing->coverImage->path) }}"/>
                            @endif
                            <img src="{{ asset('img/ph_camera-fill.svg') }}" class="w-[32px] h-[32px] absolute p-2 rounded-full bg-gray-200 bottom-0 right-0">
                        </div>
                        <div class="flex flex-col gap-[8px]">
                            <label class="text-sm font-medium text-[#1F1F1F]">รูปโปรไฟล์ของคุณ</label>
                            <span class="text-xs text-[#8C8A94]">จำกัดการอัปโหลด 1 รูปเท่านั้นไฟล์ .jpg, .png ขนาดไม่เกิน 5MB</span>
                            <span class="profile-upload text-[#286F51] font-medium">อัพโหลดรูปภาพ</span>
                            <div class="upload_error text-red-500"></div>
                            <input type="file" id="hiddenProfileUpload" name="profile_image" accept=".jpg,.jpeg,.png" style="display:none">
                        </div>
                    </div>
                </div>
                @if ( old('coverImage') || old('images'))
                <div id="image_listing" class="p-[16px] gap-[16px] bg-[#F8F8F8] rounded-[8px] mt-4">
                </div>
                @endif
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label for="firstname">ชื่อจริง <span class="req">*</span></label>
                    <input required type="text" name="firstname" id="firstname" maxlength="10" placeholder="ชื่อจริง"
                        class="border rounded-lg px-3 py-2" value="{{ old('firstname', $nursing->firstname ?? '') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label for="lastname">นามสกุล <span class="req">*</span></label>
                    <input type="text" name="lastname" id="lastname" maxlength="10" placeholder="นามสกุล"
                        class="border rounded-lg px-3 py-2" value="{{ old('lastname', $nursing->lastname ?? '') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label for="nickname">ชื่อเล่น <span class="req">*</span></label>
                    <input required type="text" name="nickname" id="nickname" placeholder="ชื่อเล่น"
                        class="border rounded-lg px-3 py-2" value="{{ old('nickname', $nursing->profile->nickname ?? '') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label for="gender">เพศ <span class="req">*</span></label>
                    <select name="gender" id="gender" class="border rounded-lg px-3 py-2">
                        <option value="MALE" @selected(old('gender', $nursing->profile->gender ?? '') == 'MALE')>ชาย</option>
                        <option value="FEMALE" @selected(old('gender', $nursing->profile->gender ?? '') == 'FEMALE')>หญิง</option>
                    </select>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label for="phone">เบอร์โทรศัพท์ <span class="req">*</span></label>
                    <input required type="text" name="phone" id="phone" maxlength="10" placeholder="เบอร์โทรศัพท์"
                        class="border rounded-lg px-3 py-2" value="{{ old('phone', $nursing->phone ?? '') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label for="email">อีเมล์ <span class="req">*</span></label>
                    <input type="email" name="email" id="email" placeholder="อีเมล์"
                        class="border rounded-lg px-3 py-2" value="{{ old('email', $nursing->email ?? '') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label for="date_of_birth">วัน/เดือน/ปีเกิด <span class="req">*</span></label>
                    <input required type="text" name="date_of_birth" id="date_of_birth" placeholder="วว/ดด/ปป"
                        class="border rounded-lg px-3 py-2" value="{{ old('date_of_birth', $nursing->profile->date_of_birth ?? '') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-[8px]">
                    <label for="blood">กรุ๊ปเลือด(ถ้ามี) </label>
                    <input type="text" name="blood" id="blood" placeholder="กรุ๊ปเลือด"
                        class="border rounded-lg px-3 py-2" value="{{ old('blood', $nursing->profile->blood ?? '') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <div class="flex flex-row justify-between bg-[#F7FCF9] px-[12px] py-[8px] rounded-md">
                <div class="flex flex-row gap-[8px] items-center">
                    <img src="{{ asset('img/tabler-icon-map-2.svg') }}">
                    <span class="text-md text-[#286F51] font-medium">
                        ที่อยู่ปัจจุบัน
                    </span>
                </div>
            </div>

            <div class="flex flex-col gap-[8px]">
                <label for="address">ที่อยู่ <span class="req">*</span></label>
                <textarea id="address" name="address" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="ระบุที่อยู่">{{ old('address', $nursing->profile->address ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                <div class="flex flex-col gap-[8px]">
                    <label for="weight">จังหวัด <span class="req">*</span></label>
                    <select id="province" name="province_id" class="border rounded-lg px-3 py-2" onchange="handleSelectProvince()">
                        @if(isset($nursing->profile) && isset($nursing->profile->province) && isset($nursing->profile->province->id))
                            <option value="{{ $nursing->profile->province->id }}" selected>{{ $nursing->profile->province->name }}</option>
                        @endif
                    <select>
                </div>
                <div class="flex flex-col gap-[8px]">
                    <label for="height">อำเภอ/เขต <span class="req">*</span></label>
                    <select id="district" name="district_id" class="border rounded-lg px-3 py-2" onchange="handleSelectDistrict()">
                        @if(isset($nursing->profile) && isset($nursing->profile->district) && isset($nursing->profile->district->id))
                            <option value="{{  $nursing->profile->district->id }}" selected>{{ $nursing->profile->district->name }}</option>
                        @endif
                    <select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                <div class="flex flex-col gap-[8px]">
                    <label for="weight">ตำบล/แขวง <span class="req">*</span></label>
                    <select id="sub_district" name="sub_district_id" class="border rounded-lg px-3 py-2" onchange="handleSelectSubDistrict()">
                        @if(isset($nursing->profile) && isset($nursing->profile->subDistrict) && isset($nursing->profile->subDistrict->id))
                            <option value="{{  $nursing->profile->subDistrict->id }}" selected>{{ $nursing->profile->subDistrict->name }}</option>
                        @endif
                    <select>
                </div>
                <div class="flex flex-col gap-[8px]">
                    <label for="zipcode">รหัสไปรษณีย์ <span class="req">*</span></label>
                    <input required type="text" name="zipcode" id="zipcode" placeholder="รหัสไปรษณีย์"
                        class="border rounded-lg px-3 py-2" value="{{ old('zipcode', $nursing->profile->zipcode ?? '') }}"/>
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