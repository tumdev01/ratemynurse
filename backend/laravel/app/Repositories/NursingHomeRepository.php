<?php

namespace App\Repositories;
use App\Models\User;
use App\Models\NursingHome;
use App\Models\NursingHomeProfile;
use App\Models\Province;
use App\Models\Image;
use App\Models\NursingHomeStaff;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\DataTables;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\NursingHomeUpdateRequest;
use App\Http\Requests\NursingHomeCreateStaffRequest;
use App\Enums\SpecialFacilityType;
use App\Enums\HomeServiceType;
use App\Enums\AdditionalServiceType;
use App\Enums\FacilityType;
use App\Enums\CenterHighlightType;

class NursingHomeRepository
{
    public function getNursingHomes(array $filters = [])
    {
        $query = NursingHomeProfile::query()
            ->with([
                'owner:id,firstname,lastname,user_type,status,email',
                'province:id,name',
                'district:id,name',
                'subDistrict:id,name',
                'images',        // polymorphic
                'coverImage',    // polymorphic
                'rates.rate_details',
            ])
            ->withCount(['rates as review_count'])
            ->select(['id', 'user_id', 'name', 'zipcode', 'province_id', 'district_id', 'sub_district_id', 'description', 'cost_per_month', 'certified'])
            ->selectSub(function ($q) {
                $q->from('rate_details')
                ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                ->selectRaw('AVG(rate_details.scores)')
                ->whereColumn('rates.rateable_id', 'nursing_home_profiles.id')
                ->where('rates.rateable_type', NursingHomeProfile::class);
            }, 'average_score')
            ->whereNull('deleted_at')
            ->whereHas('owner', function ($q) {
                $q->where('status', '!=', 0);
            });

        // Filter certified
        if (isset($filters['certified'])) {
            $certified = (int) $filters['certified'];
            $query->where('certified', $certified);
        }

        // Limit
        if (!empty($filters['limit']) && is_numeric($filters['limit'])) {
            $query->limit((int) $filters['limit']);
        }

        return $query->orderBy('id', 'DESC')->get();
    }

    // สำหรับ shortcode nursing-homes-specific เท่านั้น — เบากว่า getNursingHomes() เพราะดึงแค่ field
    // ที่ swiper การ์ดใช้แสดงผลจริง (ไม่ดึง owner, images ทั้งชุด, หรือ rates.rate_details ที่ไม่ได้ใช้
    // เพราะ average_score/review_count คำนวณจาก subquery/withCount อยู่แล้ว)
    public function getNursingHomesByIds(array $ids)
    {
        if (empty($ids)) {
            return collect();
        }

        return NursingHomeProfile::query()
            ->with([
                'province:id,name',
                'district:id,name',
                'subDistrict:id,name',
                'coverImage',
            ])
            // select() ต้องมาก่อน withCount() เสมอ — สลับกันแล้ว withCount จะถูกเขียนทับเงียบๆ
            // (ทดสอบยืนยันแล้วผ่าน tinker: select ก่อน -> review_count ได้ 0 ถูกต้อง, select หลัง -> ได้ NULL)
            ->select(['id', 'name', 'province_id', 'district_id', 'sub_district_id', 'description', 'cost_per_month', 'certified'])
            ->withCount(['rates as review_count'])
            ->selectSub(function ($q) {
                $q->from('rate_details')
                ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                ->selectRaw('AVG(rate_details.scores)')
                ->whereColumn('rates.rateable_id', 'nursing_home_profiles.id')
                ->where('rates.rateable_type', NursingHomeProfile::class);
            }, 'average_score')
            ->whereNull('deleted_at')
            ->whereHas('owner', function ($q) {
                $q->where('status', '!=', 0);
            })
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
            ->get();
    }

