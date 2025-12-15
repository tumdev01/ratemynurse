<?php

namespace App\Repositories;
use Illuminate\Support\Arr;
use App\Models\Job;
use App\Models\User;
use Yajra\DataTables\DataTables;

class JobRepository extends BaseRepository {

    public function getJob(Int $id)
    {
        return Job::query()
        ->with([
            'province:id,name',
            'district:id,name',
            'sub_district:id,name',
            'user'
        ])
        ->where('id', (int) $id)
        ->whereNull('deleted_at')
        ->first();
    }

    public function store(array $params)
    {
        $user = User::findOrFail(Arr::get($params, 'user_id'));

        return Job::create([
            'user_id'        => $user->id,
            'name'           => Arr::get($params, 'name'),
            'service_type'   => Arr::get($params, 'service_type'),
            'care_type'      => Arr::get($params, 'care_type'),
            'hire_type'      => Arr::get($params, 'hire_type'),
            'hire_rule'      => Arr::get($params, 'hire_rule'),
            'cost'           => Arr::get($params, 'cost'),
            'start_date'     => Arr::get($params, 'start_date'),
            'description'    => Arr::get($params, 'description'),
            'address'        => Arr::get($params, 'address'),
            'province_id'    => Arr::get($params, 'province_id'),
            'district_id'    => Arr::get($params, 'district_id'),
            'sub_district_id'=> Arr::get($params, 'sub_district_id'),
            'phone'          => Arr::get($params, 'phone'),
            'email'          => Arr::get($params, 'email') ?? null,
            'facebook'       => Arr::get($params, 'facebook') ?? null,
            'lineid'         => Arr::get($params, 'lineid') ?? null,
        ]);
    }

    public function getJobPagination(array $filters = [])
    {
        $by_user      = Arr::get($filters, 'user', false);
        $limits       = Arr::get($filters, 'limits', 10);
        $order        = Arr::get($filters, 'order', 'DESC');
        $order_by     = Arr::get($filters, 'order_by', 'id');
        $service_type = Arr::get($filters, 'service_type');
        $care_type    = Arr::get($filters, 'care_type');
        $hire_type    = Arr::get($filters, 'hire_type');
        $min_cost     = Arr::get($filters, 'min_cost');   // ใช้ cost ไม่ใช่ price
        $max_cost     = Arr::get($filters, 'max_cost');
        $created_at   = Arr::get($filters, 'created_at'); // ใช้จาก schema
        $province_id  = Arr::get($filters, 'province_id');
        $page         = Arr::get($filters, 'page');

        $job = Job::query()
        ->with([
            'province:id,name',
            'district:id,name'
        ])
        ->whereNull('deleted_at');

        // filter by login user (from token)
        if ($by_user === true) {
            $user = auth()->user();
            if ($user) {
                $job->where('user_id', $user->id);
            }
        }

        // apply filters 
        $job->when($service_type, fn($q) => $q->where('service_type', $service_type))
            ->when($care_type, fn($q) => $q->where('care_type', $care_type))
            ->when($hire_type, fn($q) => $q->where('hire_type', $hire_type))
            ->when($province_id, fn($q) => $q->where('province_id', $province_id))
            ->when($min_cost, fn($q) => $q->where('cost', '>=', $min_cost))
            ->when($max_cost, fn($q) => $q->where('cost', '<=', $max_cost))
            ->when($created_at, fn($q) => $q->whereDate('created_at', '>=', $created_at));
        
        return $job->orderBy($order_by, $order)
                ->paginate($limits, ['*'], 'page', $page ?? 1);
    }

    public function getJobDataTable(array $filters = [])
    {
        $orderby = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order   = Arr::get($filters, 'order', 'DESC');

        $query = Job::query()
            ->with([
                'province:id,name',
                'district:id,name'
            ])
            ->whereNull('deleted_at');

        $labels = [
            'DAILY'   => 'รายวัน',
            'WEEKLY'  => 'รายสัปดาห์',
            'MONTHLY' => 'รายเดือน',
            'YEARLY'  => 'รายปี',
        ];

        $statusLabels = [
            'OPEN'   => 'เปิดรับ',
            'CLOSED' => 'ปิดรับ',
            'EXPIRED'=> 'หมดอายุ',
        ];

        return DataTables::of($query)
            // แสดง action
            ->addColumn('action', fn($n) => '<a href="#" class="text-blue-600 hover:underline">แก้ไข</a>')

            // แสดง hire_type เป็น label
            ->editColumn('hire_type', function ($row) use ($labels) {
                return $labels[$row->hire_type] ?? $row->hire_type;
            })

            // ให้ค้นหา hire_type ได้ทั้ง key และ label
            ->filterColumn('hire_type', function ($query, $keyword) use ($labels) {
                $reverse = array_flip($labels);
                $query->where(function($q) use ($keyword, $reverse) {
                    $q->where('hire_type', 'like', "%{$keyword}%");
                    foreach ($reverse as $label => $key) {
                        if (str_contains($label, $keyword)) {
                            $q->orWhere('hire_type', $key);
                        }
                    }
                });
            })

            // สร้าง column virtual ชื่อ location รวม province + district
            ->addColumn('location', function ($row) {
                $province = $row->province->name ?? '';
                $district = $row->district->name ?? '';
                return $province && $district ? "{$province}, {$district}" : ($province ?: ($district ?: '-'));
            })

            // ให้ค้นหา location ได้ทั้งจังหวัดและอำเภอ
            ->filterColumn('location', function ($query, $keyword) {
                $query->whereHas('province', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                })
                ->orWhereHas('district', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })

            // เพิ่ม filter สำหรับ status (ค้นหาจาก label)
            ->filterColumn('status', function($query, $keyword) use ($statusLabels) {
                $reverse = array_flip($statusLabels); // label => DB value
                $query->where(function($q) use ($keyword, $reverse, $statusLabels) {
                    foreach ($statusLabels as $dbValue => $label) {
                        if (str_contains($label, $keyword)) {
                            $q->orWhere('status', $dbValue);
                        }
                    }
                });
            })

            ->rawColumns(['action'])
            ->orderColumn($orderby, fn($query, $order) => $query->orderBy($orderby, $order))
            ->make(true);
    }

}