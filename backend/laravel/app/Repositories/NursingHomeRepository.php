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
                'owner:id,firstname,lastname,user_type,status',
                'province:id,name',
                'district:id,name',
                'subDistrict:id,name',
                'images',        // polymorphic
                'coverImage',    // polymorphic
                'rates.rate_details',
            ])
            ->withCount(['rates as review_count'])
            ->select(['id', 'user_id', 'name', 'zipcode', 'province_id', 'district_id', 'sub_district_id', 'description', 'cost_per_month'])
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

    public function getNursingHomePagination(array $filters = [])
    {
        $orderby   = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order     = Arr::get($filters, 'order', 'DESC');
        $limit     = Arr::get($filters, 'limit', 8); 
        $certified = Arr::get($filters, 'certified');

        $query = NursingHomeProfile::query()
            ->with([
                'owner:id,firstname,lastname,user_type,status',
                'province:id,name',
                'district:id,name',
                'subDistrict:id,name',
                'coverImage:id,imageable_id,imageable_type,path,is_cover',
                'images:id,imageable_id,imageable_type,path,is_cover',
                'rates.rate_details',
            ])
            ->withCount(['rates as review_count'])
            ->select(['id', 'user_id', 'name', 'zipcode', 'province_id', 'district_id', 'sub_district_id', 'description'])
            ->selectSub(function ($q) {
                $q->from('rate_details')
                ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                ->selectRaw('AVG(rate_details.scores)')
                ->whereColumn('rates.rateable_id', 'nursing_home_profiles.id')
                ->where('rates.rateable_type', NursingHomeProfile::class);
            }, 'average_score')
            ->whereNull('deleted_at');

        // Filter certified
        if (!is_null($certified)) {
            $certifiedBool = filter_var($certified, FILTER_VALIDATE_BOOLEAN);
            $query->where('certified', $certifiedBool ? 1 : 0);
        }

        // Filter province
        if (isset($filters['province'])) {
            $province_id = Province::where('code', $filters['province'])->value('id');
            $query->where('province_id', $province_id);
        }

        return $query->orderBy($orderby, $order)->paginate($limit);
    }

    public function getInfo(int $id) 
    {
        $nursingHome = NursingHomeProfile::query()
            ->with([
                'owner:id,firstname,lastname,user_type,status',
                'rates',
                'rates.rate_details:rate_id,scores,scores_for',
                'images:id,imageable_id,imageable_type,path,is_cover',
                'coverImage:id,imageable_id,imageable_type,path,is_cover',
                'staffs',
                'licenses'
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
                'owner:id,firstname,lastname,user_type,status',
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

                    NursingHomeProfile::updateOrCreate(
                        ['id' => $user->id], // เงื่อนไขหา record
                        [
                            'name'    => $request->name,
                            'email'   => $user->email ?? NULL,
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
                            'center_highlights' => $request->center_highlights,
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
                            'map' => $request->map,
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
                                    'imageable_id'  => $user->id,
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

}
