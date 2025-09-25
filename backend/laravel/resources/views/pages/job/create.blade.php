@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        
        <form id="jobCreate" method="post" action="{{ route('job.store') }}" class="flex flex-col gap-[32px] w-full max-w-[870px] mx-auto">
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
            @csrf
                @if(session('error'))
                    <div class="flex flex-col justify-start bg-red-500 p-[16px] rounded-md text-white">
                        <span>
                            {{ session('error') }}
                        </span>
                    </div>
                @endif
                <div class="flex flex-col justify-start bg-[#F0F9F4] p-[16px] rounded-md">
                    <span class="htitle text-[16px] md:text-lg text-[#286F51]">สร้างประกาศงานใหม่</span>
                    <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน ลงประกาศด้วยไอดี Admin ประกาศจะไม่ถูกระบุชื่อผู้ลงประกาศ</span>
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

                <div class="flex flex-col">
                    <label for="name" class="font-medium">ชื่อประกาศ <span class="req">*</span></label>
                    <input class="border rounded-lg px-3 py-2" type="text" name="name" id="name" placeholder="ฉันหา..." value="{{ old('name') }}" required>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px]">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="service_type" class="font-medium">ประเภทบริการ <span class="req">*</span></label>
                        <select class="border rounded-lg px-3 py-2" name="service_type" id="service_type" required>
                            <option class="disabled selected hidden">ประเภทบริการ</option>
                            <option value="NURSING">พยาบาล / คนดูแล</option>
                            <option value="NURSING_HOME">ศูนย์ดูแลผู้สูงอายุ</option>
                        </select>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="hire_type" class="font-medium">ลักษณะการจ้าง <span class="req">*</span></label>
                        <select class="border rounded-lg px-3 py-2" name="hire_type" id="hire_type" required>
                            <option class="disabled selected hidden">เช่น รายวัน/สัปดาห์/เดือน/ปี</option>
                            <option value="DAILY">รายวัน</option>
                            <option value="WEEKLY">รายสัปดาห์</option>
                            <option value="MONTHLY">รายเดือน</option>
                            <option value="YEARLY">รายปี</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="service_type" class="font-medium">งบประมาณ <span class="req">*</span></label>
                        <input class="border rounded-lg px-3 py-2" type="number" name="cost" placeholder="฿ งบประมาณ" value="{{ old('code') }}">
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="start_date">วันที่เริ่มงาน <span class="req">*</span></label>
                        <input required type="date" name="start_date" id="start_date" placeholder="วว/ดด/ปปปป"
                            class="border rounded-lg px-3 py-2"
                            value="{{ old('start_date') }}"/>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label for="description" class="font-medium">รายละเอียดงาน <span class="req">*</span></label>
                    <textarea id="description" name="description" class="min-h-[234px] border rounded-lg px-3 py-2" placeholder="อธิบายรายละเอียดงานที่คุณต้องการ เช่น 
                        1. ข้อมูลงาน:
                            - จุดประสงค์ของงาน: ดูแลผู้สูงอายุที่บ้าน, ดูแลผู้ป่วย, ดูแลหลังการผ่าตัด, ฯลฯ
                            - แผนการทำงาน: สามารถเลือกการทำงานเป็นกะหรือเต็มเวลา

                        2. รายละเอียดงาน:
                            - จำนวนชั่วโมงการทำงาน: กำหนดตามความต้องการของลูกค้า
                            - สถานที่ทำงาน: บ้านลูกค้าหรือที่อยู่อาศัยที่ระบุ
                            - เงื่อนไขการจ้างงาน: ช่วงเวลาที่สามารถทำงานได้, เงื่อนไขในการดูแลพิเศษ, ฯลฯ">{{ old('description') }}</textarea>
                </div>

                <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                    <img src="https://ratemynurse.org/wp-content/uploads/2025/08/Group.png" width="19" height="18" loading="lazy">
                    <span class="text-md text-[#286F51] font-semibold">สถานที่ทำงาน</span>
                </span>

                <div class="flex flex-col">
                    <label for="address" class="font-medium">ที่อยู่ <span class="req">*</span></label>
                    <textarea id="address" name="address" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="ระบุที่อยู่">{{ old('addreess') }}</textarea>
                </div>

                <div class="grid grid-cols-3 gap-[15px] md:gap-[32px]">
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
                        <div class="flex flex-col">
                            <label for="weight">ตำบล/แขวง <span class="req">*</span></label>
                            <select id="sub_district" name="sub_district_id" class="border rounded-lg px-3 py-2" onchange="handleSelectSubDistrict()" required>
                                @if(!empty(old('sub_district_id')))
                                    <option value="{{ old('sub_district_id') }}" selected></option>
                                @endif
                            <select>
                        </div>
                    </div>


                <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                    <img src="https://ratemynurse.org/wp-content/uploads/2025/09/contact-vector.png" width="19" height="18" loading="lazy">
                    <span class="text-md text-[#286F51] font-semibold">ข้อมูลติดต่อ</span>
                </span>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="phone" class="font-medium">เบอร์โทรศัพท์ <span class="req">*</span></label>
                        <input class="border rounded-lg px-3 py-2" type="text" name="phone" id="phone" placeholder="เบอร์โทรศัพท์" value="{{ old('phone') }}" required>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="email" class="font-medium">อีเมล์</label>
                        <input class="border rounded-lg px-3 py-2" id="email" name="email" placeholder="อีเมล (ไม่บังคับ)" type="email" value="{{ old('email') }}">
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="facebook" class="font-medium flex flex-row gap-[4px]"><img src="https://ratemynurse.org/wp-content/uploads/2025/09/facebook.webp" width="20" height="20"> Facebook</label>
                        <input class="border rounded-lg px-3 py-2" type="text" name="facebook" placeholder="Facebook (ไม่บังคับ)" value="{{ old('facebook') }}">
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="line_id" class="font-medium flex flex-row gap-[4px]"><img src="https://ratemynurse.org/wp-content/uploads/2025/09/line.webp" width="21" height="20"> Line ID (ไลน์ไอดี)</label>
                        <input class="border rounded-lg px-3 py-2" id="line_id" name="lineid" placeholder="Line ID (ไม่บังคับ)" type="text" value="{{ old('lineid') }}">
                    </div>
                </div>

                <div class="w-full bg-[#ECECED]" style="height:1px;"></div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] justify-center">
                    <button type="submit" class="w-[200px] h-[48px] rounded-lg bg-[#286F51] text-white" fdprocessedid="7yrfx4">บันทึก</button>
                </div>        
        </form>

    </div>
</div>
@endsection
@section('style')
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
</style>
@endsection
@section('javascript')
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('flatpickr/monthSelect/index.js') }}"></script>
    <script src="{{ asset('flatpickr/th.js') }}"></script>
    <script>
        ajaxCallDropdownOption('#province', '/api/provinces_list', 'กรุณาเลือกจังหวัด');
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
    <script>
        $(function () {
            flatpickr('#start_date', {
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
@endsection