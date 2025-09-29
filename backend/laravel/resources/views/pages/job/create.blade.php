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
                            <option value="NURSING" {{ old('service_type') == 'NURSING' ? 'selected' : '' }}>พยาบาล</option>
                            <option value="NURSING_HOME" {{ old('service_type') == 'NURSING_HOME' ? 'selected' : '' }}>ศูนย์ดูแล</option>
                        </select>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="care_type" class="font-medium">ประเภทคนดูแล <span class="req">*</span></label>
                        <select class="border rounded-lg px-3 py-2" name="care_type" id="care_type" required>
                            <option class="disabled selected hidden">เช่น พยาบาลวิชาชีพ คนดูแล อื่นๆ</option>
                            <option value="RN" {{ old('care_type') == 'RN' ? 'selected' : '' }}>พยาบาลวิชาชีพ (RN)</option>
                            <option value="PN" {{ old('care_type') == 'PN' ? 'selected' : '' }}>ผู้ช่วยพยาบาล (PN)</option>
                            <option value="NA" {{ old('care_type') == 'NA' ? 'selected' : '' }}>พนักงานผู้ช่วยการพยาบาล (NA)</option>
                            <option value="CG" {{ old('care_type') == 'CG' ? 'selected' : '' }}>คนดูแล (CG)</option>
                            <option value="MAIN" {{ old('care_type') == 'MAIN' ? 'selected' : '' }}>แม่บ้าน (ดูแล ทำงานบ้านได้ด้วย)</option>
                            <option value="ETC" {{ old('care_type') == 'ETC' ? 'selected' : '' }}>อื่นๆ</option>
                        </select>
                    </div>
                    
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px]">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="hire_type" class="font-medium">ระยะเวลาจ้าง <span class="req">*</span></label>
                        <select class="border rounded-lg px-3 py-2" name="hire_type" id="hire_type" required>
                            <option class="disabled selected hidden">เช่น รายวัน/สัปดาห์/เดือน/ปี</option>
                            <option value="DAILY" {{ old('hire_type') == 'DAILY' ? 'selected' : '' }}>รายวัน</option>
                            <option value="MONTHLY" {{ old('hire_type') == 'MONTHLY' ? 'selected' : '' }}>รายเดือน</option>
                        </select>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="hire_rule" class="font-medium">ลักษณะการจ้าง <span class="req">*</span></label>
                        <select class="border rounded-lg px-3 py-2" name="hire_rule" id="hire_rule" required>
                            <option class="disabled selected hidden">เช่น อยู่ประจำ ค้างคืน ชั่วคราว ไปกลับ</option>
                            <option value="FULL_STAY" {{ old('hire_url') == 'FULL_STAY' ? 'selected' : '' }}>อยู่ประจำ ค้างคืน</option>
                            <option value="FULL_ROUND" {{ old('hire_url') == 'FULL_ROUND' ? 'selected' : '' }}>อยู่ประจำ ไปกลับ</option>
                            <option value="PART_STAY" {{ old('hire_url') == 'PART_STAY' ? 'selected' : '' }}>ชั่วคราว ค้างคืน</option>
                            <option value="PART_ROUND" {{ old('hire_url') == 'PART_ROUND' ? 'selected' : '' }}>ชั่วคราว ไปกลับ</option>
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
                    <svg fill="#ffffff" height="24px" width="24px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M497.348,34.305L338.059,11.549c-1.224-0.174-3.297-0.22-4.828,0L176.356,33.96L19.481,11.549 C9.175,10.079,0,18.094,0,28.444V460.8c0,8.494,6.245,15.693,14.652,16.895l159.289,22.756c1.456,0.207,3.091,0.248,4.828,0 l156.876-22.411l156.875,22.411c10.304,1.471,19.481-6.54,19.481-16.895V51.2C512,42.706,505.755,35.505,497.348,34.305z M159.289,463.876L34.133,445.998V48.122l125.156,17.879V463.876z M318.578,445.997l-125.156,17.879V66.001l125.156-17.879 V445.997z M477.867,463.876l-125.156-17.879V48.122l125.156,17.879V463.876z"></path> </g> </g> <g> <g> <path d="M415.289,301.511c-25.095,0-45.511,20.416-45.511,45.511c0,19.061,11.784,35.412,28.444,42.179v21.259 c0,9.425,7.641,17.067,17.067,17.067s17.067-7.641,17.067-17.067v-21.259c16.66-6.766,28.444-23.117,28.444-42.179 C460.8,321.927,440.384,301.511,415.289,301.511z M415.289,358.4c-6.274,0-11.378-5.104-11.378-11.378 c0-6.274,5.104-11.378,11.378-11.378c6.274,0,11.378,5.104,11.378,11.378C426.667,353.296,421.563,358.4,415.289,358.4z"></path> </g> </g> <g> <g> <path d="M96.711,75.39c-25.095,0-45.511,20.416-45.511,45.511c0,19.061,11.784,35.412,28.444,42.179v21.259 c0,9.425,7.641,17.067,17.067,17.067s17.067-7.641,17.067-17.067V163.08c16.66-6.766,28.444-23.117,28.444-42.179 C142.222,95.805,121.806,75.39,96.711,75.39z M96.711,132.279c-6.274,0-11.378-5.104-11.378-11.378 c0-6.274,5.104-11.378,11.378-11.378c6.274,0,11.378,5.104,11.378,11.378C108.089,127.175,102.985,132.279,96.711,132.279z"></path> </g> </g> </g></svg>
                    <span class="text-md text-white font-semibold">สถานที่ทำงาน</span>
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
                    <svg class="w-6 h-6 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <span class="text-md text-white font-semibold">ข้อมูลติดต่อ</span>
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