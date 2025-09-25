@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <h1>รายการ ประกาศงาน</h1>
        <a href="{{ route('job.create') }}" class="text-blue-600 hover:underline">เพิ่มใหม่</a>
    </div>

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg opacity-0" id="jobTableWrapper">
        <table id="jobTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th></th>
                    <th class="px-6 py-3">หัวข้อ</th>
                    <th class="px-6 py-3">สถานะ</th>
                    <th class="px-6 py-3">ประเภทบริการ</th>
                    <th class="px-6 py-3">ลักษณะการจ้าง</th>
                    <th class="px-6 py-3">สถานที่ทำงาน</th>
                    <th class="px-6 py-3">งบประมาณ</th>
                    <th class="px-6 py-3">วันที่ลงประกาศ</th>
                    <th></th>
                    <th class="px-6 py-3"><span class="sr-only">แก้ไข</span></th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800"></tbody>
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
        $('#jobTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            responsive: true,
            ajax: '{{ route("job.pagination") }}',
            order: [[0, 'desc']],
            language: {
                decimal: "",
                emptyTable: "ไม่มีข้อมูลในตาราง",
                info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                infoEmpty: "แสดง 0 ถึง 0 จาก 0 รายการ",
                infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)",
                thousands: ",",
                lengthMenu: "แสดง _MENU_ รายการ",
                loadingRecords: "กำลังโหลด...",
                processing: "กำลังประมวลผล...",
                search: "ค้นหา:",
                zeroRecords: "ไม่พบข้อมูลที่ค้นหา",
                paginate: {
                    first: "หน้าแรก",
                    last: "หน้าสุดท้าย",
                    next: "ถัดไป",
                    previous: "ก่อนหน้า"
                }
            },
            columns: [
                { data: 'id', name: 'id', searchable: false, orderable: true, visible: false },

                { data: 'name', name: 'name', searchable: true, orderable: true },

                { 
                    data: 'status', 
                    name: 'status', 
                    searchable: true, 
                    orderable: true,
                    render: function(data) {
                        if (data === 'OPEN') return '<span class="text-center w-[55px] block text-sm bg-green-200 rounded-md p-2 text-green-800">เปิดรับ</span>';
                        if (data === 'CLOSED') return '<span class="text-center w-[60px] block text-sm bg-red-200 rounded-md p-2 text-red-800">ปิดรับ</span>';
                        return '<span class="text-center w-[70px] block text-sm bg-gray-200 rounded-md p-2 text-gray-800">หมดอายุ</span>';
                    }
                },

                { 
                    data: 'service_type',
                    name: 'service_type', 
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        if (data === 'NURSING') return 'พยาบาลดูแลผู้สูงอายุ';
                        if (data === 'NURSING_HOME') return 'ศูนย์ดูแลผู้สูงอายุ';
                        return data ?? '-';
                    }
                },

                {
                    data: 'hire_type',
                    name: 'hire_type',
                    searchable: true,
                    orderable: true,
                    render: {
                        _: function(data) {
                            const labels = { DAILY: "รายวัน", WEEKLY: "รายสัปดาห์", MONTHLY: "รายเดือน", YEARLY: "รายปี" };
                            return labels[data] ?? data ?? '-';
                        },
                        filter: function(data) {
                            const labels = { DAILY: "รายวัน", WEEKLY: "รายสัปดาห์", MONTHLY: "รายเดือน", YEARLY: "รายปี" };
                            return (labels[data] ?? '') + ' ' + data;
                        }
                    }
                },

                // Province + District column
                {
                    data: null,
                    name: 'location',
                    searchable: true,
                    orderable: false,
                    render: function(data, type, row) {
                        const province = row.province?.name ?? '';
                        const district = row.district?.name ?? '';
                        return province && district ? `${province}, ${district}` : province || district || '-';
                    }
                },

                {
                    data: 'cost',
                    name: 'cost',
                    orderable: true,
                    render: function(data) {
                        return data ? parseFloat(data).toLocaleString('th-TH', { minimumFractionDigits: 0 }) : '-';
                    }
                },

                {
                    data: 'created_at',
                    name: 'created_at',
                    orderable: true,
                    render: function(data) {
                        if (!data) return '-';
                        const date = new Date(data);
                        return date.toLocaleDateString('th-TH', { year: 'numeric', month: '2-digit', day: '2-digit' });
                    }
                },
                {
                    data: null,
                    name: 'switch_status',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row) {
                        if(row.status == 'CLOSED') {
                            return `<span style="cursor:pointer" onclick="switchTo(${row.id}, 'OPEN')">
                                <svg class="w-6 h-6 text-red-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                </svg>
                            </span>`;
                        } else if ( row.status == 'OPEN' ) {
                            return `<span style="cursor:pointer" onclick="switchTo(${row.id}, 'CLOSED')">
                                <svg class="w-6 h-6 text-green-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/>
                                </svg>
                            </span>`;
                        } else {
                            return `<span>
                                <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                                </svg>
                            </span>`;
                        }
                    }
                },
                {
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    orderable: false,
                    render: function(data) {
                        let url = "{{ route('job.edit', ':id') }}";
                        url = url.replace(':id', data);
                        return `<a href="${url}" class="text-blue-600 hover:underline">แก้ไข</a>`;
                    }
                },
            ],

            initComplete: function() {
                $('#jobTableWrapper').removeClass('opacity-0').addClass('opacity-100 transition-opacity duration-300');
            },

            drawCallback: function() {
                const wrapper = $('#jobTable_wrapper');

                // header flex
                if(wrapper.find('.dt-header-flex').length === 0){
                    wrapper.find('.dataTables_length, #jobTable_filter')
                        .wrapAll('<div class="dt-header-flex flex flex-row justify-between items-center mb-4 px-2"></div>');
                }

                // footer flex
                const tableWrapper = $(this).closest('.dataTables_wrapper');
                if (!tableWrapper.find('.custom-footer').length) {
                    tableWrapper.find('.dataTables_info, .dataTables_paginate')
                        .wrapAll('<div class="custom-footer flex flex-col md:flex-row items-center justify-between m-4 px-2 text-xs"></div>');
                }

                // pagination styling
                tableWrapper.find('.dataTables_paginate').addClass('flex space-x-2 items-center');
                tableWrapper.find('.dataTables_paginate a').addClass('px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700');
                tableWrapper.find('.dataTables_paginate .current').addClass('bg-blue-600 text-white');

                // filter + length styling
                $('#jobTable_filter input').addClass('border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none ml-2');
                $('#jobTable_length label').addClass('flex items-center gap-2 text-gray-700 dark:text-gray-300 font-medium');
                $('#jobTable_length select').addClass('pr-6 border border-gray-300 rounded-md px-2 py-1 text-sm bg-white dark:bg-gray-700 dark:text-white');

                // table row styling
                $('table tbody tr:even').addClass('bg-gray-50 dark:bg-gray-700');
                $('table tbody tr:odd').addClass('bg-white dark:bg-gray-800');
                $('table tbody tr').addClass('hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200');
                $('table tbody tr td, table tbody tr th').addClass('px-6 py-4');
            }
        });

    });
</script>

<script>
    // ✅ ฟังก์ชันต้องอยู่นอก $(function() {...})
    function switchTo(id, status) {
        let url = "{{ route('job.status-update', ':id') }}";
        url = url.replace(':id', id);

        axios.post(url, {
            status: status
        }).then(res => {
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: res.data.message,
                showConfirmButton: false,
                timer: 2000
            });
            $('#jobTable').DataTable().ajax.reload(null, false); // reload table
        }).catch(err => {
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