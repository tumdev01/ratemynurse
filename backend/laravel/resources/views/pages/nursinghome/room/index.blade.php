@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <h1>รายการ ห้องพัก <a class="underline text-blue-200" href="{{ route('nursing-home.edit', $nursingHome->id) }}">{{$nursingHome->name}}</a></h1>
        <a href="{{route('nursing-home.room.create', $nursingHome->id)}}" class="text-blue-600 hover:underline">เพิ่มใหม่</a>
    </div>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg opacity-0" id="roomTableWrapper">
        <table id="roomTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3"></th>
                    <th class="px-6 py-3">รูปภาพ</th>
                    <th class="px-6 py-3">ชื่อ</th>
                    <th class="px-6 py-3">รายละเอียด</th>
                    <th class="px-6 py-3">ราคา/คืน</th>
                    <th class="px-6 py-3">ราคา/เดือน</th>
                    <th class="px-6 py-3"><span class="sr-only">แก้ไข</span></th>
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
    const nursinghomeId = {{ $nursingHome->id }};
    $(function() {
        $('#roomTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false, // ปิด auto width ของ DataTables
            responsive: true, // ถ้าใช้ responsive
            ajax: {
                url: '{{ route("nursing-home.room.data") }}',
                data: function (d) {
                    d.user_id = {{ $nursingHome->id }}; // ส่งค่า nursingHome id ไปด้วย
                }
            },
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
                { data: 'name', name: 'name' },
                { 
                    data: 'description', 
                    name: 'description', 
                    searchable: false, 
                    orderable: false,
                    render(data, type, row) {
                        if (!data) return '';
                        return data.length > 100 
                            ? data.substring(0, 100) + '...' 
                            : data;
                    }
                },
                {
                    data: 'cost_per_day',
                    name: 'cost_per_day',
                    render: function (data, type, row) {
                        let num = parseFloat(data) || 0;
                        return '฿' + num.toLocaleString('th-TH', { minimumFractionDigits: 2 });
                    }
                },
                {
                    data: 'cost_per_month',
                    name: 'cost_per_month',
                    render: function (data, type, row) {
                        let num = parseFloat(data) || 0;
                        return '฿' + num.toLocaleString('th-TH', { minimumFractionDigits: 2 });
                    }
                },
                {
                    data: 'id', name: 'id', searchable: false, orderable: false, render: function (data, type, row) {
                        let url = "{{ route('nursing-home.edit', ':id') }}"; // ใส่ placeholder
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
            }
            ],
            initComplete: function(settings, json) {
                // เมื่อ DataTable โหลดเสร็จ ค่อยแสดง
                $('#roomTableWrapper').removeClass('opacity-0').addClass('opacity-100 transition-opacity duration-300');
            },
            drawCallback: function(settings){
                // เอา length และ filter มาห่อ div และใช้ flex
                const wrapperId = 'roomTable_wrapper'; // DataTables auto wrapper
                const wrapper = $('#' + wrapperId);

                // สร้าง flex container ถ้ายังไม่มี
                if(wrapper.find('.dt-header-flex').length === 0){
                    wrapper.find('.dataTables_length, #roomTable_filter')
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
                $('#roomTable_filter input')
                .addClass('border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none ml-2');
                $('#roomTable_length label').addClass('flex items-center gap-2 text-gray-700 dark:text-gray-300 font-medium');
                $('#roomTable_length select').addClass('pr-6 border border-gray-300 rounded-md px-2 py-1 text-sm bg-white dark:bg-gray-700 dark:text-white');
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