    public function getNursingHomePagination(array $filters = [])
    {
        $orderby   = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order     = Arr::get($filters, 'order', 'DESC');
        $limit     = Arr::get($filters, 'limit', 8); 
        $certified = Arr::get($filters, 'certified');
        $province  = Arr::get($filters, 'province');
        $zone      = Arr::get($filters, 'zone');

        $query = NursingHomeProfile::query()
            ->with([
                'owner:id,firstname,lastname,user_type,status,email',
                'province:id,name',
                'district:id,name',
                'subDistrict:id,name',
                'coverImage:id,imageable_id,imageable_type,path,is_cover',
                'images:id,imageable_id,imageable_type,path,is_cover',
                'rates.rate_details',
            ])
            ->withCount(['rates as review_count'])
            ->select(['id', 'user_id', 'name', 'zipcode', 'province_id', 'district_id', 'sub_district_id', 'description', 'cost_per_month', 'facilities', 'private_room_no', 'duo_room_no', 'center_highlights'])
            ->selectSub(function ($q) {
                $q->from('rate_details')
                ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                ->selectRaw('AVG(rate_details.scores)')
                ->whereColumn('rates.rateable_id', 'nursing_home_profiles.id')
                ->where('rates.rateable_type', NursingHomeProfile::class);
            }, 'average_score')
            ->whereHas('owner', function ($q) {
                $q->where('status', 1);
            })
            ->whereNull('deleted_at');

        // Filter province
        if (isset($filters['province'])) {
            $province_id = Province::where('code', $filters['province'])->value('id');
            $query->where('province_id', $province_id);
        }

        // Apply grid filters (facilities, cost, room, rate)
        $this->applyGridFilters($query, $filters);
        // Priority sorting
        $query->orderByRaw("
            CASE 
                WHEN sort_order > 0 THEN 0
                ELSE 1
            END
        ")->orderByRaw("
            CASE 
                WHEN sort_order > 0 THEN sort_order
                ELSE NULL
            END ASC
        ");
        $query->orderBy($orderby, $order);

        return $query->paginate($limit);
    }

    public function countByProvince(array $filters = [])
    {
        $certified = Arr::get($filters, 'certified');
        $province  = Arr::get($filters, 'province');

        $query = NursingHomeProfile::query()
            ->selectRaw('nursing_home_profiles.*')
            ->selectSub(function ($q) {
                $q->from('rate_details')
                ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                ->selectRaw('AVG(rate_details.scores)')
                ->whereColumn('rates.rateable_id', 'nursing_home_profiles.id')
                ->where('rates.rateable_type', NursingHomeProfile::class);
            }, 'average_score')
            ->whereHas('owner', function ($q) {
                $q->where('status', 1);
            })
            ->whereNull('deleted_at');

        if (isset($filters['province'])) {
            $province_id = Province::where('code', $filters['province'])->value('id');
            $query->where('province_id', $province_id);
        }

        // Apply grid filters (facilities, cost, room, rate)
        $this->applyGridFilters($query, $filters);

        return $query->count();
    }

    public function getNursingHomeWithZone(array $filters = [])
    {
        $orderby   = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order     = Arr::get($filters, 'order', 'DESC');
        $certified = Arr::get($filters, 'certified');
        $province  = Arr::get($filters, 'province');
        $zone      = Arr::get($filters, 'zone');
        $additionalLimit = Arr::get($filters, 'additional_limit', 10);
        
        // ถ้าไม่มี zone แต่มี province ให้ดึง zone จาก province
        if (!$zone && $province) {
            $zone = Province::where('code', $province)->value('zone');
        }
        
        // ดึงข้อมูลจากจังหวัดที่เลือก
        $provinceHomes = $this->getHomesQuery($filters)
            ->when(isset($filters['province']), function ($q) use ($filters) {
                $province_id = Province::where('code', $filters['province'])->value('id');
                $q->where('province_id', $province_id);
            })
            ->orderBy($orderby, $order)
            ->get();
        
        $provinceHomesIds = $provinceHomes->pluck('id')->toArray();
        
        // ดึงข้อมูลเพิ่มจาก zone (ไม่รวมจังหวัดที่เลือก)
        $zoneHomes = collect();
        if ($zone) {
            $zoneHomes = $this->getHomesQuery($filters)
                ->whereHas('province', function ($q) use ($zone) {
                    $q->where('zone', $zone);
                })
                ->when(isset($filters['province']), function ($q) use ($filters) {
                    $province_id = Province::where('code', $filters['province'])->value('id');
                    $q->where('province_id', '!=', $province_id);
                })
                ->whereNotIn('id', $provinceHomesIds)
                ->inRandomOrder()
                ->limit($additionalLimit)
                ->get();
        }
        
        // รวมข้อมูล: จังหวัดที่เลือกก่อน แล้วตามด้วย zone
        return $provinceHomes->concat($zoneHomes);
    }

    private function getHomesQuery(array $filters = [])
    {
        $certified = Arr::get($filters, 'certified');

        $query = NursingHomeProfile::query()
            ->with([
                'owner:id,firstname,lastname,user_type,status,email',
                'province:id,name',
                'district:id,name',
                'subDistrict:id,name',
                'coverImage:id,imageable_id,imageable_type,path,is_cover',
                'images:id,imageable_id,imageable_type,path,is_cover',
                'rates.rate_details',
            ])
            ->withCount(['rates as review_count'])
            ->select(['id', 'user_id', 'name', 'zipcode', 'province_id', 'district_id', 'sub_district_id', 'description', 'cost_per_month', 'facilities', 'private_room_no', 'duo_room_no'])
            ->selectSub(function ($q) {
                $q->from('rate_details')
                ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                ->selectRaw('AVG(rate_details.scores)')
                ->whereColumn('rates.rateable_id', 'nursing_home_profiles.id')
                ->where('rates.rateable_type', NursingHomeProfile::class);
            }, 'average_score')
            ->whereHas('owner', function ($q) {
                $q->where('status', 1);
            })
            ->whereNull('deleted_at');

        $this->applyGridFilters($query, $filters);

        return $query;
    }

    /**
     * Apply grid filters: facilities, cost, room, rate, search
     */
    private function applyGridFilters($query, array $filters)
    {
        $facilities = Arr::get($filters, 'facilities', []);
        $costRanges = Arr::get($filters, 'cost', []);
        $room       = Arr::get($filters, 'room');
        $rate       = Arr::get($filters, 'rate');
        $search     = Arr::get($filters, 'search');

        // Search by name
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Facilities — JSON column contains [{"key":"NURSE_STATION","value":"..."},...]
        if (!empty($facilities) && is_array($facilities)) {
            foreach ($facilities as $facility) {
                $query->whereRaw(
                    "JSON_SEARCH(facilities, 'one', ?, NULL, '\$[*].key') IS NOT NULL",
                    [$facility]
                );
            }
        }

        // Cost — multiple price ranges (OR logic)
        if (!empty($costRanges) && is_array($costRanges)) {
            $query->where(function ($q) use ($costRanges) {
                foreach ($costRanges as $range) {
                    if (str_contains($range, '-')) {
                        [$min, $max] = explode('-', $range);
                        $q->orWhereBetween('cost_per_month', [(int) $min, (int) $max]);
                    } else {
                        // Open-ended range like "16001"
                        $q->orWhere('cost_per_month', '>=', (int) $range);
                    }
                }
            });
        }

        // Room type
        if ($room === 'SINGLE_BED') {
            $query->where('private_room_no', '>', 0);
        } elseif ($room === 'DOUBLE_BED') {
            $query->where('duo_room_no', '>', 0);
        }

        // Rating — minimum average score
        if (!empty($rate) && is_numeric($rate)) {
            $query->having('average_score', '>=', (int) $rate);
        }
    }

    public function getInfo(int $id) 
    {
        $nursingHome = NursingHomeProfile::query()
            ->with([
                'owner:id,firstname,lastname,user_type,status,email',
                'province:id,name',
                'district:id,name',
                'subDistrict:id,name',
                'rates',
                'rates.rate_details:rate_id,scores,scores_for',
                'images:id,imageable_id,imageable_type,path,is_cover',
                'coverImage:id,imageable_id,imageable_type,path,is_cover',
                'staffs'
            ])
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        if (!$nursingHome) {
            return null;
        }

        // รวม rate_details ทั้งหมดสำหรับ global average
        $allDetails = $nursingHome->rates->flatMap->rate_details;
        $nursingHome->global_avg = $allDetails->count() > 0 ? number_format($allDetails->avg('scores'), 2) : null;

        // เพิ่ม avg_scores ให้แต่ละ rate
        $nursingHome->rates->transform(function ($rate) {
            $rateDetails = $rate->rate_details;
            $rate->avg_scores = $rateDetails->count() > 0 ? number_format($rateDetails->avg('scores'), 2) : null;
            return $rate;
        });

        return $nursingHome;
    }

    public function getNursingHomeDataTable(array $filters = [])
    {
        $orderby = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order   = Arr::get($filters, 'order', 'DESC');

        $query = NursingHomeProfile::query()
            ->with([
                'owner:id,firstname,lastname,user_type,status,email',
                'province:id,name',
                'district:id,name',
                'subDistrict:id,name',
                'images:id,imageable_id,imageable_type,path,is_cover', // ✅ polymorphic
                'coverImage:id,imageable_id,imageable_type,path,is_cover', // ✅ polymorphic
                'rates',
                'rates.rate_details:rate_id,scores,scores_for',
            ])
            ->whereNull('deleted_at') // ของ nursing_home_profiles
            ->whereHas('owner', function ($q) {
                $q->where('user_type', 'NURSING_HOME')
                ->where('status', '!=', 0)
                ->whereNull('deleted_at'); // ของ users
            })
            ->select([
                'id', 'user_id', 'name', 'zipcode', 'province_id', 'district_id', 'sub_district_id'
            ]);

        return DataTables::of($query)
            ->addColumn('nursing_home_name', fn($n) => $n->name ?? '-')
            ->addColumn('owner_name', fn($n) => optional($n->owner)->firstname . ' ' . optional($n->owner)->lastname)
            ->addColumn('cover_image', fn($n) => $n->coverImage ? $n->coverImage->full_path : '')
            ->addColumn('average_score', function ($n) {
                $allDetails = $n->rates->flatMap->rate_details;
                return $allDetails->count() > 0
                    ? number_format($allDetails->avg('scores'), 2)
                    : '-';
            })
            ->addColumn('review_count', fn($n) => $n->rates->count())
            ->addColumn('action', fn($n) => '<a href="#" class="text-blue-600 hover:underline">แก้ไข</a>')
            ->rawColumns(['cover_image', 'action'])
            ->orderColumn($orderby, fn($query, $order) => $query->orderBy($orderby, $order))
            ->make(true);
    }

    public function updateNursingHomeData(NursingHomeUpdateRequest  $request, Int $id)
    {
        try {
            $data = $request->validated();
            $user = NursingHomeProfile::findOrFail($id);

            DB::transaction(function () use ($request, $user) {
                if ($user && $user->id) {
                    // email อยู่บนตาราง users (เจ้าของบัญชีจริง) ไม่ใช่ nursing_home_profiles —
                    // เดิม flow นี้ไม่เคยอัปเดต users.email เลย ต้องอัปเดตแยกตรงนี้
                    if ($request->filled('email') && $user->owner) {
                        $user->owner->update(['email' => $request->email]);
                    }

                    $home_service_type = null;

                    if ($request->home_service_type) {
                        $pre_home_service_type = [];
                        $allServices = HomeServiceType::list();
                        foreach ($request->home_service_type as $serviceKey) {
                            if (isset($allServices[$serviceKey])) {
                                $pre_home_service_type[] = [
                                    'key'   => $serviceKey,
                                    'value' => $allServices[$serviceKey],
                                ];
                            }
                        }
                        $home_service_type = json_encode($pre_home_service_type);
                    }

                    $additional_service_type = null;

                    if ($request->additional_service_type) {
                        $pre_additional_service_type = [];
                        $allServices = AdditionalServiceType::list();
                        foreach ($request->additional_service_type as $serviceKey) {
                            if (isset($allServices[$serviceKey])) {
                                $pre_additional_service_type[] = [
                                    'key'   => $serviceKey,
                                    'value' => $allServices[$serviceKey],
                                ];
                            }
                        }
                        $additional_service_type = json_encode($pre_additional_service_type);
                    }

                    $special_facilities = null;
                    if ($request->special_facilities) {
                        $pre_special_facilities = [];
                        $allServices = SpecialFacilityType::list();
                        foreach ($request->special_facilities as $serviceKey) {
                            if (isset($allServices[$serviceKey])) {
                                $pre_special_facilities[] = [
                                    'key'   => $serviceKey,
                                    'value' => $allServices[$serviceKey],
                                ];
                            }
                        }
                        $special_facilities = json_encode($pre_special_facilities);
                    }

                    $facilities = null;
                    if ($request->facilities) {
                        $pre_facilities = [];
                        $allServices = FacilityType::list();
                        foreach ($request->facilities as $serviceKey) {
                            if (isset($allServices[$serviceKey])) {
                                $pre_facilities[] = [
                                    'key'   => $serviceKey,
                                    'value' => $allServices[$serviceKey],
                                ];
                            }
                        }
                        $facilities = json_encode($pre_facilities);
                    }

                    $center_highlights = null;
                    if ($request->center_highlights) {
                        $pre_center_highlights = [];
                        $allServices = CenterHighlightType::list();
                        foreach ($request->center_highlights as $serviceKey) {
                            if (isset($allServices[$serviceKey])) {
                                $pre_center_highlights[] = [
                                    'key'   => $serviceKey,
                                    'value' => $allServices[$serviceKey],
                                ];
                            }
                        }
                        $center_highlights = json_encode($pre_center_highlights);
                    }
                    $profile = NursingHomeProfile::updateOrCreate(
                        ['id' => $user->id], // เงื่อนไขหา record
                        [
                            'name'    => $request->name,
                            'email'   => $request->email ?? $user->owner->email ?? NULL,
                            'description' => $request->description,
                            'main_phone'  => $request->main_phone,
                            'res_phone'   => $request->res_phone,
                            'facebook'    => $request->facebook,
                            'website'     => $request->website,
                            'address'     => $request->address,
                            'license_no'  => $request->license_no,
                            'license_start_date' => $request->license_start_date,
                            'license_exp_date' => $request->license_exp_date,
                            'license_by' => $request->license_by,
                            'certificates' => $request->certificates,
                            'hospital_no' => $request->hospital_no,
                            'manager_name' => $request->manager_name,
                            'graduated' => $request->graduated,
                            'graduated_paper' => $request->graduated_paper,
                            'exp_year' => $request->exp_year,
                            'manager_phone' => $request->manager_phone,
                            'manager_email' => $request->manager_email,
                            'assist_name' => $request->assist_name,
                            'assist_no' => $request->assist_no,
                            'assist_expert' => $request->assist_expert,
                            'assist_phone' => $request->assist_phone,
                            'home_service_type' => $home_service_type,
                            'etc_service' => $request->etc_service,
                            'additional_service_type' => $additional_service_type,
                            'building_no' => $request->building_no ?? 0,
                            'total_room' => $request->total_room ?? 0,
                            'private_room_no' => $request->private_room_no ?? 0,
                            'duo_room_no' => $request->duo_room_no ?? 0,
                            'shared_room_three_beds' => $request->shared_room_three_beds ?? 0,
                            'max_serve_no' => $request->max_serve_no ?? 0,
                            'area' => $request->area ?? 0,
                            'special_facilities' => $special_facilities,
                            'facilities' => $facilities,
                            'ambulance' => $request->ambulance ?? 0,
                            'ambulance_amount' => $request->ambulance_amount ?? 0,
                            'van_shuttle' => $request->van_shuttle ?? 0,
                            'special_medical_equipment' => $request->special_medical_equipment ?? NULL,
                            'total_staff' => $request->total_staff ?? 0,
                            'total_fulltime_nurse' => $request->total_fulltime_nurse ?? 0,
                            'total_parttime_nurse' => $request->total_parttime_nurse ?? 0,
                            'total_nursing_assistant' => $request->total_nursing_assistant ?? 0,
                            'total_regular_doctor' => $request->total_regular_doctor ?? 0,
                            'total_physical_therapist' => $request->total_physical_therapist ?? 0,
                            'total_pharmacist' => $request->total_pharmacist ?? 0,
                            'total_nutritionist' => $request->total_nutritionist ?? 0,
                            'total_social_worker' => $request->total_social_worker ?? 0,
                            'total_general_employees' => $request->total_general_employees ?? 0,
                            'total_security_officer' => $request->total_security_officer ?? 0,
                            'cost_per_day' => $request->cost_per_day ?? 0,
                            'cost_per_month' => $request->cost_per_month ?? 0,
                            'deposit' => $request->deposit ?? 0,
                            'registration_fee' => $request->registration_fee ?? 0,
                            'special_food_expenses' => $request->special_food_expenses ?? 0,
                            'physical_therapy_fee' => $request->physical_therapy_fee ?? 0,
                            'delivery_fee' => $request->delivery_fee ?? 0,
                            'laundry_service' => $request->laundry_service ?? 0,
                            'social_security' => $request->social_security ?? 0,
                            'private_health_insurance' => $request->private_health_insurance ?? 0,
                            'installment' => $request->installment ?? 0,
                            'payment_methods' => $request->payment_methods ?? NULL,
                            'center_highlights' => $center_highlights,
                            'patients_target' => $request->patients_target,
                            'visiting_time' => $request->visiting_time,
                            'patient_admission_policy' => $request->patient_admission_policy,
                            'emergency_contact_information' => $request->emergency_contact_information,
                            'additional_notes' => $request->additional_notes,
                            'sub_district_id' => $request->sub_district_id,
                            'district_id' => $request->district_id,
                            'province_id' => $request->province_id,
                            'zipcode' => $request->zipcode,
                            'certified' => $request->has('certified') ? 1 : 0,
                            'youtube_url' => $request->youtube_url,
                            // ฟิลด์ map (ลิงก์แชร์) ถูกปิดออกจากฟอร์มแล้ว (ใช้ map_embed ทางเดียว) —
                            // เก็บค่าเดิมไว้เฉยๆ กันข้อมูลเก่าหายถ้ามี ไม่ได้ตั้งใจให้แก้ไขได้อีกแล้ว
                            'map' => $request->map ?? $user->map,
                            'map_embed' => $request->map_embed ?? NULL,
                        ]
                    );

                    if ($request->hasFile('profiles')) {
                        // ✅ เช็คว่าผู้ใช้นี้มีภาพอยู่แล้วหรือไม่
                        $hasExistingImages = $user->images()->exists();

                        // ถ้าไม่มีภาพเดิมเลย → รูปแรกจะเป็น cover
                        $first = !$hasExistingImages;

                        foreach ($request->file('profiles') as $file) {
                            if ($file->isValid()) {
                                $extension = $file->getClientOriginalExtension();
                                $hashedName = md5(uniqid($user->id, true)) . '.' . $extension;
                                $destPath = 'images/' . $hashedName;
                                $destFullPath = public_path($destPath);

                                File::ensureDirectoryExists(dirname($destFullPath));
                                File::copy($file->getRealPath(), $destFullPath);

                                $user->images()->create([
                                    'name'          => $file->getClientOriginalName(),
                                    'path'          => $destPath,
                                    'type'          => 'NURSING_HOME',
                                    'filetype'      => $file->getClientMimeType(),
                                    'is_cover'      => $first,
                                    'imageable_id'  => $profile->id,
                                    'imageable_type' => 'App\Models\NursingHomeProfile',
                                    'user_id' => $profile->user_id
                                ]);

                                // ✅ ถ้ามีภาพเดิมแล้ว → รูปใหม่ทั้งหมดเป็น false
                                // ✅ ถ้าไม่มีภาพเดิม → รูปแรก true, รูปต่อไป false
                                $first = false;
                            }
                        }
                    }
                }
            });

            return [
                'status'      => 'success',
                'status_code' => 200,
                'message'     => 'Profiles uploaded successfully'
            ];

        } catch (ModelNotFoundException $e) {
            return [
                'status'      => 'error',
                'status_code' => 404,
                'errors'      => [
                    'message' => 'User not found',
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'status'      => 'error',
                'status_code' => 500,
                'errors'      => [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ],
            ];
        }
    }

    public function createStaff(NursingHomeCreateStaffRequest $request, Int $id)
    {
        try {
            $data = $request->validated();
            $user = NursingHomeProfile::findOrFail($id);
            DB::transaction(function () use ($request, $user) {
                if ($user && $user->id) {
                    $staff = NursingHomeStaff::create(
                        [
                            'user_id' => $user->id,
                            'name' => $request->name,
                            'responsibility' => $request->responsibility,
                        ]
                    );

                    if ($request->hasFile('image')) {
                        $image = $request->image;

                        if ($image->isValid()) {
                            $filename = time() . '_' . $image->getClientOriginalName();

                            $sourcePath = $image->getRealPath();
                            $extension = $image->getClientOriginalExtension();
                            $hashedName = md5(uniqid($user->id, true)) . '.' . $extension;
                            $destPath = 'images/' . $hashedName;
                            $destFullPath = public_path($destPath);

                            File::ensureDirectoryExists(dirname($destFullPath));
                            File::copy($sourcePath, $destFullPath);

                            $staff->image = $destPath;
                            $staff->save();
                        }
                    }
                }
            });

            return [
                'status'      => 'success',
                'status_code' => 200,
                'message'     => 'Staff uploaded successfully'
            ];

        } catch (ModelNotFoundException $e) {
            return [
                'status'      => 'error',
                'status_code' => 404,
                'errors'      => [
                    'message' => 'User not found',
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'status'      => 'error',
                'status_code' => 500,
                'errors'      => [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ],
            ];
        }
    }

    /**
     * id => user_id
     */
    public function getProfile(int $id)
    {
        return User::query()
            ->with([
                // ดึงข้อมูลบ้านพักคนชรา (Nursing Homes)
                'nursingHomes' => function ($query) {
                    $query->with([
                        'province:id,name',
                        'district:id,name',
                        'subDistrict:id,name',
                        'images',
                        'coverImage',
                        'staffs',
                        'rates',
                        'rooms',
                    ]);
                },
            ])
            ->where('id', $id)
            ->first();
    }

    public function getProfiles(int $id)
    {
        return NursingHomeProfile::query()
            ->with([
                'province:id,name',
                'district:id,name',
                'subDistrict:id,name',
                'coverImage:id,imageable_id,imageable_type,path,is_cover',
                'images:id,imageable_id,imageable_type,path,is_cover',
                'rates.rate_details',
                'licenses'
            ])
            ->withCount(['rates as review_count'])
            //->select(['id', 'user_id', 'name', 'zipcode', 'province_id', 'district_id', 'sub_district_id', 'description'])
            ->selectSub(function ($q) {
                $q->from('rate_details')
                ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                ->selectRaw('AVG(rate_details.scores)')
                ->whereColumn('rates.rateable_id', 'nursing_home_profiles.id')
                ->where('rates.rateable_type', NursingHomeProfile::class);
            }, 'average_score')
            ->where('user_id', $id)
            ->whereNull('deleted_at')
            ->get();
    }

    public function updateProfile(array $input, Int $user_id)
    {
        $profileId = Arr::get($input, 'id');

        $user = NursingHome::findOrFail($user_id);

        $input['email'] = $user->email ?? NULL;
        $input = collect($input)
            ->reject(function ($value) {
                // ถ้าเป็น array ให้ข้าม ไม่ต้อง trim
                if (is_array($value)) {
                    return false;
                }

                return is_null($value) || trim($value) === '';
            })
            ->toArray();

        $home_service_type = null;
        if ($input['home_service_type']) {
            $pre_home_service_type = [];
            $allServices = HomeServiceType::list();
            foreach ($input['home_service_type'] as $serviceKey) {
                if (isset($allServices[$serviceKey])) {
                    $pre_home_service_type[] = [
                        'key'   => $serviceKey,
                        'value' => $allServices[$serviceKey],
                    ];
                }
            }
            $home_service_type = json_encode($pre_home_service_type);
        }

        $additional_service_type = null;
        if ($input['additional_service_type']) {
            $pre_additional_service_type = [];
            $allServices = AdditionalServiceType::list();
            foreach ($input['additional_service_type'] as $serviceKey) {
                if (isset($allServices[$serviceKey])) {
                    $pre_additional_service_type[] = [
                        'key'   => $serviceKey,
                        'value' => $allServices[$serviceKey],
                    ];
                }
            }
            $additional_service_type = json_encode($pre_additional_service_type);
        }

        $special_facilities = null;
        if ($input['special_facilities']) {
            $pre_special_facilities = [];
            $allServices = SpecialFacilityType::list();
            foreach ($input['special_facilities'] as $serviceKey) {
                if (isset($allServices[$serviceKey])) {
                    $pre_special_facilities[] = [
                        'key'   => $serviceKey,
                        'value' => $allServices[$serviceKey],
                    ];
                }
            }
            $special_facilities = json_encode($pre_special_facilities);
        }

        $facilities = null;
        if ($input['facilities']) {
            $pre_facilities = [];
            $allServices = FacilityType::list();
            foreach ($input['facilities'] as $serviceKey) {
                if (isset($allServices[$serviceKey])) {
                    $pre_facilities[] = [
                        'key'   => $serviceKey,
                        'value' => $allServices[$serviceKey],
                    ];
                }
            }
            $facilities = json_encode($pre_facilities);
        }

        $center_highlights = null;
        if ($input['center_highlights']) {
            $pre_center_highlights = [];
            $allServices = CenterHighlightType::list();
            foreach ($input['center_highlights'] as $serviceKey) {
                if (isset($allServices[$serviceKey])) {
                    $pre_center_highlights[] = [
                        'key'   => $serviceKey,
                        'value' => $allServices[$serviceKey],
                    ];
                }
            }
            $center_highlights = json_encode($pre_center_highlights);
        }

        if ($profileId) {
            $profile = NursingHomeProfile::where('id', $profileId)
                ->where('user_id', $user_id)
                ->first();

            if ($profile) {
                $input['home_service_type'] = $home_service_type;
                $input['additional_service_type'] = $additional_service_type;
                $input['special_facilities'] = $special_facilities;
                $input['facilities'] = $facilities;
                $input['center_highlights'] = $center_highlights;
                $profile->update($input);
                return $profile;
            } else {
                throw new \Exception('Profile not found for this user.');
            }
        } else {
            $input['user_id'] = $user_id;
            return NursingHomeProfile::create($input);
        }
    }

    public function getProfileById(int $user_id, int $profile_id)
    {
        return NursingHomeProfile::query()
            ->with(['coverImage', 'images', 'licenses', 'staffs', 'province', 'district', 'subDistrict'])
            ->where('user_id', $user_id)
            ->where('id', $profile_id)
            ->firstOrFail();
    }

    public function updateGeneralProfile(int $user_id, array $inputs)
    {
        $profile = NursingHomeProfile::findOrFail(Arr::get($inputs, 'profile_id'));
        $profile->name = Arr::get($inputs, 'name');
        $profile->email = Arr::get($inputs, 'email');
        $profile->main_phone = Arr::get($inputs, 'main_phone');
        $profile->res_phone  = Arr::get($inputs, 'res_phone');
        $profile->facebook   = Arr::get($inputs, 'facebook');
        $profile->website    = Arr::get($inputs, 'website');
        $profile->address    = Arr::get($inputs, 'address');
        $profile->province_id= Arr::get($inputs, 'province_id');
        $profile->district_id= Arr::get($inputs, 'district_id');
        $profile->sub_district_id = Arr::get($inputs, 'sub_district_id');
        $profile->map_show   = Arr::get($inputs, 'map_show');
        $profile->update();
        return $profile;
    }

    public function updateAboutProfile(int $user_id, array $inputs, array $files = [], array $staffAvatars = [])
    {
        $profile = NursingHomeProfile::findOrFail(Arr::get($inputs, 'profile_id'));
        $profile->license_no = Arr::get($inputs, 'license_no');
        $profile->license_start_date = Arr::get($inputs, 'license_start_date');
        $profile->license_exp_date   = Arr::get($inputs, 'license_exp_date');
        $profile->license_by = Arr::get($inputs, 'license_by');
        $profile->certificates = Arr::get($inputs, 'certificates');
        $profile->hospital_no  = Arr::get($inputs, 'hospital_no');
        $profile->cost_per_day = Arr::get($inputs, 'cost_per_day');
        $profile->cost_per_month = Arr::get($inputs, 'cost_per_month');
        $profile->deposit = Arr::get($inputs, 'deposit');
        $profile->registration_fee = Arr::get($inputs, 'registration_fee');
        $profile->special_food_expenses = Arr::get($inputs, 'special_food_expenses');
        $profile->physical_therapy_fee  = Arr::get($inputs, 'physical_therapy_fee');
        $profile->delivery_fee = Arr::get($inputs, 'delivery_fee');
        $profile->laundry_service = Arr::get($inputs, 'laundry_service');
        $profile->social_security = Arr::get($inputs, 'social_security');
        $profile->private_health_insurance = Arr::get($inputs, 'private_health_insurance');
        $profile->installment = Arr::get($inputs, 'installment');
        $profile->payment_methods = Arr::get($inputs, 'payment_methods');
        $profile->update();

        // Handle license file uploads
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
        foreach ($files as $file) {
            if (!$file->isValid()) continue;
            if (!in_array($file->getMimeType(), $allowedMimes)) continue;
            if ($file->getSize() > 5 * 1024 * 1024) continue;

            $extension  = $file->getClientOriginalExtension();
            $hashedName = md5(uniqid($profile->id, true)) . '.' . $extension;
            $destPath   = 'images/nursing-home/' . $profile->id . '/license/' . $hashedName;
            $destFull   = public_path($destPath);

            File::ensureDirectoryExists(dirname($destFull));
            File::copy($file->getRealPath(), $destFull);

            $profile->licenses()->create([
                'filename' => $file->getClientOriginalName(),
                'filetype' => $file->getMimeType(),
                'path'     => $destPath,
            ]);
        }

        // Staff sync: delete all existing, re-create from submitted data
        $staffsData = Arr::pull($inputs, 'staffs', []);

        // Collect existing_image paths that will be preserved
        $preserveImages = [];
        foreach ($staffsData as $item) {
            if (!empty($item['existing_image'])) {
                $preserveImages[] = $item['existing_image'];
            }
        }

        // Delete old staff images from disk (skip images that will be reused)
        $oldStaffs = NursingHomeStaff::where('nursing_home_profile_id', $profile->id)->get();
        foreach ($oldStaffs as $old) {
            if ($old->image && !in_array($old->image, $preserveImages) && File::exists(public_path($old->image))) {
                File::delete(public_path($old->image));
            }
        }
        NursingHomeStaff::where('nursing_home_profile_id', $profile->id)->delete();

        foreach ($staffsData as $index => $item) {
            $name = $item['name'] ?? '';
            if (empty(trim($name))) continue;

            $staff = NursingHomeStaff::create([
                'user_id'                  => $user_id,
                'nursing_home_profile_id'  => $profile->id,
                'name'           => $name,
                'responsibility' => $item['position'] ?? '',
            ]);

            // Handle avatar
            if (isset($staffAvatars[$index]) && $staffAvatars[$index]->isValid()) {
                $file = $staffAvatars[$index];
                $ext = $file->getClientOriginalExtension();
                $hashed = md5(uniqid($staff->id, true)) . '.' . $ext;
                $destPath = 'images/nursing-home/' . $profile->id . '/staff/' . $hashed;
                File::ensureDirectoryExists(dirname(public_path($destPath)));
                File::copy($file->getRealPath(), public_path($destPath));
                $staff->image = $destPath;
                $staff->save();
            } elseif (!empty($item['existing_image'])) {
                // Preserve existing image path (sent from frontend for staff that weren't re-uploaded)
                $staff->image = $item['existing_image'];
                $staff->save();
            }
        }

        return $profile->load(['licenses', 'staffs']);
    }

    public function updateMoreInfoProfile(int $user_id, array $inputs, array $files = [])
    {
        $profile = NursingHomeProfile::findOrFail(Arr::get($inputs, 'profile_id'));
        $profile->description = Arr::get($inputs, 'about') ?? '';
        $profile->youtube_url = Arr::get($inputs, 'youtube_url') ?? NULL;
        $profile->etc_service = Arr::get($inputs, 'etc_services') ?? '';
        $home_service_type = null;
        if (Arr::get($inputs, 'home_service_type')) {
            $pre_home_service_type = [];
            $allServices = HomeServiceType::list();
            foreach (Arr::get($inputs, 'home_service_type') as $serviceKey) {
                if (isset($allServices[$serviceKey])) {
                    $pre_home_service_type[] = [
                        'key'   => $serviceKey,
                        'value' => $allServices[$serviceKey],
                    ];
                }
            }
            $home_service_type = json_encode($pre_home_service_type);
        }
        $profile->home_service_type = $home_service_type ?? NULL;

        $additional_service_type = null;
        if (Arr::get($inputs, 'additional_service_type')) {
            $pre_additional_service_type = [];
            $allServices = AdditionalServiceType::list();
            foreach (Arr::get($inputs, 'additional_service_type') as $serviceKey) {
                if (isset($allServices[$serviceKey])) {
                    $pre_additional_service_type[] = [
                        'key'   => $serviceKey,
                        'value' => $allServices[$serviceKey],
                    ];
                }
            }
            $additional_service_type = json_encode($pre_additional_service_type);
        }
        $profile->additional_service_type = $additional_service_type ?? NULL;

        $special_facilities = null;
        if (Arr::get($inputs, 'special_facilities')) {
            $pre_special_facilities = [];
            $allServices = SpecialFacilityType::list();
            foreach (Arr::get($inputs, 'special_facilities') as $serviceKey) {
                if (isset($allServices[$serviceKey])) {
                    $pre_special_facilities[] = [
                        'key'   => $serviceKey,
                        'value' => $allServices[$serviceKey],
                    ];
                }
            }
            $special_facilities = json_encode($pre_special_facilities);
        }
        $profile->special_facilities = $special_facilities ?? NULL;

        $facilities = null;
        if (Arr::get($inputs, 'facilities')) {
            $pre_facilities = [];
            $allServices = FacilityType::list();
            foreach (Arr::get($inputs, 'facilities') as $serviceKey) {
                if (isset($allServices[$serviceKey])) {
                    $pre_facilities[] = [
                        'key'   => $serviceKey,
                        'value' => $allServices[$serviceKey],
                    ];
                }
            }
            $facilities = json_encode($pre_facilities);
        }
        $profile->facilities = $facilities ?? NULL;

        $center_highlights = null;
        if (Arr::get($inputs, 'center_highlights')) {
            $pre_center_highlights = [];
            $allServices = CenterHighlightType::list();
            foreach (Arr::get($inputs, 'center_highlights') as $serviceKey) {
                if (isset($allServices[$serviceKey])) {
                    $pre_center_highlights[] = [
                        'key'   => $serviceKey,
                        'value' => $allServices[$serviceKey],
                    ];
                }
            }
            $center_highlights = json_encode($pre_center_highlights);
        }
        $profile->center_highlights = $center_highlights ?? NULL;

        $profile->building_no = Arr::get($inputs, 'building_no') ?? 0;
        $profile->total_room = Arr::get($inputs, 'total_room') ?? 0;
        $profile->private_room_no = Arr::get($inputs, 'private_room_no') ?? 0;
        $profile->duo_room_no = Arr::get($inputs, 'duo_room_no') ?? 0;
        $profile->shared_room_three_beds = Arr::get($inputs, 'shared_room_three_beds') ?? 0;
        $profile->max_serve_no = Arr::get($inputs, 'max_serve_no') ?? 0;
        $profile->area = Arr::get($inputs, 'area') ?? 0;
        $profile->ambulance = Arr::get($inputs, 'ambulance') ?? 0;
        $profile->ambulance_amount = Arr::get($inputs, 'ambulance_amount') ?? 0;
        $profile->van_shuttle = Arr::get($inputs, 'van_shuttle') ?? 0;
        $profile->special_medical_equipment = Arr::get($inputs, 'special_medical_equipment') ?? '';
        $profile->update();

        // -------------------------------------------------------
        // จัดการ detail_images + image_order (reorder)
        // -------------------------------------------------------
        $imageOrder = Arr::get($inputs, 'image_order');
        $hasNewFiles = !empty($files);

        if ($imageOrder) {
            $order = json_decode($imageOrder, true) ?? [];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

            // Save new files first, map index → new Image id
            $newImageIds = [];
            if ($hasNewFiles) {
                foreach ($files as $idx => $file) {
                    if (!$file->isValid()) continue;
                    if (!in_array($file->getMimeType(), $allowedMimes)) continue;
                    if ($file->getSize() > 5 * 1024 * 1024) continue;

                    $extension    = $file->getClientOriginalExtension();
                    $hashedName   = md5(uniqid($profile->id, true)) . '.' . $extension;
                    $destPath     = 'images/nursing-home/' . $profile->id . '/detail/' . $hashedName;
                    $destFullPath = public_path($destPath);

                    File::ensureDirectoryExists(dirname($destFullPath));
                    File::copy($file->getRealPath(), $destFullPath);

                    $newImage = Image::create([
                        'user_id'        => $user_id,
                        'name'           => $file->getClientOriginalName(),
                        'path'           => $destPath,
                        'filetype'       => $file->getMimeType(),
                        'is_cover'       => false,
                        'type'           => 'NURSING_HOME_DETAIL',
                        'imageable_id'   => $profile->id,
                        'imageable_type' => 'App\Models\NursingHomeProfile',
                    ]);
                    $newImageIds[$idx] = $newImage->id;
                }
            }

            // Apply order: position 0 = cover, rest = not cover
            foreach ($order as $pos => $entry) {
                $imageId = null;
                if (($entry['type'] ?? '') === 'existing') {
                    $imageId = $entry['id'] ?? null;
                } elseif (($entry['type'] ?? '') === 'new') {
                    $imageId = $newImageIds[$entry['index'] ?? -1] ?? null;
                }
                if ($imageId) {
                    Image::where('id', $imageId)
                        ->where('imageable_id', $profile->id)
                        ->where('imageable_type', 'App\Models\NursingHomeProfile')
                        ->update(['is_cover' => $pos === 0]);
                }
            }

        } elseif ($hasNewFiles) {
            // No order info but new files — legacy behavior
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

            $hasExistingImages = Image::where('imageable_id', $profile->id)
                                    ->where('imageable_type', 'App\Models\NursingHomeProfile')
                                    ->exists();

            $first = !$hasExistingImages;

            foreach ($files as $file) {
                if (!$file->isValid()) continue;
                if (!in_array($file->getMimeType(), $allowedMimes)) continue;
                if ($file->getSize() > 5 * 1024 * 1024) continue;

                $extension    = $file->getClientOriginalExtension();
                $hashedName   = md5(uniqid($profile->id, true)) . '.' . $extension;
                $destPath     = 'images/nursing-home/' . $profile->id . '/detail/' . $hashedName;
                $destFullPath = public_path($destPath);

                File::ensureDirectoryExists(dirname($destFullPath));
                File::copy($file->getRealPath(), $destFullPath);

                if ($first) {
                    Image::where('imageable_id', $profile->id)
                        ->where('imageable_type', 'App\Models\NursingHomeProfile')
                        ->where('is_cover', true)
                        ->update(['is_cover' => false]);
                }

                Image::create([
                    'user_id'        => $user_id,
                    'name'           => $file->getClientOriginalName(),
                    'path'           => $destPath,
                    'filetype'       => $file->getMimeType(),
                    'is_cover'       => $first,
                    'type'           => 'NURSING_HOME_DETAIL',
                    'imageable_id'   => $profile->id,
                    'imageable_type' => 'App\Models\NursingHomeProfile',
                ]);

                $first = false;
            }
        }
        return $profile->load(['coverImage', 'images']);
    }

    public function getProfileByCollections(array $filters)                                                                                                                                                                                                             
    {
      $clauseMap = [                                                                                                                                                                                                                                                            'owner_by'   => 'user_id',
          'managed_by' => 'manager_id',                                                                                                                                                                                                                               
          'created_by' => 'created_by',
      ];

      $provinceId = $filters['province_id'] ?? null;
      $clause     = $filters['clause'] ?? null;
      $ids        = $filters['ids'] ?? [];

      // หา zone จาก province_id แล้วดึง province_id ทั้งหมดใน zone เดียวกัน
      $zoneProvinceIds = [];
      if ($provinceId) {
          $zone = Province::where('id', $provinceId)->value('zone');

          if ($zone) {
              $zoneProvinceIds = Province::where('zone', $zone)->pluck('id')->toArray();
          }
      }

      $query = NursingHomeProfile::query()
          ->with([
              'province:id,name',
              'district:id,name',
              'rates.rate_details',
              'coverImage'
          ]);

      // ต้องตรง ids AND อยู่ใน zone เดียวกัน
      if ($clause && isset($clauseMap[$clause]) && !empty($ids)) {
          $column = $clauseMap[$clause];
          $query->whereIn($column, $ids);
      }

      if (!empty($zoneProvinceIds)) {
          $query->whereIn('province_id', $zoneProvinceIds);
      }

      return $query
          ->inRandomOrder()
          ->limit(8)
          ->get();
    }

}
