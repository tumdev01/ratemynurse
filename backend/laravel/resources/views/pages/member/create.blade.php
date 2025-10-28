@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">


        <form id="registerNurse" method="post" action="{{ route('nursing-home.store') }}" class="flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="coords" value="">
            <input type="hidden" id="provinceTxt" value="">
            <input type="hidden" id="districtTxt" value="">
            <input type="hidden" id="subDistrictTxt" value="">
            @if(session('error'))
                <div class="flex flex-col justify-start bg-red-500 p-[16px] rounded-md text-white">
                    <span>
                        {{ session('error') }}
                    </span>
                 </div>
            @endif
            <div class="flex flex-col justify-start bg-[#F0F9F4] p-[16px] rounded-md">
                <span class="htitle text-[16px] md:text-lg text-[#286F51]">สร้างสมาชิกผู้ใช้บริการ</span>
                <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน เพื่อสร้างบัญชี</span>
            </div>
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div id="frm">
                <!-- Personal Info -->
                <div id="frm_personal" class="flex flex-col gap-[32px]">
                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">ข้อมูลส่วนตัว</span>
                    </span>

                    <!-- Personal Info Fname, Lname -->
                    <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label for="main_phone">ชื่อจริง <span class="req">*</span></label>
                            <input required type="text" name="main_phone" id="main_phone" maxlength="10" placeholder="เบอร์โทรศัพท์หลัก"
                                class="border rounded-lg px-3 py-2" value="{{ old('main_phone') }}"/>
                            <label class="error text-xs text-red-600"></label>
                        </div>
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label for="res_phone">นามสกุล</label>
                            <input type="text" name="res_phone" id="res_phone" maxlength="10" placeholder="เบอร์โทรศัพท์สำรอง"
                                class="border rounded-lg px-3 py-2" value="{{ old('res_phone') }}"/>
                            <label class="error text-xs text-red-600"></label>
                        </div>
                    </div>
                    <!-- About -->
                    <div class="flex flex-col">
                        <label for="about">เกี่ยวกับฉัน <span class="req">*</span></label>
                        <textarea id="about" name="about" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="แนะนำตัวสั้นๆ เพื่อให้ผู้ให้บริการรู้จักคุณมากขึ้น">{{ old('about') ?? '' }}</textarea>
                    </div>

                    <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label for="email">อีเมล <span class="req">*</span></label>
                            <input required type="text" name="email" id="email" placeholder="example@gmail.com"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('email') }}"/>
                            <label class="error text-xs text-red-600"></label>
                        </div>
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label for="phone">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" id="phone" placeholder="เบอร์โทรศัพท์"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('phone') ?? '' }}"/>
                        </div>
                    </div>
                    <!-- Gender - Birthdate -->
                    <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label for="email">เพศ <span class="req">*</span></label>
                            <input required type="text" name="email" id="email" placeholder="example@gmail.com"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('email') }}"/>
                            <label class="error text-xs text-red-600"></label>
                        </div>
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label for="date_of_birth">วัน/เดือน/ปีเกิด</label>
                            <input type="text" name="date_of_birth" id="date_of_birth" placeholder="วัน/เดือน/ปีเกิด"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('date_of_birth') ?? '' }}"/>
                        </div>
                    </div>

                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">ที่อยู่ปัจจุบัน</span>
                    </span>

                    <div class="flex flex-col">
                        <label for="address">ที่อยู่ <span class="req">*</span></label>
                        <textarea id="address" name="address" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="ระบุที่อยู่">{{ old('address') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                        <div class="flex flex-col">
                            <label for="weight">จังหวัด <span class="req">*</span></label>
                            <select id="province" name="province_id" class="border rounded-lg px-3 py-2" onchange="handleSelectProvince()" required>
                                @if(!empty(old('province_id')))
                                    <option value="{{ old('province_id') }}" selected></option>
                                @endif
                            <select>
                        </div>
                        <div class="flex flex-col">
                            <label for="height">อำเภอ/เขต <span class="req">*</span></label>
                            <select id="district" name="district_id" class="border rounded-lg px-3 py-2" onchange="handleSelectDistrict()" required>
                                @if(!empty(old('district_id')))
                                    <option value="{{ old('district_id') }}" selected></option>
                                @endif
                            <select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                        <div class="flex flex-col">
                            <label for="weight">ตำบล/แขวง <span class="req">*</span></label>
                            <select id="sub_district" name="sub_district_id" class="border rounded-lg px-3 py-2" onchange="handleSelectSubDistrict()" required>
                                @if(!empty(old('sub_district_id')))
                                    <option value="{{ old('sub_district_id') }}" selected></option>
                                @endif
                            <select>
                        </div>
                        <div class="flex flex-col">
                            <label for="zipcode">รหัสไปรษณีย์ <span class="req">*</span></label>
                            <input required type="text" name="zipcode" id="zipcode" placeholder="รหัสไปรษณีย์"
                                class="border rounded-lg px-3 py-2" onchange="handleSelectZipcode()" value="{{ old('zipcode') }}"/>
                        </div>
                    </div>

                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">ผู้ติดต่อฉุกเฉิน</span>
                    </span>
                    <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                        <div class="flex flex-col">
                            <label for="email">ชื่อผู้ติดต่อ </label>
                            <input required type="text" name="license_no" id="license_no" placeholder="เลขที่ใบอนุญาต"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('license_no') }}"/>
                        </div>
                        <div class="flex flex-col">
                            <label for="license_start_date">เบอร์โทรศัพท์ <span class="req">*</span></label>
                            <input required type="date" name="license_start_date" id="license_start_date" placeholder="วว/ดด/ปปปป"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('license_start_date') }}"/>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <label for="address">ความสัมพันธ์ <span class="req">*</span></label>
                        <input required type="text" name="license_start_date" id="license_start_date" placeholder="เช่น มารดา, บิดา, คู่สมรส"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('license_start_date') }}"/>
                    </div>
                    
                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">บริการที่ต้องการ</span>
                    </span>

                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            เลือกประเภทบริการที่คุณต้องการ (สามารถเลือกได้มากกว่า 1 รายการ)
                        </div>

                        <div class="grid grid-cols-1 gap-[15px]">
                            @php
                                $home_service_type_checked = old('home_service_type') ?? []; // array ของค่าที่ติ๊กตอน submit
                            @endphp
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="DAYCARE"
                                    {{ in_array('DAYCARE', $home_service_type_checked) ? 'checked' : '' }}>
                                พยาบาลดูแลตามบ้าน
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="RESIDENTIAL_CARE"
                                    {{ in_array('RESIDENTIAL_CARE', $home_service_type_checked) ? 'checked' : '' }}>
                                ศูนย์ดูแลผู้สูงอายุ
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="RESPITE_CARE"
                                    {{ in_array('RESPITE_CARE', $home_service_type_checked) ? 'checked' : '' }}>
                                บริการนวดและกายภาพบำบัด
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="SPECIAL_CARE"
                                    {{ in_array('SPECIAL_CARE', $home_service_type_checked) ? 'checked' : '' }}>
                                บริการส่งอาหารสำหรับผู้สูงอายุ
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="REHABILITATION"
                                    {{ in_array('REHABILITATION', $home_service_type_checked) ? 'checked' : '' }}>
                                บริการรับ-ส่งไปหาหมอ
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="REHABILITATION_PALLIATIVE_CARE"
                                    {{ in_array('REHABILITATION_PALLIATIVE_CARE', $home_service_type_checked) ? 'checked' : '' }}>
                                บริการดูแลเด็ก
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="DEMENTIA_PATIENTS"
                                    {{ in_array('DEMENTIA_PATIENTS', $home_service_type_checked) ? 'checked' : '' }}>
                                บริการทำความสะอาดบ้าน
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] justify-center">
                        <button type="submit" class="w-[200px] h-[48px] rounded-lg bg-[#286F51] text-white">บันทึก</button>
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
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('flatpickr/monthSelect/index.js') }}"></script>
    <script src="{{ asset('flatpickr/th.js') }}"></script>

    <script>
        const mainPhone = document.getElementById('main_phone');
        const resPhone = document.getElementById('res_phone');
    
        mainPhone.addEventListener('input', function () {
            let typingTimer;
            const doneTypingInterval = 500; // 0.5 วินาที
            clearTimeout(typingTimer); // reset timer ทุกครั้งที่พิมพ์
            typingTimer = setTimeout(() => {
                validatePhone(this);
            }, doneTypingInterval);
        });

        resPhone.addEventListener('input', function () {
            let typingTimer;
            const doneTypingInterval = 500; // 0.5 วินาที
            clearTimeout(typingTimer); // reset timer ทุกครั้งที่พิมพ์
            typingTimer = setTimeout(() => {
                validatePhone(this);
            }, doneTypingInterval);
        });

        resPhone.addEventListener('input', function() {
            // กรองให้เหลือเฉพาะตัวเลข
            this.value = this.value.replace(/[^0-9]/g, '');

            // ตัดเกิน 10 หลักออกอัตโนมัติ
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }

            // ตรวจสอบ pattern: ต้องขึ้นต้นด้วย 0 และมี 10 หลัก
            const thaiPhonePattern = /^0[0-9]{9}$/;

            // if (thaiPhonePattern.test(this.value)) {
            //     loginBtn.removeAttribute('disabled'); // เปิดปุ่ม
            // } else {
            //     loginBtn.setAttribute('disabled', true); // ปิดปุ่ม
            // }
        });

        ajaxCallDropdownOption('#province', '/api/provinces_list', 'กรุณาเลือกจังหวัด');

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

        let provinceTxt = '', districtTxt = '', subDistrictTxt = '', zipcodeTxt = '';
        function handleSelectProvince() {
            let province = $('#province option:selected');
            provinceTxt = province.text() ?? '';
            ajaxCallDropdownOption('#district', '/api/districts_list/' + $('#province').val() , 'เลือกอำเภอ/เขต');
            //getGoogleMap();
        }
        function handleSelectDistrict() {
            let district = $('#district');
            districtTxt = $('#district option:selected').text() ?? '';
            ajaxCallDropdownOption('#sub_district', '/api/sub_districts_list/' + $('#district').val(), 'เลือกตำบล/แขวง');
            //getGoogleMap();
        }

        function handleSelectSubDistrict() {
            let subDistrict = $('#sub_district');
            subDistrictTxt = $('#sub_district option:selected').text() ?? '';
            //getGoogleMap();
        }

        function handleSelectZipcode() {
            let zipcode = $('#zipcode');
            zipcodeTxt = zipcode.val();
            //getGoogleMap();
        }

        function hanleSelectAddress() {
            let address = $('#address');
            addressTxt  = address.val();
            //getGoogleMap();
        }

        async function getGoogleMap() {
            if (!addressTxt || !subDistrictTxt || !districtTxt || !provinceTxt || !zipcodeTxt) {
                console.log("ไม่สามารถ Geocode: ข้อมูลยังไม่ครบ");
                return;
            }

            let locationStr = `${addressTxt} ${subDistrictTxt} ${districtTxt} ${provinceTxt} ${zipcodeTxt}`;

            try {
                const coords = await geocodeAddress(locationStr);
                showMap(coords);
            } catch (err) {
                console.log("Geocode failed:", err);
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


    </script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_KEY') }}&libraries=places"></script>
    <script>
        function geocodeAddress(address) {
            const geocoder = new google.maps.Geocoder();
            return new Promise((resolve, reject) => {
                geocoder.geocode({ address: address }, function(results, status) {
                    if (status === "OK") {
                        const location = results[0].geometry.location;
                        resolve({ lat: location.lat(), lng: location.lng() });
                    } else {
                        reject(status);
                    }
                });
            });
        }

        async function showMap(coords) {
            // สร้าง map
            const map = new google.maps.Map(document.getElementById("map_show"), {
                zoom: 16,
                center: coords
            });

            // ใส่ marker
            new google.maps.Marker({
                position: coords,
                map: map,
                title: "ตำแหน่ง"
            });
        }
    </script>

    <script>
        $(function () {
            flatpickr('#license_start_date, #license_exp_date', {
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

            let provinceTxt = $('#provinceTxt').val() ?? '';
            $('#province:selected').html(provinceTxt);

            let districtTxt = $('#districtTxt').val() ?? '';
            $('#district:selected').html(districtTxt);

            let subDistrictTxt = $('#subDistrictTxt').val() ?? '';
            $('#sub_district:selected').html(subDistrictTxt);
        })
    </script>    

    <script>
        function renderMap() {
            const mapShow = document.getElementById('map_show');
            const iframeValue = document.getElementById('map_embed').value.trim();

            mapShow.innerHTML = ''; // ล้างก่อน

            if (iframeValue) {
                // ถ้ามี iframe ให้ใส่ตรง ๆ
                mapShow.innerHTML = iframeValue;
            }
        }

        renderMap();

        document.getElementById('map_embed').addEventListener('input', renderMap);
    </script>

    <script>
        document.getElementById('avatar').addEventListener('click', () => {
            document.getElementById('hiddenProfileUpload').click();
        });

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