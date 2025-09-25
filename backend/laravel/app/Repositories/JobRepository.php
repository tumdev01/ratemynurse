<?php

namespace App\Repositories;
use Illuminate\Support\Arr;
use App\Models\Job;
use App\Models\User;
use Yajra\DataTables\DataTables;

class JobRepository extends BaseRepository {
    public function store(array $params)
    {
        try {
            $user = User::findOrFail(Arr::get($params, 'user_id'));

            $job = Job::create([
                'user_id' => $user->id,
                'name' => Arr::get($params, 'name'),
                'service_type' => Arr::get($params, 'service_type'),
                'hire_type' => Arr::get($params, 'hire_type'),
                'cost' => Arr::get($params, 'cost'),
                'start_date' => Arr::get($params, 'start_date'),
                'description' => Arr::get($params, 'description'),
                'address' => Arr::get($params, 'address'),
                'province_id' => Arr::get($params, 'province_id'),
                'district_id' => Arr::get($params, 'district_id'),
                'sub_district_id' => Arr::get($params, 'sub_district_id'),
                'phone' => Arr::get($params, 'phone'),
                'email' => Arr::get($params, 'email') ?? NULL,
                'facebook' => Arr::get($params, 'facebook') ?? NULL,
                'lineid' => Arr::get($params, 'lineid') ?? NULL
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'บันทึกสำเร็จ'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getJobPagination(array $filters = [])
    {
        $by_user      = Arr::get($filters, 'user', false);
        $limits       = Arr::get($filters, 'limits', 10);
        $order        = Arr::get($filters, 'order', 'DESC');
        $order_by     = Arr::get($filters, 'order_by', 'id');
        $service_type = Arr::get($filters, 'service_type');
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

        return DataTables::of($query)
            ->addColumn('action', fn($n) => '<a href="#" class="text-blue-600 hover:underline">แก้ไข</a>')
            ->rawColumns(['action'])
            ->orderColumn($orderby, fn($query, $order) => $query->orderBy($orderby, $order))
            ->make(true);
    }

}