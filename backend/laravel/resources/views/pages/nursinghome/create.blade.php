@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">


        <form id="registerNurse" method="post" action="{{ route('nursing-home.store') }}" class="flex flex-col gap-[32px] w-full max-w-[870px] mx-auto">
            @csrf
            <input type="hidden" name="coords" value="">
            <div class="flex flex-col justify-start bg-[#F0F9F4] p-[16px] rounded-md">
                <span class="htitle text-[16px] md:text-lg text-[#286F51]">สร้างสมาชิกผู้ให้บริการ บ้านพักดูแลผู้สูงอายุ</span>
                <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน เพื่อสร้างบัญชี</span>
            </div>
            <!-- <div class="flex flex-column items-center">
                <div id="avatar" class="mb-[24px]">
                    <img src="https://i0.wp.com/ratemynurse.org/wp-content/uploads/2025/08/UserAvatar.webp?fit=102%2C102&amp;ssl=1" class="ct-image" srcset="" sizes="(max-width: 102px) 100vw, 102px">
                </div>
                <span class="avar_h text-green-30 text-lg font-semibold mb-[12px]">รูปโปรไฟล์ของคุณ</span>
                <span>ไฟล์ .jpg, .png, .gif ขนาดไม่เกิน 5MB</span>
                <input type="file" id="hiddenProfileUpload" name="user_profile" style="display:none" />
            </div> -->
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
                        <span class="text-md text-white font-semibold">ข้อมูลทั่วไปของศูนย์</span>
                    </span>
                    
                    <div class="flex flex-col">
                        <label for="name">ชื่อศูนย์ดูแลผู้สูงอายุ <span class="req">*</span></label>
                        <input required type="text" name="name" id="name" placeholder="ระบุชื่อศูนย์ฯ"
                                class="border rounded-lg px-3 py-2" value="{{ old('name') }}"/>
                    </div>

                    <div class="flex flex-col">
                        <label for="description">คำอธิบายศูนย์ <span class="req">*</span></label>
                        <textarea required id="description" name="description" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="อธิบายเกี่ยวกับศูนย์ของคุณ เพื่อให้ผู้ใช้้บริการรู้จักคุณมากขึ้น">{{ old('description') }}</textarea>
                    </div>

                    <!-- Telephone Number -->
                    <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label for="main_phone">เบอร์โทรศัพท์หลัก <span class="req">*</span></label>
                            <input required type="text" name="main_phone" id="main_phone" maxlength="10" placeholder="เบอร์โทรศัพท์หลัก"
                                class="border rounded-lg px-3 py-2" value="{{ old('main_phone') }}"/>
                            <label class="error text-xs text-red-600"></label>
                        </div>
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label for="res_phone">เบอร์โทรศัพท์สำรอง</label>
                            <input type="text" name="res_phone" id="res_phone" maxlength="10" placeholder="เบอร์โทรศัพท์สำรอง"
                                class="border rounded-lg px-3 py-2" value="{{ old('res_phone') }}"/>
                            <label class="error text-xs text-red-600"></label>
                        </div>
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
                            <label for="facebook">Facebook Page (ถ้ามี)</label>
                            <input type="text" name="facebook" id="facebook" placeholder="ชื่อ Facebook Page (ถ้ามี)"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('facebook') }}"/>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <label for="youtube_url">Youtube (ถ้ามี)</label>
                        <input type="text" name="youtube_url" id="youtube_url" placeholder="https:/www/eample.com (ถ้ามี)"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('youtube_url') }}"/>
                    </div>

                    <div class="flex flex-col">
                        <label for="website">เว็บไซต์ (ถ้ามี)</label>
                        <input type="text" name="website" id="website" placeholder="https:/www/eample.com (ถ้ามี)"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('website') }}"/>
                    </div>

                    <div class="flex flex-col">
                        <label for="certified">ได้รับการรับรอง</label>
                        <div class="toggle-switch">
                            <input class="toggle-input" id="certified-toggle" name ="certified" type="checkbox" {{ (old('certified') ? 'checked' : '') }}>
                            <label class="toggle-label" for="certified-toggle"></label>
                        </div>

                    </div>

                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">ที่อยู่ศูนย์</span>
                    </span>

                    <div class="flex flex-col">
                        <label for="address">ที่อยู่ <span class="req">*</span></label>
                        <textarea id="address" name="address" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="ระบุที่อยู่" onchange="hanleSelectAddress()">{{ old('address') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                        <div class="flex flex-col">
                            <label for="weight">จังหวัด <span class="req">*</span></label>
                            <select id="province" name="province_id" class="border rounded-lg px-3 py-2" onchange="handleSelectProvince()">
                                @if(!empty(old('province_id')))
                                    <option value="{{ old('province_id') }}" selected></option>
                                @endif
                            <select>
                        </div>
                        <div class="flex flex-col">
                            <label for="height">อำเภอ/เขต <span class="req">*</span></label>
                            <select id="district" name="district_id" class="border rounded-lg px-3 py-2" onchange="handleSelectDistrict()">
                                @if(!empty(old('district_id')))
                                    <option value="{{ old('district_id') }}" selected></option>
                                @endif
                            <select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                        <div class="flex flex-col">
                            <label for="weight">ตำบล/แขวง <span class="req">*</span></label>
                            <select id="sub_district" name="sub_district_id" class="border rounded-lg px-3 py-2" onchange="handleSelectSubDistrict()">
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

                    <div class="flex flex-col">
                        <label for="map">แผนที่</label>
                        <input required type="text" name="map" id="map"
                            class="border rounded-lg px-3 py-2" value="{{ old('map') }}"/>
                    </div>

                    <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                        <div class="flex flex-col">
                            <label for="map">ฝังแผนที่ (iframe)</label>
                            <input required type="text" name="map_embed" id="map_embed"
                                class="border rounded-lg px-3 py-2" value="{{ old('map_embed') }}"/>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <div id="map_show"></div>
                    </div>

                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">ข้อมูลใบอนุญาตและการรับรอง</span>
                    </span>
                    <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                        <div class="flex flex-col">
                            <label for="email">เลขที่ใบอนุญาตประกอบกิจการ <span class="req">*</span></label>
                            <input required type="text" name="license_no" id="license_no" placeholder="เลขที่ใบอนุญาต"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('license_no') }}"/>
                        </div>
                        <div class="flex flex-col">
                            <label for="license_start_date">วันที่ออกใบอนุญาต <span class="req">*</span></label>
                            <input required type="date" name="license_start_date" id="license_start_date" placeholder="วว/ดด/ปปปป"
                                class="border rounded-lg px-3 py-2"
                                value="{{ old('license_start_date') }}"/>
                                

                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                        <div class="flex flex-col">
                            <label for="license_exp_date">วันที่หมดอายุ <span class="req">*</span></label>
                            <input required type="text" name="license_exp_date" id="license_exp_date" placeholder="วว/ดด/ปปปป"
                                    class="border rounded-lg px-3 py-2"
                                    value="{{ old('license_exp_date') }}"/>
                        </div>
                        <div class="flex flex-col">
                            <label for="address">หน่วยงานที่ออกใบอนุญาต <span class="req">*</span></label>
                            <input required type="text" name="license_by" id="license_by" placeholder="เช่น กรมการแพทย์ กระทรวงสาธารณสุข"
                                    class="border rounded-lg px-3 py-2"
                                    value="{{ old('license_by') }}"/>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">

                        <div class="flex flex-col">
                            <label for="address">มาตรฐานการรับรอง (ถ้ามี)</label>
                            <input type="text" name="certificates" id="certificates" placeholder="เช่น มาตรฐาน HA, JCI (ถ้ามี)"
                                    class="border rounded-lg px-3 py-2"
                                    value="{{ old('certificates') }}"/>
                        </div>
                        <div class="flex flex-col">
                            <label for="address">หมายเลขประจำสถานพยาบาล (ถ้ามี)</label>
                            <input type="text" name="hospital_no" id="hospital_no" placeholder="รหัสสถานพยาบาล (ถ้ามี)"
                                    class="border rounded-lg px-3 py-2"
                                    value="{{ old('hospital_no') }}"/>
                        </div>
                    </div>
                    
                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">ข้อมูลผู้รับผิดชอบ</span>
                    </span>

                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            ผู้อำนวยการ/ผู้จัดการ
                        </div>
                        <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                            <div class="flex flex-col">
                                <label for="address">ชื่อ-นามสกุล <span class="req">*</span></label>
                                <input required type="text" name="manager_name" id="manager_name" placeholder="ชื่อ-นามสกุล ผู้อำนวยการ"
                                        class="border rounded-lg px-3 py-2"
                                        value="{{ old('manager_name') }}"/>
                            </div>
                            <div class="flex flex-col">
                                <label for="address">คุณวุฒิการศึกษา <span class="req">*</span></label>
                                <input required type="text" name="graduated" id="graduated" placeholder="เช่น ปริญญาตรี พยาบาลศาสตร์"
                                        class="border rounded-lg px-3 py-2"
                                        value="{{ old('graduated') }}"/>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                            <div class="flex flex-col">
                                <label for="address">ใบอนุญาตประกอบวิชาชีพ</label>
                                <input type="text" name="graduated_paper" id="graduated_paper" placeholder="เลขที่ใบอนุญาต (ถ้ามี)"
                                        class="border rounded-lg px-3 py-2"
                                        value="{{ old('graduated_paper') }}"/>
                            </div>
                            <div class="flex flex-col">
                                <label for="address">ประสบการณ์ (ปี)</label>
                                <select name="exp_year" class="border rounded-lg px-3 py-2">
                                    <option>จำนวนปี</option>
                                    @for ($i = 0; $i <= 30; $i++)
                                        <option value="{{ $i }}" {{ old('exp_year') == $i ? 'selected' : '' }}>
                                            {{ $i }} ปี
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                            <div class="flex flex-col">
                                <label for="manager_phone">เบอร์โทรติดต่อ <span class="req">*</span></label>
                                <input required type="text" name="manager_phone" id="manager_phone" placeholder="เบอร์โทรติดต่อ"
                                        class="border rounded-lg px-3 py-2"
                                        value="{{ old('manager_phone') }}"/>
                            </div>
                            <div class="flex flex-col">
                                <label for="manager_email">อีเมลส่วนตัว <span class="req">*</span></label>
                                <input required type="text" name="manager_email" id="manager_email" placeholder="example@gmail.com"
                                        class="border rounded-lg px-3 py-2"
                                        value="{{ old('manager_email') }}"/>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            แพทย์ประจำ/ที่ปรึกษา
                        </div>

                        <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                            <div class="flex flex-col">
                                <label for="assist_name">ชื่อ-นามสกุล แพทย์	<span class="req">*</span></label>
                                <input required type="text" name="assist_name" id="assist_name" placeholder="ชื่อ-นามสกุล แพทย์ประจำ"
                                        class="border rounded-lg px-3 py-2"
                                        value="{{ old('assist_name') }}"/>
                            </div>
                            <div class="flex flex-col">
                                <label for="assist_no">เลขที่ใบอนุญาตแพทย์ <span class="req">*</span></label>
                                <input required type="text" name="assist_no" id="assist_no" placeholder="เลขที่ใบอนุญาต"
                                        class="border rounded-lg px-3 py-2"
                                        value="{{ old('assist_no') }}"/>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                            <div class="flex flex-col">
                                <label for="assist_expert">ความเชี่ยวชาญ <span class="req">*</span></label>
                                <input required type="text" name="assist_expert" id="assist_expert" placeholder="เช่น อายุรศาสตร์ เวชศาสตร์ผู้สูงอายุ"
                                        class="border rounded-lg px-3 py-2"
                                        value="{{ old('assist_expert') }}"/>
                            </div>
                            <div class="flex flex-col">
                                <label for="assist_phone">เบอร์โทรติดต่อ <span class="req">*</span></label>
                                <input required type="text" name="assist_phone" id="assist_phone" placeholder="เบอร์โทรติดต่อ"
                                        class="border rounded-lg px-3 py-2"
                                        value="{{ old('assist_phone') }}"/>
                            </div>
                        </div>
                    </div>

                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">บริการและการดูแล</span>
                    </span>

                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            ประเภทบริการ
                        </div>

                        <div class="grid grid-cols-2 gap-[32px]">
                            @php
                                $home_service_type_checked = old('home_service_type') ?? []; // array ของค่าที่ติ๊กตอน submit
                            @endphp
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="DAYCARE"
                                    {{ in_array('DAYCARE', $home_service_type_checked) ? 'checked' : '' }}>
                                การดูแลประจำวัน (Day Care)
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="RESIDENTIAL_CARE"
                                    {{ in_array('RESIDENTIAL_CARE', $home_service_type_checked) ? 'checked' : '' }}>
                                การดูแลแบบพักอาศัย (Residential Care)
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="RESPITE_CARE"
                                    {{ in_array('RESPITE_CARE', $home_service_type_checked) ? 'checked' : '' }}>
                                การดูแลระยะสั้น (Respite Care)
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="SPECIAL_CARE"
                                    {{ in_array('SPECIAL_CARE', $home_service_type_checked) ? 'checked' : '' }}>
                                การดูแลผู้ป่วยพิเศษ (Special Care)
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="REHABILITATION"
                                    {{ in_array('REHABILITATION', $home_service_type_checked) ? 'checked' : '' }}>
                                การบำบัดฟื้นฟู (Rehabilitation)
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="REHABILITATION_PALLIATIVE_CARE"
                                    {{ in_array('REHABILITATION_PALLIATIVE_CARE', $home_service_type_checked) ? 'checked' : '' }}>
                                การบำบัดฟื้นฟู (การดูแลประคับประคอง)
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="DEMENTIA_PATIENTS"
                                    {{ in_array('DEMENTIA_PATIENTS', $home_service_type_checked) ? 'checked' : '' }}>
                                การดูแลผู้ป่วยสมองเสื่อม
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="home_service_type[]" value="EMERGENCY_SERVICE"
                                    {{ in_array('EMERGENCY_SERVICE', $home_service_type_checked) ? 'checked' : '' }}>
                                บริการฉุกเฉิน 24 ชั่วโมง
                            </div>
                        </div>
                        <div class="w-full">
                            <label>บริการพิเศษอื่นๆ</label>
                            <input type="text" class="w-full border rounded-lg px-3 py-2" name="etc_service" value="{{ old('etc_service') }}">
                        </div>
                    </div>

                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            บริการเสริม
                        </div>
                        <div class="flex flex-row flex-wrap gap-[32px]">
                            @php
                                $additional_service_type_check = old('additional_service_type') ?? [];
                            @endphp
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="additional_service_type[]" value="FOOD"
                                    {{ in_array('FOOD', $additional_service_type_check) ? 'checked' : '' }}>
                                บริการอาหาร
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="additional_service_type[]" value="TRANSPORTATION"
                                    {{ in_array('TRANSPORTATION', $additional_service_type_check) ? 'checked' : '' }}>
                                บริการรับส่ง
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="additional_service_type[]" value="LAUNDRY"
                                    {{ in_array('LAUNDRY', $additional_service_type_check) ? 'checked' : '' }}>
                                ซักรีด
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="additional_service_type[]" value="RECREATIONAL_ACTIVITIES"
                                    {{ in_array('RECREATIONAL_ACTIVITIES', $additional_service_type_check) ? 'checked' : '' }}>
                                กิจกรรมนันทนาการ
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="additional_service_type[]" value="SOCIAL_WORK_ACTIVITIES"
                                    {{ in_array('SOCIAL_WORK_ACTIVITIES', $additional_service_type_check) ? 'checked' : '' }}>
                                กิจกรรมสังคมสงเคราะห์
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="additional_service_type[]" value="SPIRITUAL_ACTIVITIES"
                                    {{ in_array('SPIRITUAL_ACTIVITIES', $additional_service_type_check) ? 'checked' : '' }}>
                                กิจกรรมทางจิตวิญญาณ
                            </div>
                        </div>
                    </div>

                    {{-- สิ่งอำนวยความสะดวกและอุปกรณ์ --}}
                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">สิ่งอำนวยความสะดวกและอุปกรณ์</span>
                    </span>

                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            ข้อมูลอาคารและห้อง
                        </div>
                        <div class="grid grid-cols-2 gap-[32px]">
                            <div class="flex flex-col">
                                <label>จำนวนชั้นของอาคาร</label>
                                <input type="number" name="building_no" class="border rounded-lg px-3 py-2" placeholder="จำนวนชั้น" value="{{ old('building_no') }}"/>
                            </div>
                            <div class="flex flex-col">
                                <label>จำนวนห้องพักรวม</label>
                                <input type="number" name="total_room" class="border rounded-lg px-3 py-2" placeholder="จำนวนห้อง" value="{{ old('total_room') }}"/>
                                
                            </div>
                            <div class="flex flex-col">
                                <label>ห้องพักเดี่ยว</label>
                                <input type="number" name="private_room_no" class="border rounded-lg px-3 py-2" placeholder="จำนวนห้อง" value="{{ old('private_room_no') }}" />
                                
                            </div>
                            <div class="flex flex-col">
                                <label>ห้องพักคู่</label>
                                <input type="number" name="duo_room_no" class="border rounded-lg px-3 py-2" placeholder="จำนวนห้อง" value="{{ old('duo_room_no') }}"/>
                            </div>
                            <div class="flex flex-col">
                                <label>ห้องพักรวม (3+ เตียง)</label>
                                <input type="number" name="shared_room_three_beds" class="border rounded-lg px-3 py-2" placeholder="จำนวนห้อง" value="{{ old('shared_room_three_beds') }}"/>
                            </div>
                            <div class="flex flex-col">
                                <label>ความจุผู้ป่วยสูงสุด</label>
                                <input type="number" name="max_serve_no" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('max_serve_no') }}"/>
                            </div>
                        </div>
                        <div class="w-full flex flex-col">
                            <label>พื้นที่รวม (ตร.ม.)</label>
                            <input type="number" name="area" placeholder="ตารางเมตร" class="border rounded-lg px-3 py-2" value="{{ old('area') }}">
                        </div>
                    </div>

                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            ห้องพิเศษและสิ่งอำนวยความสะดวก
                        </div>
                        <div class="flex flex-row flex-wrap gap-[32px]">
                            @php
                                $special_facilities_check = old('special_facilities') ?? [];
                            @endphp
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="special_facilities[]" value="NURSE_STATION"
                                    {{ in_array('NURSE_STATION', $special_facilities_check) ? 'checked' : '' }}>
                                ห้องพยาบาล/สถานีพยาบาล
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="special_facilities[]" value="EMERGENCY_ROOM"
                                    {{ in_array('EMERGENCY_ROOM', $special_facilities_check) ? 'checked' : '' }}>
                                ห้องฉุกเฉิน
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="special_facilities[]" value="EXAMINATION_ROOM"
                                    {{ in_array('EXAMINATION_ROOM', $special_facilities_check) ? 'checked' : '' }}>
                                ห้องตรวจ
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="special_facilities[]" value="MEDICINE_ROOM"
                                    {{ in_array('MEDICINE_ROOM', $special_facilities_check) ? 'checked' : '' }}>
                                ห้องยา
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="special_facilities[]" value="KITCHEN_CAFETERIA"
                                    {{ in_array('KITCHEN_CAFETERIA', $special_facilities_check) ? 'checked' : '' }}>
                                ห้องครัว/โรงอาหาร
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="special_facilities[]" value="DINING_ROOM"
                                    {{ in_array('DINING_ROOM', $special_facilities_check) ? 'checked' : '' }}>
                                ห้องรับประทานอาหาร
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="special_facilities[]" value="ACTIVITY_ROOM"
                                    {{ in_array('ACTIVITY_ROOM', $special_facilities_check) ? 'checked' : '' }}>
                                ห้องกิจกรรม
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="special_facilities[]" value="PHYSICAL_THERAPY_ROOM"
                                    {{ in_array('PHYSICAL_THERAPY_ROOM', $special_facilities_check) ? 'checked' : '' }}>
                                ห้องกายภาพบำบัด
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="special_facilities[]" value="MEETING_ROOM"
                                    {{ in_array('MEETING_ROOM', $special_facilities_check) ? 'checked' : '' }}>
                                ห้องประชุม
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="special_facilities[]" value="OFFICE_ROOM"
                                    {{ in_array('OFFICE_ROOM', $special_facilities_check) ? 'checked' : '' }}>
                                ห้องออฟฟิศ
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="special_facilities[]" value="LAUNDRY_ROOM"
                                    {{ in_array('LAUNDRY_ROOM', $special_facilities_check) ? 'checked' : '' }}>
                                ห้องซักรีด
                            </div>
                        </div>
                    </div>

                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            สิ่งอำนวยความสะดวกทั่วไป
                        </div>
                        <div class="flex flex-row flex-wrap gap-[32px]">
                            @php
                                $facilities_check = old('facilities') ?? [];
                            @endphp
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="ELEVATOR"
                                    {{ in_array('ELEVATOR', $facilities_check) ? 'checked' : '' }}>
                                ลิฟต์
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="WHEELCHAIR_RAMP"
                                    {{ in_array('WHEELCHAIR_RAMP', $facilities_check) ? 'checked' : '' }}>
                                ทางลาดสำหรับรถเข็น
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="BATHROOM_GRAB_BAR"
                                    {{ in_array('BATHROOM_GRAB_BAR', $facilities_check) ? 'checked' : '' }}>
                                ราวจับในห้องน้ำ
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="EMERGENCY_BELL"
                                    {{ in_array('EMERGENCY_BELL', $facilities_check) ? 'checked' : '' }}>
                                กระดิ่งฉุกเฉิน
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="CAMERA"
                                    {{ in_array('CAMERA', $facilities_check) ? 'checked' : '' }}>
                                กล้องวงจรปิด
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="FIRE_SYSTEM"
                                    {{ in_array('FIRE_SYSTEM', $facilities_check) ? 'checked' : '' }}>
                                ระบบดับเพลิง
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="BACKUP_GENERATOR"
                                    {{ in_array('BACKUP_GENERATOR', $facilities_check) ? 'checked' : '' }}>
                                เครื่องปั่นไฟสำรอง
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="AIR_CONDITIONER"
                                    {{ in_array('AIR_CONDITIONER', $facilities_check) ? 'checked' : '' }}>
                                เครื่องปรับอากาศ
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="GARDEN_AREA"
                                    {{ in_array('GARDEN_AREA', $facilities_check) ? 'checked' : '' }}>
                                สวนหย่อม/พื้นที่นันทนาการ
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="PARKING"
                                    {{ in_array('PARKING', $facilities_check) ? 'checked' : '' }}>
                                ที่จอดรถ
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="WIFI_INTERNET"
                                    {{ in_array('WIFI_INTERNET', $facilities_check) ? 'checked' : '' }}>
                                WiFi / อินเทอร์เน็ต
                            </div>
                            <div class="flex flex-row gap-[8px] items-center">
                                <input type="checkbox" name="facilities[]" value="CENTRAL_TELEVISION"
                                    {{ in_array('CENTRAL_TELEVISION', $facilities_check) ? 'checked' : '' }}>
                                โทรทัศน์ส่วนกลาง
                            </div>
                        </div>
                    </div>

                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            ยานพาหนะและอุปกรณ์พิเศษ
                        </div>
                        <div class="flex flex-row gap-[24px] items-center">
                            <label>รถพยาบาล:</label>
                            <input type="radio" name="ambulance" value="1" {{ old('ambulance') == 1 ? 'checked' : '' }}>
                            <label>มี</label>
                            <input type="radio" name="ambulance" value="0" {{ old('ambulance') == 0 ? 'checked' : '' }}>
                            <label>ไม่มี</label>
                            <input type="number" name="ambulance_amount" 
                                class="border rounded-lg px-3 py-2" 
                                placeholder="จำนวนคัน"
                                value="{{ old('ambulance_amount') }}">
                        </div>
                        <div class="flex flex-row gap-[24px] items-center">
                            <label>รถตู้/รถรับส่ง :</label>
                            <input type="radio" name="van_shuttle" value="1" {{ old('van_shuttle') == 1 ? 'checked' : '' }}>
                            <label>มี</label>
                            <input type="radio" name="van_shuttle" value="0" {{ old('van_shuttle') == 0 ? 'checked' : '' }}>
                            <label>ไม่มี</label>
                        </div>
                        <div class="w-full">
                            <label>อุปกรณ์การแพทย์พิเศษ	</label>
                            <input type="text" name="special_medical_equipment" 
                                class="w-full border rounded-lg px-3 py-2" 
                                placeholder="เช่น เครื่องช่วยหายใจ, เครื่องวัดสัญญาณชีพ"
                                value="{{ old('special_medical_equipment') }}">
                        </div>
                    </div>

                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">ข้อมูลบุคลากร</span>
                    </span>

                    <div class="grid grid-cols-3 gap-[32px]">
                        <div class="flex flex-col">
                            <label for="address">จำนวนพยาบาลรวม	</label>
                            <input type="number" name="total_staff" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('total_staff') }}">
                        </div>
                        <div class="flex flex-col">
                            <label for="address">พยาบาลประจำการ</label>
                            <input type="number" name="total_fulltime_nurse" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('total_fulltime_nurse') }}">
                        </div>
                        <div class="flex flex-col">
                            <label for="address">พยาบาลพาร์ทไทม์</label>
                            <input type="number" name="total_parttime_nurse" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('total_parttime_nurse') }}">
                        </div>
                        <div class="flex flex-col">
                            <label for="address">ผู้ช่วยพยาบาล</label>
                            <input type="number" name="total_nursing_assistant" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('total_nursing_assistant') }}">
                        </div>
                        <div class="flex flex-col">
                            <label for="address">แพทย์ประจำ</label>
                            <input type="number" name="total_regular_doctor" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('total_regular_doctor') }}">
                        </div>
                        <div class="flex flex-col">
                            <label for="address">นักกายภาพบำบัด</label>
                            <input type="number" name="total_physical_therapist" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('total_physical_therapist') }}">
                        </div>
                        <div class="flex flex-col">
                            <label for="address">เภสัชกร</label>
                            <input type="number" name="total_ftotal_pharmacistulltime_nurse" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('total_pharmacist') }}">
                        </div>
                        <div class="flex flex-col">
                            <label for="address">นักโภชนาการ</label>
                            <input type="number" name="total_nutritionist" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('total_nutritionist') }}">
                        </div>
                        <div class="flex flex-col">
                            <label for="address">นักสังคมสงเคราะห์</label>
                            <input type="number" name="total_social_worker" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('total_social_worker') }}">
                        </div>
                        <div class="flex flex-col">
                            <label for="address">พนักงานทั่วไป</label>
                            <input type="number" name="total_general_employees" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('total_general_employees') }}">
                        </div>
                        <div class="flex flex-col">
                            <label for="address">รปภ./เจ้าหน้าที่รักษาความปลอดภัย</label>
                            <input type="number" name="total_security_officer" class="border rounded-lg px-3 py-2" placeholder="คน" value="{{ old('total_security_officer') }}">
                        </div>
                    </div>
                    
                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">ข้อมูลการเงินและค่าบริการ</span>
                    </span>

                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            ค่าบริการพื้นฐาน
                        </div>

                        <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                            <div class="flex flex-col">
                                <label for="address">ค่าบริการรายวัน (บาท)</label>
                                <input type="number" name="total_security_officer" class="border rounded-lg px-3 py-2" placeholder="฿ บาท/วัน" value="{{ old('cost_per_day') }}">
                            </div>
                            <div class="flex flex-col">
                                <label for="address">ค่าบริการรายเดือน (บาท)</label>
                                    <input type="number" 
                                    name="cost_per_month" 
                                    class="border rounded-lg px-3 py-2" 
                                    placeholder="฿ บาท/เดือน"
                                    value="{{ old('cost_per_month') }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                            <div class="flex flex-col">
                                <label for="address">ค่ามัดจำ (บาท)</label>
                                <input type="number" 
                                    name="deposit" 
                                    class="border rounded-lg px-3 py-2" 
                                    placeholder="฿ บาท"
                                    value="{{ old('deposit') }}">
                            </div>
                            <div class="flex flex-col">
                                <label for="address">ค่าลงทะเบียน (บาท)</label>
                                <input type="number" 
                                    name="registration_fee" 
                                    class="border rounded-lg px-3 py-2" 
                                    placeholder="฿ บาท"
                                    value="{{ old('registration_fee') }}">
                            </div>
                        </div>
                    </div>

                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            ค่าบริการเพิ่มเติม
                        </div>

                        <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                            <div class="flex flex-col">
                                <label for="address">ค่าอาหารพิเศษ (บาท/วัน)</label>
                                <input type="number" 
                                    name="special_food_expenses" 
                                    class="border rounded-lg px-3 py-2" 
                                    placeholder="฿ บาท/วัน"
                                    value="{{ old('special_food_expenses') }}">
                            </div>
                            <div class="flex flex-col">
                                <label for="address">ค่ากายภาพบำบัด (บาท/ครั้ง)</label>
                                <input type="number" 
                                    name="physical_therapy_fee" 
                                    class="border rounded-lg px-3 py-2" 
                                    placeholder="฿ บาท/ครั้ง"
                                    value="{{ old('physical_therapy_fee') }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                            <div class="flex flex-col">
                                <label for="address">ค่ารับส่ง (บาท/ครั้ง)</label>
                                <input type="number" 
                                    name="delivery_fee" 
                                    class="border rounded-lg px-3 py-2" 
                                    placeholder="฿ บาท/ครั้ง"
                                    value="{{ old('delivery_fee') }}">
                            </div>
                            <div class="flex flex-col">
                                <label for="address">ค่าบริการซักรีด (บาท/เดือน)</label>
                                <input type="number" 
                                    name="laundry_service" 
                                    class="border rounded-lg px-3 py-2" 
                                    placeholder="฿ บาท/เดือน"
                                    value="{{ old('laundry_service') }}">
                            </div>
                        </div>
                    </div>

                    <div class="p-[16px] gap-[16px] flex flex-col bg-[#F8F8F8] rounded-[8px]">
                        <div class="sub_topic flex flex-row gap-[8px] items-center">
                            การรับประกันและการเงิน
                        </div>
                        <div class="flex flex-row gap-[24px] items-center">
                            <label>การรับประกันสังคม:</label>
                            <input type="radio" name="social_security" value="1" {{ old('social_security') == 1 ? 'checked' :'' }}>
                            <label>มี</label>
                            <input type="radio" name="social_security" value="0" {{ old('social_security') == 0 ? 'checked' :'' }}>
                            <label>ไม่มี</label>
                        </div>
                        <div class="flex flex-row gap-[24px] items-center">
                            <label>การรับประกันสุขภาพเอกชน :</label>
                            <input type="radio" name="private_health_insurance" value="1" {{ old('private_health_insurance') == 1 ? 'checked' : '' }}>
                            <label>มี</label>
                            <input type="radio" name="private_health_insurance" value="0" {{ old('private_health_insurance') == 0 ? 'checked' : '' }}>
                            <label>ไม่มี</label>
                        </div>
                        <div class="flex flex-row gap-[24px] items-center">
                            <label>การผ่อนชำระ :</label>
                            <input type="radio" name="installment" value="1" {{ old('installment') == 1 ? 'checked' : '' }}>
                            <label>มี</label>
                            <input type="radio" name="installment" value="0" {{ old('installment') == 0 ? 'checked' : '' }}>
                            <label>ไม่มี</label>
                        </div>
                        <div class="w-full">
                            <label>วิธีการชำระเงิน</label>
                            <input required type="text" name="payment_methods" class="w-full border rounded-lg px-3 py-2" value="{{ old('payment_methods') }}">
                        </div>
                    </div>
                    
                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">ข้อมูลเพิ่มเติม</span>
                    </span>
                    <div class="flex flex-col">
                        <label class="mb-2" for="center_highlights">จุดเด่นของศูนย์</label>
                        <textarea id="center_highlights" name="center_highlights" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="เช่น การดูแลผู้ป่วยหัวใจ, การฉีดยา, การดูแลแผล ฯลฯ">{{ old('center_highlights') }}</textarea>
                    </div>
                    <div class="flex flex-col">
                        <label class="mb-2" for="patients_target">กลุ่มเป้าหมายผู้ป่วย</label>
                        <textarea id="patients_target" name="patients_target" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="เช่น ผู้สูงอายุทั่วไป ผู้ป่วยโรคเรื้อรัง ผู้ป่วยสมองเสื่อม">{{ old('patients_target') }}</textarea>
                    </div>
                    <div class="flex flex-col">
                        <label class="mb-2" for="visiting_time">ช่วงเวลาเยี่ยม</label>
                        <textarea id="visiting_time" name="visiting_time" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="เช่น วันจันทร์-อาทิตย์ 08:00-20:00">{{ old('visiting_time') }}</textarea>
                    </div>
                    <div class="flex flex-col">
                        <label class="mb-2" class="mb-2" for="patient_admission_policy">นโยบายการรับผู้ป่วย</label>
                        <textarea id="patient_admission_policy" name="patient_admission_policy" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="เกณฑ์การรับผู้ป่วย ข้อจำกัด หรือเงื่อนไขพิเศษ">{{ old('patient_admission_policy') }}</textarea>
                    </div>
                    <div class="flex flex-col">
                        <label class="mb-2" for="emergency_contact_information">ข้อมูลติดต่อฉุกเฉิน</label>
                        <textarea id="emergency_contact_information" name="emergency_contact_information" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="เบอร์โทรฉุกเฉิน 24 ชั่วโมง">{{ old('emergency_contact_information') }}</textarea>
                    </div>
                    <div class="flex flex-col">
                        <label class="mb-2" for="additional_notes">หมายเหตุเพิ่มเติม</label>
                        <textarea id="additional_notes" name="additional_notes" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="ข้อมูลเพิ่มเติมหรือรายละเอียดพิเศษ">{{ old('additional_notes') }}</textarea>
                    </div>
                    
                    <div class="flex flex-col">
                        <label class="mb-2" for="address">Cover Image / Gallery</label>
                        <div class="border border-dashed rounded-lg h-[130px] flex justify-center items-center">
                            <div id="certificate_upload" class="flex flex-row gap-[16px] justify-center">
                                <img src="https://ratemynurse.org/wp-content/uploads/2025/08/upload2.png" loading="lazy" width="70" height="67">
                                <div class="flex flex-col">
                                    <label class="text-sm font-semibold">คลิกเพื่ออัปโหลดไฟล์</label>
                                    <span class="text-xs">รองรับ .JPG, .PNG, .PDF | ขนาดไม่เกิน 5 MB</span>
                                </div>
                            </div>
                        </div>
                        @if ( old('coverImage') || old('images'))
                        <div id="image_listing" class="p-[16px] gap-[16px] bg-[#F8F8F8] rounded-[8px] mt-4">
                            {{-- <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th></th>
                                        <th class="text-center">cover</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tr>
                                    <td class="px-6 py-4"><img src="{{ $nursinghome->coverImage->full_path }}" width="90"></td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="toggle-switch">
                                            <input class="toggle-input" id="coverImg-toggle" name ="coverImg" type="checkbox" {{ ($nursinghome->coverImage->is_cover ? 'checked' : '') }}>
                                            <label class="toggle-label" for="coverImg-toggle"></label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 w-10">
                                        <a href="#" class="text-red-500 text-sm text-right"><svg class="w-4 h-4 text-red-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/> </svg></a>
                                    </td>
                                </tr>
                                @foreach ($nursinghome->images as $image)
                                    <tr>
                                        <td class="px-6 py-4"><img src="{{ $image->full_path }}" width="90"></td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="toggle-switch">
                                                <input class="toggle-input" data-id="{{ $image->id }}" id="image-{{$image->id}}-toggle" name ="image[]" type="checkbox" {{ ($image->is_cover ? 'checked' : '') }}>
                                                <label class="toggle-label" for="image-{{$image->id}}-toggle"></label>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 w-10">
                                            <a href="#" data-id="{{ $image->id }}" class="text-red-500 text-sm text-right"><svg class="w-4 h-4 text-red-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/> </svg></a>
                                        </td>
                                    </tr>
                                @endforeach

                            </table> --}}
                        </div>
                        @endif
                    </div>
                    
                    <span class="w-full min-h-[1px] divider clear-both"></span>

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
                            success({
                                results: response.data.data.map(function (item) {
                                    return {
                                        text: item.name,
                                        id: item.id
                                    };
                                })
                            });
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
            })
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

        // render ตอนโหลดหน้า
        renderMap();

        // render ตอน input change
        document.getElementById('map_embed').addEventListener('input', renderMap);
    </script>
@endsection