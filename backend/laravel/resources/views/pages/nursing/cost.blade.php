@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    @include('pages.nursing.components.navigation')
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <!-- <form id="costNurse" class="text-[16px] flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" method="POST" action="{{ route('nursing.cost.update', $nursing->id) }}">
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

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            <input type="hidden" name="user_type" value="NURSING">

            <div class="flex flex-row justify-between bg-[#F7FCF9] px-[12px] py-[8px] rounded-md">
                <div class="flex flex-row gap-[8px] items-center">
                    <svg class="w-6 h-6 text-[#286F51]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <span class="text-md text-[#286F51] font-medium">
                        แพ็คเกจค่าบริการ
                    </span>
                </div>
            </div>

            <div class="flex flex-col">
                <label class="text-[#5A5A5A]" for="type">ชื่อแพ็คเกจ <span class="req">*</span></label>
                <select name="type" id="type" class="border rounded-lg px-3 py-2">
                    <option>เลือกประเภท</option>
                    <option value="DAILY">รายวัน</option>
                    <option value="MONTH">รายเดือน</option>
                </select>
            </div>
            
            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label class="text-[#5A5A5A]" for="hire_rule">ลักษณะการจ้างงาน</label>
                    <select name="hire_rule" id="hire_rule" class="border rounded-lg px-3 py-2">
                        <option>เลือกลักษณะการทำงาน</option>
                        <option value="FULL_ROUND">อยู่ประจำ ไป-กลับ</option>
                        <option value="FULL_STAY">อยู่ประจำ ค้างคืน</option>
                        <option value="PART_ROUND">ชั่วคราว ค้างคืน</option>
                        <option value="PART_STAY">ชั่วคราว ไปกลับ</option>
                    </select>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label class="text-[#5A5A5A]" for="cost">ค่าบริการ</label>
                    <input required type="number" name="cost" id="cost" placeholder="ค่าบริการ"
                        class="border rounded-lg px-3 py-2" value="{{ old('cost') }}"/>
                    <label class="error text-xs text-red-600"></label>
                </div>
            </div>

            <span class="w-full min-h-[1px] divider clear-both"></span>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] justify-end">
                <button type="submit" class="w-[200px] h-[48px] rounded-lg bg-[#286F51] text-white">บันทึกข้อมูล</button>
            </div>
        </form> -->
        @php
            $nursingCosts = $nursing->costs
            ->groupBy('type')
            ->map(function ($items) {
                return $items->keyBy('hire_rule');
            })
            ->toArray();
        @endphp

        <form id="costNurse" class="text-[16px] flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" method="POST" action="{{ route('nursing.cost.update', $nursing->id) }}">
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

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            <input type="hidden" name="user_type" value="NURSING">
               <div class="flex flex-col gap-[4px] px-[12px] py-[8px] bg-[#F7FCF9] rounded-[8px]">
                    <label class="text-[#286F51] font-semibold">ค่าบริการ</label>
                    <p class="text-[#5A5A5A] text-sm">กรุณาระบุอัตราค่าบริการของคุณตามลักษณะการทำงาน โดยสามารถกำหนดได้ทั้ง รายวัน และรายเดือนและรูปแบบลักษณะการจ้างที่คุณ
                        รับเพื่อให้ผู้ใช้บริการทราบช่วงราคาค่าจ้างที่ชัดเจนก่อนตัดสินใจ</p>
                </div>

                <div class="flex flex-col gap-[16px] p-[16px] bg-[#F7FCF9] rounded-[8px]">
                    <div class="flex flex-row gap-[12px]">
                        <label class="has-border text-[#286F51] font-semibold">รายวัน</label>
                    </div>
                    <div class="bg-[#ECECED] h-[1px] w-full"></div>
                    <span>ลักษณะการจ้างงาน</span>
                    <div class="flex flex-row justify-between">
                        <div class="flex flex-row gap-[8px]">
                            <label for="">อยู่ประจำ ค้างคืน</label>
                        </div>
                        <div class="w-full max-w-[400px]">
                            <input type="number" name="DAILY[FULL_STAY]" id="daily_full_stay_cost" placeholder="฿ ค่าบริการ"
                                class="border rounded-lg px-3 py-2 w-full" value="{{ old('DAILY.FULL_STAY', $nursingCosts['DAILY']['FULL_STAY']['cost'] ?? '') }}"/>
                        </div>
                    </div>
                    <div class="flex flex-row justify-between">
                        <div class="flex flex-row gap-[8px]">
                            <label for="">อยู่ประจำ ไปกลับ</label>
                        </div>
                        <div class="w-full max-w-[400px]">
                            <input type="number" name="DAILY[FULL_ROUND]" id="daily_full_round_cost" maxlength="10" placeholder="฿ ค่าบริการ"
                                class="border rounded-lg px-3 py-2 w-full" value="{{ old('DAILY.FULL_ROUND', $nursingCosts['DAILY']['FULL_ROUND']['cost'] ?? '') }}"/>
                        </div>
                    </div>
                    <div class="flex flex-row justify-between">
                        <div class="flex flex-row gap-[8px]">
                            <label for="">ชั่วคราว ค้างคืน</label>
                        </div>
                        <div class="w-full max-w-[400px]">
                            <input type="number" name="DAILY[PART_STAY]" id="daily_part_stay_cost" maxlength="10" placeholder="฿ ค่าบริการ"
                                class="border rounded-lg px-3 py-2 w-full" value="{{ old('DAILY.PART_STAY', $nursingCosts['DAILY']['PART_STAY']['cost'] ?? '') }}"/>
                        </div>
                    </div>
                    <div class="flex flex-row justify-between">
                        <div class="flex flex-row gap-[8px]">
                            <label for="">ชั่วคราว ไปกลับ</label>
                        </div>
                        <div class="w-full max-w-[400px]">
                            <input type="number" name="DAILY[PART_ROUND]" id="daily_part_round_cost" maxlength="10" placeholder="฿ ค่าบริการ"
                                class="border rounded-lg px-3 py-2 w-full" value="{{ old('DAILY.PART_ROUND', $nursingCosts['DAILY']['PART_ROUND']['cost'] ?? '') }}"/>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-[16px] p-[16px] bg-[#F7FCF9] rounded-[8px]">
                    <div class="flex flex-row gap-[12px]">
                        <label class="has-border text-[#286F51] font-semibold">รายเดือน</label>
                    </div>
                    <div class="bg-[#ECECED] h-[1px] w-full"></div>
                    <span>ลักษณะการจ้างงาน</span>
                    <div class="flex flex-row justify-between">
                        <div class="flex flex-row gap-[8px]">
                            <label for="">อยู่ประจำ ค้างคืน</label>
                        </div>
                        <div class="w-full max-w-[400px]">
                            <input type="number" name="MONTH[FULL_STAY]" id="monthly_full_stay_cost" maxlength="10" placeholder="฿ ค่าบริการ"
                                class="border rounded-lg px-3 py-2 w-full" value="{{ old('DAILY.FULL_STAY', $nursingCosts['DAILY']['FULL_STAY']['cost'] ?? '') }}"/>
                        </div>
                    </div>
                    <div class="flex flex-row justify-between">
                        <div class="flex flex-row gap-[8px]">
                            <label for="">อยู่ประจำ ไปกลับ</label>
                        </div>
                        <div class="w-full max-w-[400px]">
                            <input type="number" name="MONTH[FULL_ROUND]" id="monthly_full_round_cost" maxlength="10" placeholder="฿ ค่าบริการ"
                                class="border rounded-lg px-3 py-2 w-full" value="{{ old('DAILY.FULL_ROUND', $nursingCosts['DAILY']['FULL_ROUND']['cost'] ?? '') }}"/>
                        </div>
                    </div>
                    <div class="flex flex-row justify-between">
                        <div class="flex flex-row gap-[8px]">
                            <label for="">ชั่วคราว ค้างคืน</label>
                        </div>
                        <div class="w-full max-w-[400px]">
                            <input type="number" name="MONTH[PART_STAY]" id="monthly_part_stay_cost" maxlength="10" placeholder="฿ ค่าบริการ"
                                class="border rounded-lg px-3 py-2 w-full" value="{{ old('DAILY.PART_STAY', $nursingCosts['DAILY']['PART_STAY']['cost'] ?? '') }}"/>
                        </div>
                    </div>
                    <div class="flex flex-row justify-between">
                        <div class="flex flex-row gap-[8px]">
                            <label for="">ชั่วคราว ไปกลับ</label>
                        </div>
                        <div class="w-full max-w-[400px]">
                            <input type="number" name="MONTH[PART_ROUND]" id="monthly_part_round_cost" maxlength="10" placeholder="฿ ค่าบริการ"
                                class="border rounded-lg px-3 py-2 w-full" value="{{ old('DAILY.PART_ROUND', $nursingCosts['DAILY']['PART_ROUND']['cost'] ?? '') }}"/>
                        </div>
                    </div>
                </div>

                <div class="bg-[#ECECED] w-full h-[1px]"></div>

                <div class="flex flex-row justify-between">
                    <div class="flex flex-row gap-[24px]">
                        <button type="submit" class="w-[200px] h-[48px] rounded-lg bg-[#286F51] text-white">บันทึกข้อมูล</button>
                    </div>
                </div> 
        </form>
    </div>
    <!-- <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <div class="text-[16px] flex flex-col gap-[32px] w-full max-w-[1440px] mx-auto">
            <div class="flex flex-row justify-between bg-[#F7FCF9] px-[12px] py-[8px] rounded-md">
                <div class="flex flex-row gap-[8px] items-center">
                    <svg class="w-6 h-6 text-[#286F51]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <span class="text-md text-[#286F51] font-medium">
                        รายการแพ็กเกจ
                    </span>
                </div>
            </div>

            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 dataTable no-footer">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">ประเภท</th>
                        <th class="px-6 py-3 max-w-[50%]">ลักษณะการจ้างงาน</th>
                        <th class="px-6 py-3">ราคา</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800">
                    @if($costs)
                        @foreach($costs as $cost)
                            @php
                                $hire_rule = '';
                                switch($cost->hire_rule) {
                                    case 'FULL_ROUND':
                                        $hire_rule = 'อยุ่ประจำ ไปกลับ';
                                        break;
                                    case 'FULL_STAY':
                                        $hire_rule = 'อยู่ประจำ ค้างคืน';
                                        break;
                                    case 'PART_ROUND':
                                        $hire_rule = 'ชั่วคราว ไปกลับ';
                                        break;
                                    case 'PART_STAY':
                                        $hire_rule = 'ชั่วคราว ค้างคืน';
                                        break;
                                }
                            @endphp
                            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-700 dark:even:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                <td class="px-6 py-3">
                                    {{ $cost->type == 'DAILY' ? 'รายวัน' : 'รายเดือน' }}
                                </td>
                                <td class="px-6 py-3 w-[50%]">{{ $hire_rule }}</td>
                                <td class="px-6 py-3">
                                    ฿{{ number_format($cost->cost, 2, '.', ',') }}
                                </td>
                                <td class="px-6 py-3"></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div> -->
</div>
@endsection
@section('style')
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> 
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
