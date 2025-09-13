@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <h1>
            <a class="underline" href="{{ route('nursing-home.edit', $nursinghome->id) }}">{{  __('กลับหน้าแก้ไข') }} ( {{ $nursinghome->profile->name }} )</a>
        </h1>
    </div>

    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <form id="frmRate" class="flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ $nursinghome->id }}">
            <input type="hidden" name="user_type" value="NURSING_HOME">
            <div class="flex flex-col justify-start bg-[#F0F9F4] p-[16px] rounded-md">
                <span class="htitle text-[16px] md:text-lg text-[#286F51]">เพิ่มรีวิว {{ $nursinghome->firstname }}</span>
                <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน</span>
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
                <div id="frm_personal" class="flex flex-col gap-[32px]">
                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">ข้อมูลทั่วไปของศูนย์</span>
                    </span>

                    <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label for="main_phone">ค้นหาผู้ให้คะแนน <span class="req">*</span></label>
                            <select name="author_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></select>
                            <label class="explain text-xs py-2 text-gray-300">เว้นว่าง หากไม่ต้องการระบุ หรือ กำลังเพิ่มในฐานะผู้ดูแลระบบ</label>
                            <label class="error text-xs text-red-600 py-2"></label>
                        </div>
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label for="description">บริการที่ใช้ <span class="req">*</span></label>
                            <input required type="text" name="description" id="description" maxlength="10" placeholder="ระบุบริการที่ใช้ เช่น ผู้ใช้บริการบ้านพักผู้สูงอายุ"
                                class="border rounded-lg px-3 py-2" value="{{ old('description') }}"/>
                            <label class="error text-xs text-red-600"></label>
                        </div>
                    </div>
                    
                    <div class="flex flex-col">
                        <label for="text">ข้อความ <span class="req">*</span></label>
                        <textarea required id="text" name="text" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="ข้อความรีวิว">{{ old('text') ?? '' }}</textarea>
                    </div>

                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-md text-white font-semibold">หัวข้อที่ให้คะแนน</span>
                    </span>

                    <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section px-4 bg-gray-50">
                        <div class="w-full flex flex-col">
                            @foreach($choices as $key => $choice)
                                <div class="flex gap-2 flex-row py-4 items-center">
                                    <label class="w-full">{{ $choice }}</label>
                                    <input class="w-[50px] bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block ps-2.5 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="number" min="1" max="5" name="scores[{{$key}}]" value="1">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="relative overflow-x-auto shadow-md opacity-100 transition-opacity duration-300">
        @if ( count($nursinghome->rates) > 0 )
            <table id="nursingHomesTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 w-[150px]"></th>
                        <th class="px-6 py-3">ผู้ให้คะแนน</th>
                        <th class="px-6 py-3">บริการที่ใช้</th>
                        <th class="px-6 py-3">รีวิว</th>
                        <th class="px-6 py-3">คะแนนเฉลี่ย</th>
                        <th class="px-6 py-3">หัวข้อ - คะแนน</th>
                        <th class="px-6 py-3"><span class="sr-only">ลบ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nursinghome->rates as $rate)
                        <tr data-id="{{ $rate->id }}" class="hover:bg-gray-100">
                            <th class="px-6 py-3 w-[150px] font-normal">
                                <span class="p-2 bg-gray-200 rounded-full w-[50px] h-[50px] inline-block">
                                @if($rate->image)
                                    <img class="w-[50px] h-[50px]" src="{{ asset($rate->image) }}" alt="{{ $rate->name }}">
                                @endif
                                </span>
                            </th>
                            <th class="px-6 py-3 font-normal">{{ $rate->name }}</th>
                            <th class="px-6 py-3 font-normal">{{ $rate->description }}</th>
                            <th class="px-6 py-3 font-normal">{{ $rate->text }}</th>
                            <th class="px-6 py-3 font-normal">{{ $rate->avg_scores }}</th>
                            <th class="px-6 py-3 font-normal">
                                @if($rate->rate_details)
                                    
                                    @foreach($rate->rate_details as $rate_detail)
                                        <div class="py-2">
                                            <label class="text-sm font-medium">{{ $rate_detail->scores_for_label}}</label>
                                            <div class="flex flex-row gap-1 mt-2">
                                            @for($i=0; $i<$rate_detail->scores; $i++)
                                                <div class="bg-green-500 w-[50px] h-[5px]"></div>
                                            @endfor
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                @endif
                            </th>
                            <th class="px-6 py-3 font-normal">
                                <span onclick="deleteRate({{ $rate->id }})">
                                    <svg class="cursor-pointer w-4 h-4 text-red-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/> </svg>
                                </span>
                            </th>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="p-4">{{ __('ยังไม่มีรีวิว') }}
        @endif
    </div>

@endsection

@section('style')
    <style>
        .swal-confirm-btn {
        background-color: #dc3545 !important; /* แดง */
        color: #fff !important;
        }

        .swal-cancel-btn {
        background-color: #6c757d !important; /* เทา */
        color: #fff !important;
        }
    </style>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const nursinghomeId = {{ $nursinghome->id }};

    ajaxCallDropdownOption('#author_id', '/api/members', 'กรุณาเลือกสมาชิก');
</script> 
@endsection