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

class NursingHomeRepository
{
    public function getNursingHomes(array $filters = [])
    {
        $query = NursingHome::query()
        ->with([
            'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,cost_per_day,cost_per_month,home_service_type,special_facilities,facilities,certified',
            'profile.province:id,name',
            'profile.district:id,name',
            'profile.subDistrict:id,name',
            'images:user_id,path,is_cover',
            'coverImage:user_id,path,is_cover',
            'rates',
            'rates.rate_details:rate_id,scores,scores_for',
        ])
        ->withCount('rates as review_count') // จำนวนรีวิว
        ->addSelect('users.*') // เลือกทุกคอลัมน์จาก users
        ->selectSub(function ($q) {
            $q->from('rate_details')
            ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
            ->selectRaw('AVG(rate_details.scores)')
            ->whereColumn('rates.user_id', 'users.id');
        }, 'average_score')
        ->whereNull('deleted_at')
        ->where('status', '!=', 0)
        ->where('user_type', 'NURSING_HOME');

        if (!empty($filters['limit']) && is_numeric($filters['limit'])) {
            $query->limit((int) $filters['limit']);
        }


        if (isset($filters['certified'])) {
            $certified = (int) $filters['certified'];
            $query->whereHas('profile', function ($q) use ($certified) {
                $q->where('certified', $certified);
            });
        }

        return $query->get();
    }

    public function getNuringHomePagination(array $filters = [])
    {
        $orderby = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order   = Arr::get($filters, 'order', 'DESC');
        $limit = Arr::get($filters, 'limit', 8); 
        $certified = Arr::get($filters, 'certified');

        $query = NursingHome::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,cost_per_day,cost_per_month,home_service_type,special_facilities,facilities,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover',
                'rates',
                'rates.rate_details:rate_id,scores,scores_for',
            ])
            ->withCount('rates as review_count') // จำนวนรีวิว
            ->addSelect(['users.id'])
            ->selectSub(function ($q) {
            $q->from('rate_details')
                ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                ->selectRaw('AVG(rate_details.scores)')
                ->whereColumn('rates.user_id', 'users.id');
            }, 'average_score')
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('user_type', 'NURSING_HOME');

        // Filter certified
        if (array_key_exists('certified', $filters)) {
            if (is_null($certified) || $certified === 'null') {
                $query->whereHas('profile', fn($q) => $q->whereIn('certified', [0, 1]));
            } else {
                $certified = filter_var($certified, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $query->whereHas('profile', fn($q) => $q->where('certified', $certified ? 1 : 0));
            }
        }

        // Filter province
        if(isset($filters['province'])) {
            $province_id = Province::where('code', $filters['province'])->value('id');
            $query->whereHas('profile', fn($q) => $q->where('province_id', $province_id));
        }

        return $query->orderBy($orderby, $order)->paginate($limit);
    }

    public function getInfo(int $id) 
    {
        $nursingHome = NursingHome::query()
            ->with([
                'profile',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'rates',
                'rates.rate_details:rate_id,scores,scores_for',
                'images:id,user_id,path,is_cover',
                'coverImage:id,user_id,path,is_cover'
            ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('id', $id)
            ->where('user_type', 'NURSING_HOME')
            ->first();

        if (!$nursingHome) {
            return null;
        }

        // รวม rate_details ทั้งหมดสำหรับ global average
        $allDetails = $nursingHome->rates->flatMap->rate_details;
        $nursingHome->global_avg = $allDetails->avg('scores');

        // เพิ่ม avg_scores ให้แต่ละ rate
        $nursingHome->rates->transform(function ($rate) {
            $rateDetails = $rate->rate_details;
            $rate->avg_scores = $rateDetails->avg('scores');
            return $rate;
        });

        return $nursingHome;
    }


    public function getNursingHomeDataTable(array $filters = [])
    {
        $orderby = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order   = Arr::get($filters, 'order', 'DESC');

        $query = NursingHome::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,cost_per_day,cost_per_month,home_service_type,special_facilities,facilities,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover',
                'rates',
                'rates.rate_details:rate_id,scores,scores_for',
            ])
            ->select(['users.id'])
            ->whereNull('users.deleted_at')
            ->where('users.status', '!=', 0)
            ->where('users.user_type', 'NURSING_HOME');

        return DataTables::of($query)
            ->addColumn('name', fn($n) => optional($n->profile)->name ?? '-')
            ->addColumn('cover_image', fn($n) => $n->coverImage ? $n->coverImage->full_path : '')
            ->addColumn('average_score', function ($n) {
                $allDetails = $n->rates->flatMap->rate_details; // รวม rate_details ทั้งหมด
                return $allDetails->count() > 0 
                    ? number_format($allDetails->avg('scores'), 2) 
                    : '-';
            })
            ->addColumn('review_count', function ($n) {
                return $n->rates->count();
            })
            ->addColumn('action', fn($n) => '<a href="#" class="text-blue-600 hover:underline">แก้ไข</a>')
            ->rawColumns(['cover_image', 'action'])
            ->orderColumn($orderby, fn($query, $order) => $query->orderBy($orderby, $order))
            ->make(true);
    }


    public function updateNursingHomeData(NursingHomeUpdateRequest  $request, Int $id)
    {
        try {
            $data = $request->validated();
            $user = User::findOrFail($id);
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
                        $allServices = SpecialFacilityType::list();
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
                        ['user_id' => $user->id], // เงื่อนไขหา record
                        [
                            'name'    => $request->name,
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
                        foreach ($request->file('profiles') as $file) {
                            if ($file->isValid()) {
                                $filename = time() . '_' . $file->getClientOriginalName();

                                $sourcePath = $file->getRealPath();
                                $extension = $file->getClientOriginalExtension();
                                $hashedName = md5(uniqid($user->id, true)) . '.' . $extension;
                                $destPath = 'images/' . $hashedName;
                                $destFullPath = public_path($destPath);

                                File::ensureDirectoryExists(dirname($destFullPath));
                                File::copy($sourcePath, $destFullPath);

                                Image::create([
                                    'user_id'   => $user->id,
                                    'type'      => 'NURSING_HOME',
                                    'name'      => $filename,
                                    'path'      => $destPath,
                                    'filetype'  => $file->getClientMimeType(),
                                    'is_cover'  => 0,
                                ]);
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
            $user = User::findOrFail($id);
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
}
