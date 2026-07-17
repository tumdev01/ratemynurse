@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <h1>รายการ สมาชิก</h1>
    </div>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg opacity-0" id="memberTableWrapper">
        <table id="memberTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3"></th>
                    <th class="px-6 py-3">รูปภาพ</th>
                    <th class="px-6 py-3">ชื่อ</th>
                    <th class="px-6 py-3">อีเมล์</th>
                    <th class="px-6 py-3">เบอร์โทรศัพท์</th>
                    <th class="px-6 py-3">จังหวัด</th>
                    <th class="px-6 py-3">แพ็กเกจปัจจุบัน</th>
                    <th class="px-6 py-3">สถานะ</th>
                    <th class="px-6 py-3"><span class="sr-only">รายละเอียด</span></th>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    $('#memberTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        responsive: true,
        ajax: '{{ route("member.data") }}',
        order: [[0, 'desc']],
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
            { data: 'id', name: 'id', searchable: false, orderable: true, visible: false },
            { data: 'cover_image', name: 'cover_image', orderable: false, searchable: false },
            {
                data: 'id', name: 'fullname', orderable: false, searchable: false, render: function (data, type, row) {
                    let url = "{{ route('member.detail', ':id') }}";
                    url = url.replace(':id', data);
                    return `<a class="underline" href="${url}">${row.fullname}</a>`;
                }
            },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'province', name: 'province', searchable: false, orderable: false },
            { data: 'current_package', name: 'current_package', searchable: false, orderable: false },
            {
                data: null, name: 'status', searchable: false, orderable: false, render: function (data, type, row) {
                    let checked = row.status == 1 ? 'checked' : '';
                    return `<label class="status-toggle">
                        <input type="checkbox" class="status-toggle-input" ${checked} onchange="toggleMemberStatus(${row.id}, this)">
                        <span class="status-toggle-track"></span>
                    </label>`;
                }
            },
            {
                data: 'id', name: 'id', searchable: false, orderable: false, render: function (data, type, row) {
                    let url = "{{ route('member.detail', ':id') }}";
                    url = url.replace(':id', data);
                    return `<a href="${url}">ดูรายละเอียด</a>`;
                }
            },
        ],
        columnDefs: [
            {
                targets: 1,
                className: 'text-center',
                render: function(data) {
                    return data ? `<img src="${data}" class="w-16 h-16 rounded-md object-cover mx-auto">` : '';
                }
            }
        ],
        initComplete: function(settings, json) {
            $('#memberTableWrapper').removeClass('opacity-0').addClass('opacity-100 transition-opacity duration-300');
        },
        drawCallback: function(settings){
            const wrapperId = 'memberTable_wrapper';
            const wrapper = $('#' + wrapperId);

            if(wrapper.find('.dt-header-flex').length === 0){
                wrapper.find('.dataTables_length, #memberTable_filter')
                    .wrapAll('<div class="dt-header-flex flex flex-row justify-between items-center mb-4 px-2"></div>');
            }

            const tableWrapper = $(this).closest('.dataTables_wrapper');

            if (!tableWrapper.find('.custom-footer').length) {
                tableWrapper.find('.dataTables_info, .dataTables_paginate').wrapAll('<div class="custom-footer flex flex-col md:flex-row items-center justify-between mt-4 px-2 text-xs"></div>');
            }

            tableWrapper.find('.dataTables_paginate').addClass('flex space-x-2');
            tableWrapper.find('.dataTables_paginate a').addClass('px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700');
            tableWrapper.find('.dataTables_paginate .current').addClass('bg-blue-600 text-white');

            $('#memberTable_filter input')
            .addClass('border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none ml-2');
            $('#memberTable_length label').addClass('flex items-center gap-2 text-gray-700 dark:text-gray-300 font-medium');
            $('#memberTable_length select').addClass('pr-6 border border-gray-300 rounded-md px-2 py-1 text-sm bg-white dark:bg-gray-700 dark:text-white');
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

<script>
    // ✅ ฟังก์ชันต้องอยู่นอก $(function() {...})
    function toggleMemberStatus(id, checkbox) {
        let url = "{{ route('member.status-update', ':id') }}";
        url = url.replace(':id', id);
        let newStatus = checkbox.checked ? 1 : 0;

        axios.post(url, {
            status: newStatus
        }).then(res => {
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: res.data.message,
                showConfirmButton: false,
                timer: 2000
            });
        }).catch(err => {
            checkbox.checked = !checkbox.checked; // revert เพราะ request ล้มเหลว
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'error',
                title: 'ไม่สามารถอัพเดทสถานะได้',
                showConfirmButton: false,
                timer: 2000
            });
        });
    }
</script>
@endsection
