@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <h1>รายการ พยาบาล/ผู้ดูแล</h1>
        <a href="{{route('nursing.create')}}" class="text-blue-600 hover:underline">เพิ่มใหม่</a>
    </div>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg opacity-0" id="nursingTableWrapper">
        <table id="nursingTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3"></th>
                    <th class="px-6 py-3 w-[140px]">รูปภาพ</th>
                    <th class="px-6 py-3">ชื่อ</th>
                    <th class="px-6 py-3 w-[140px]">คะแนนเฉลี่ย</th>
                    <th class="px-6 py-3 w-[140px]">จำนวนรีวิว</th>
                    <th class="px-6 py-3 w-[140px]">สถานะ เปิด/ปิด</th>
                    <th class="px-6 py-3 w-[140px]"><span class="sr-only">แก้ไข</span></th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800">
                <!-- DataTables เติมข้อมูลตรงนี้ -->
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('javascript')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(function() {
    $('#nursingTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false, // ปิด auto width ของ DataTables
        responsive: true, // ถ้าใช้ responsive
        ajax: '{{ route("nursing.data") }}',
        order: [[0, 'desc']], // <--- 4 คือ column index ของ 'id' (ลำดับเริ่ม 0)
        language: {
            decimal:        "",
            emptyTable:     "ไม่มีข้อมูลในตาราง",
            info:           "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty:      "แสดง 0 ถึง 0 จาก 0 รายการ",
            infoFiltered:   "(กรองจากทั้งหมด _MAX_ รายการ)",
            infoPostFix:    "",
            thousands:      ",",
            lengthMenu:     "แสดง _MENU_ รายการ",
            loadingRecords: "กำลังโหลด...",
            processing:     "กำลังประมวลผล...",
            search:         "ค้นหา:",
            zeroRecords:    "ไม่พบข้อมูลที่ค้นหา",
            paginate: {
                first:    "หน้าแรก",
                last:     "หน้าสุดท้าย",
                next:     "ถัดไป",
                previous: "ก่อนหน้า"
            },
            aria: {
                sortAscending:  ": เปิดใช้งานเพื่อเรียงข้อมูลจากน้อยไปมาก",
                sortDescending: ": เปิดใช้งานเพื่อเรียงข้อมูลจากมากไปน้อย"
            }
        },
        columns: [
            { data: 'id', name: 'id', searchable: false, orderable: true, visible: false }, // ซ่อนแต่ sort ได้
            { data: 'cover_image', name: 'cover_image', orderable: false, searchable: false },
            { data: 'name', name: 'profile.name' },
            { data: 'average_score', name: 'average_score' },
            { data: 'review_count', name: 'review_count' },
            {
                data: 'status', name: 'status', searchable: false, orderable: false, render: function (data, type, row) {
                    let status = '';
                    if(data == 1) {
                        status = `<svg class="mx-auto w-6 h-6 text-green-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/>
                        </svg>
                        `;
                    } else {
                        status = `<svg class="mx-auto w-6 h-6 text-red-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                        </svg>
                        `;
                    }
                    return `<a href="#" onclick="statusToggle(${row.id})">${status}</a>`;
                }
            },
            {
                data: 'id', name: 'id', searchable: false, orderable: false, render: function (data, type, row) {
                    let url = "{{ route('nursing.edit', ':id') }}"; // ใส่ placeholder
                    url = url.replace(':id', data); // แทนที่ด้วยค่าจริง
                    return `<a href="${url}">แก้ไข</a>`;
                }
            },
        ],
        columnDefs: [
            {
                targets: 1,
                className: 'text-center',
                render: function(data) {
                    // ตรวจสอบให้แน่ใจว่า data เป็น URL ของภาพเท่านั้น
                    return data ? `<img src="${data}" class="w-16 h-16 rounded-md object-cover mx-auto">` : '';
                }
            },
            {
                targets: 1,
                render: function(data, type, row) {
                    return `<span title="ID: ${row.id}">${data}</span>`;
                }
            },
            {
                targets: 4,
                className: 'text-right'
            },
            {
                targets: 5,
                className: 'text-center'
            },
            {
                targets: 6,
                className: 'text-right'
            }
        ],
        initComplete: function(settings, json) {
            // เมื่อ DataTable โหลดเสร็จ ค่อยแสดง
            $('#nursingTableWrapper').removeClass('opacity-0').addClass('opacity-100 transition-opacity duration-300');
        },
        drawCallback: function(settings){
            // เอา length และ filter มาห่อ div และใช้ flex
            const wrapperId = 'nursingTable_wrapper'; // DataTables auto wrapper
            const wrapper = $('#' + wrapperId);

            // สร้าง flex container ถ้ายังไม่มี
            if(wrapper.find('.dt-header-flex').length === 0){
                wrapper.find('.dataTables_length, #nursingTable_filter')
                    .wrapAll('<div class="dt-header-flex flex flex-row justify-between items-center mb-4 px-2"></div>');
            }

            // เอา info + pagination มาห่อด้วย div flex
            const tableWrapper = $(this).closest('.dataTables_wrapper');

            // check ถ้ายังไม่ wrap
            if (!tableWrapper.find('.custom-footer').length) {
                tableWrapper.find('.dataTables_info, .dataTables_paginate').wrapAll('<div class="custom-footer flex flex-col md:flex-row items-center justify-between mt-4 px-2 text-xs"></div>');
            }

            // Tailwind styling
            tableWrapper.find('.dataTables_paginate').addClass('flex space-x-2');
            tableWrapper.find('.dataTables_paginate a').addClass('px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700');
            tableWrapper.find('.dataTables_paginate .current').addClass('bg-blue-600 text-white');

            // zebra stripe + hover + padding
            $('#nursingTable_filter input')
            .addClass('border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none ml-2');
            $('#nursingTable_length label').addClass('flex items-center gap-2 text-gray-700 dark:text-gray-300 font-medium');
            $('#nursingTable_length select').addClass('pr-6 border border-gray-300 rounded-md px-2 py-1 text-sm bg-white dark:bg-gray-700 dark:text-white');
            $('table tbody tr:even').addClass('bg-gray-50 dark:bg-gray-700');
            $('table tbody tr:odd').addClass('bg-white dark:bg-gray-800');
            $('table tbody tr').addClass('hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200');
            $('table tbody tr td, table tbody tr th').addClass('px-6 py-4');
            $('.dataTables_paginate').addClass('flex justify-center items-center gap-2 my-2');
            $('.dataTables_paginate span').addClass('flex justify-center items-center gap-2 !m-0');
            $('.dataTables_paginate a').addClass('px-3 py-1 !m-0 rounded border border-gray-300 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700');
            $('.dataTables_paginate .current').addClass('bg-blue-600 text-white');
        }
    });
});

</script>
@endsection
