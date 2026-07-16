<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\NursingHomeRepository;
use App\Http\Requests\NursingHomeCreateRequest;
use App\Http\Requests\NursingHomeRegisterRequest;
use App\Models\User;
use App\Models\NursingHome;
use App\Models\NursingHomeProfile;
use App\Models\Image;
use App\Models\NursingHomeLicenseImage;
use App\Models\NursingHomeStaff;
use App\Enums\UserType;
use App\Enums\HomeServiceType;
use App\Enums\AdditionalServiceType;
use App\Enums\SpecialFacilityType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use App\Http\Resources\NursingHomeProfileCompareResource;
use App\Http\Requests\updateGeneralProfileUpdateRequest;
use App\Http\Requests\updateMoreInfoProfileRequest;
use App\Http\Requests\updateAboutProfileRequest;
use App\Services\NursingHome\NursingHomeService;
use App\Http\Resources\NursingHomeProfileResource;

class NursingHomeController extends Controller {
    protected $nursing_home_repository;
    protected $service;

    public function __construct(NursingHomeRepository $nursing_home_repository, NursingHomeService $service)
    {
        $this->nursing_home_repository = $nursing_home_repository;
        $this->service = $service;
    }

    public function getNursingHomes(Request $request)
    {
        $limit = $request->input('limit');
        $certified = $request->input('certified');

        $nursings = $this->nursing_home_repository->getNursingHomes(['limit' => $limit, 'certified' => $certified]);
        return response()->json($nursings);
    }

    public function getNuringHomePagination(Request $request)
    {
        $limit = $request->input('limit', 8);
        $certified = $request->input('certified');
        $orderby  = $request->input('order_by');
        $order     = $request->input('order');
        $province  = $request->input('province');
        $zone      = $request->input('zone');
        $page      = $request->input('page', 1);

        // Filter params
        $facilities = $request->input('facilities', []);
        $cost       = $request->input('cost', []);
        $room       = $request->input('room');
        $rate       = $request->input('rate');
        $search     = $request->input('search');

        $filterParams = [
            'certified' => $certified,
            'orderby' => $orderby ?? 'id',
            'order' => $order ?? 'DESC',
            'province' => $province,
            'zone' => $zone,
            'facilities' => $facilities,
            'cost' => $cost,
            'room' => $room,
            'rate' => $rate,
            'search' => $search,
        ];

        // นับจำนวนทั้งหมดในจังหวัดก่อน
        $provinceTotal = $this->nursing_home_repository->countByProvince($filterParams);

        // ถ้าจังหวัดมีข้อมูล > 10 ใช้ pagination ปกติ
        if ($provinceTotal > 10) {
            $homes = $this->nursing_home_repository->getNursingHomePagination(
                array_merge($filterParams, ['limit' => $limit])
            );

            return response()->json([
                'data' => $homes->items(),
                'total' => $homes->total(),
                'per_page' => $homes->perPage(),
                'current_page' => $homes->currentPage(),
                'last_page' => $homes->lastPage(),
                'from' => $homes->firstItem(),
                'to' => $homes->lastItem(),
            ]);
        }

        // ถ้าจังหวัดมีข้อมูล <= 10 ให้รวมกับ zone
        $allHomes = $this->nursing_home_repository->getNursingHomeWithZone(
            array_merge($filterParams, ['additional_limit' => 16])
        );

        // Manual pagination
        $total = $allHomes->count();
        $items = $allHomes->forPage($page, $limit)->values();
        $lastPage = ceil($total / $limit);
        $from = $total > 0 ? (($page - 1) * $limit) + 1 : null;
        $to = min($page * $limit, $total);

        return response()->json([
            'data' => $items,
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => $lastPage,
            'from' => $from,
            'to' => $to > 0 ? $to : null,
        ]);
    }

    public function getNursingHome(int $id) {
        $result = $this->nursing_home_repository->getInfo((int) $id);
        return response()->json($result);
    }

    /**
     * สมัครสมาชิก provider (NURSING_HOME) แบบ atomic เดียว — สร้าง user + profile
     * ในทรานแซคชันเดียวกัน กันไม่ให้เกิด user ค้าง (orphaned) ถ้าส่วนโปรไฟล์ล้มเหลวระหว่างทาง
     * ไม่ออก access_token ทันที — ต้องยืนยัน OTP ผ่าน /api/otp/verify ก่อนถึงจะ login เข้าระบบได้จริง
     */
    public function register(NursingHomeRegisterRequest $request, \App\Services\OtpService $otpService)
    {
        try {
            $result = DB::transaction(function () use ($request) {
                $user = NursingHome::create([
                    'firstname' => $request->firstname,
                    'lastname'  => $request->lastname,
                    'email'     => $request->email,
                    'status'    => 1,
                    'phone'     => $request->phone,
                    'user_type' => UserType::NURSING_HOME->value,
                    'password'  => Hash::make($request->phone),
                    'plan'       => 'BASIC',
                    'plan_start' => Carbon::today()->toDateString(),
                ]);

                $nursinghome = NursingHomeProfile::create([
                    'name' => $user->firstname,
                    'email' => $user->email,
                    'address' => $request->address,
                    'province_id' => $request->province_id,
                    'district_id' => $request->district_id,
                    'sub_district_id' => $request->sub_district_id,
                    'zipcode'  => $request->zipcode,
                    'user_id'  => $user->id,
                    'facebook' => $request->facebook ?? null,
                    'website'  => $request->website ?? null,
                    'main_phone' => $user->phone,
                    'res_phone'  => $request->res_phone ?? null,
                ]);

                return [
                    'user' => $user,
                    'nursinghome' => $nursinghome,
                ];
            });

            $otpService->sendOtp($result['user']->id, $result['user']->phone);

            return response()->json([
                'success' => true,
                'message' => 'สมัครสมาชิกสำเร็จ กรุณายืนยัน OTP เพื่อเข้าสู่ระบบ',
                'data' => [
                    'user' => [
                        'id' => $result['user']->id,
                        'name' => $result['user']->name,
                        'email' => $result['user']->email,
                        'phone' => $result['user']->phone,
                    ],
                    'nursing_home' => [
                        'id' => $result['nursinghome']->id,
                        'name' => $result['nursinghome']->name,
                    ],
                    'otp_required' => true,
                ],
                'errors' => null,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('NursingHome provider registration failed', [
                'message' => $e->getMessage(),
                'email' => $request->email,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด ไม่สามารถสมัครสมาชิกได้ กรุณาลองใหม่อีกครั้ง',
                'errors'  => null,
            ], 500);
        }
    }

    public function store(NursingHomeCreateRequest $request) {
        try {
            $response = DB::transaction(function () use ($request) {
                // Create user
                $user = NursingHome::create([
                    'firstname' => $request->name,
                    'lastname' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->main_phone),
                    'user_type' => UserType::NURSING_HOME->value,
                    'status' => 1,
                    'phone' => $request->main_phone
                ]);

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

                    $profile = NursingHomeProfile::create([
                        'user_id' => $user->id,
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
                        // Additional Info ข้อมูลผู้รับผิดชอบ
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
                        // ห้องพิเศษและสิ่งอำนวยความสะดวก
                        'special_facilities' => $special_facilities,
                        // สิ่งอำนวยความสะดวกทั่วไป
                        'facilities' => $facilities,

                        // ยานพาหนะและอุปกรณ์พิเศษ
                        'ambulance' => $request->ambulance ?? 0,
                        'ambulance_amount' => $request->ambulance_amount ?? 0,
                        'van_shuttle' => $request->van_shuttle ?? 0,
                        'special_medical_equipment' => $request->special_medical_equipment ?? NULL,

                        // In house staff ข้อมูลบุคลากร
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
                
                        //ค่าบริการพื้นฐาน
                        'cost_per_day' => $request->cost_per_day ?? 0,
                        'cost_per_month' => $request->cost_per_month ?? 0,
                        'deposit' => $request->deposit ?? 0,
                        'registration_fee' => $request->registration_fee ?? 0,

                        // ค่าบริการเพิ่มเติม
                        'special_food_expenses' => $request->special_food_expenses ?? 0,
                        'physical_therapy_fee' => $request->physical_therapy_fee ?? 0,
                        'delivery_fee' => $request->delivery_fee ?? 0,
                        'laundry_service' => $request->laundry_service ?? 0,

                        // การรับประกันและการเงิน
                        'social_security' => $request->social_security ?? 0,
                        'private_health_insurance' => $request->private_health_insurance ?? 0,
                        'installment' => $request->installment ?? 0,
                        'payment_methods' => $request->payment_methods ?? NULL,

                        // ข้อมูลเพิ่มเติม
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
                    ]);

                    if ($request->hasFile('profiles')) {
                        $first = true;

                        foreach ($request->file('profiles') as $file) {
                            if ($file->isValid()) {
                                $filename = time() . '_' . $file->getClientOriginalName();

                                // sourcePath อ้างอิงจากไฟล์อัปโหลด
                                $sourcePath = $file->getRealPath();

                                // สร้างชื่อ hashed เหมือน seeder
                                $extension = $file->getClientOriginalExtension();
                                $hashedName = md5(uniqid($user->id, true)) . '.' . $extension;
                                $destPath = 'images/' . $hashedName;
                                $destFullPath = public_path($destPath);

                                File::ensureDirectoryExists(dirname($destFullPath));
                                File::copy($sourcePath, $destFullPath);

                                Image::create([
                                    'user_id' => $profile->id,
                                    'type' => 'NURSING_HOME',
                                    'name' => $filename,
                                    'path' => $destPath,
                                    'filetype' => $file->getClientMimeType(),
                                    'is_cover' => $first,
                                ]);

                                $first = false;
                            }
                        }
                    }

                }
            });

            return response()->json([
                'message' => 'success'
            ], 200);
            
        } catch (QueryException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
       
    }

    public function getProfiles(Request $request)
    {
        $user = $request->user();

        $profiles = $this->nursing_home_repository->getProfiles((int) $user->id);
        return response()->json($profiles);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed'
            ], 403);
        }

        try {
            $result = $this->nursing_home_repository->updateProfile($request->all(), $user->id);

            return response()->json([
                'success' => true,
                'message' => $request->has('id') ? 'Profile updated successfully' : 'Profile created successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function compareNursingHome(Request $request)
    {
        $data = $request->validate([
            'nursinghome_profile_ids'   => ['required', 'array', 'min:1', 'max:3'],
            'nursinghome_profile_ids.*' => ['integer', 'exists:nursing_home_profiles,id'],
        ]);
        
        $ids = $data['nursinghome_profile_ids'];
        
        $profiles = NursingHomeProfile::whereIn('id', $ids)
            ->with([
                'rates',
                'coverImage:id,imageable_id,path,is_cover',
            ])
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('id');
        
        // เรียงตาม order ของ IDs ที่ส่งมา
        $sorted = collect($ids)
            ->map(fn($id) => $profiles->get($id))
            ->filter()
            ->values();

        return NursingHomeProfileCompareResource::collection($sorted)
            ->additional([
                'meta' => [
                    'requested_order' => $ids,
                ]
            ]);
    }

    public function getProfile(Request $request, int $id)
    {
        $profile = $this->service->getProfile(
            $request->user(),
            $id
        );

        return new NursingHomeProfileResource($profile);
    }

    public function updateGeneralProfile(updateGeneralProfileUpdateRequest $request)
    {
        try {
            $user = $request->user();
            $profile = $this->service->updateGeneralProfile($user, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $profile
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateAboutProfile(updateAboutProfileRequest $request)
    {
        try {
            $user = $request->user();

            $staffAvatars = [];
            foreach ($request->input('staffs', []) as $index => $item) {
                if ($request->hasFile("staffs.{$index}.avatar")) {
                    $staffAvatars[$index] = $request->file("staffs.{$index}.avatar");
                }
            }

            $profile = $this->service->updateAboutProfile(
                $user,
                $request->except('license_images_upload'),
                $request->file('license_images_upload') ?? [],
                $staffAvatars
            );
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $profile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateMoreInfoProfile(updateMoreInfoProfileRequest $request)
    {
        try {
            $user = $request->user();
            $profile = $this->service->updateMoreInfoProfile(
                $user,
                $request->except('detail_images'),
                $request->file('detail_images') ?? []);
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $profile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteImage(int $id)
    {
        try {
            $user = request()->user();
            $image = Image::where('id', $id)
                ->where('imageable_type', NursingHomeProfile::class)
                ->whereHas('imageable', fn($q) => $q->where('user_id', $user->id))
                ->first();

            if (!$image) {
                return response()->json(['success' => false, 'message' => 'Image not found'], 404);
            }

            $filePath = public_path($image->path);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            if ($image->is_cover) {
                $nextImage = Image::where('imageable_id', $image->imageable_id)
                    ->where('imageable_type', NursingHomeProfile::class)
                    ->where('id', '!=', $id)
                    ->first();
                if ($nextImage) {
                    $nextImage->update(['is_cover' => true]);
                }
            }

            $image->delete();

            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteLicenseImage(int $id)
    {
        try {
            $user = request()->user();
            $license = NursingHomeLicenseImage::where('id', $id)
                ->whereHas('profile', fn($q) => $q->where('user_id', $user->id))
                ->first();

            if (!$license) {
                return response()->json(['success' => false, 'message' => 'License file not found'], 404);
            }

            $filePath = public_path($license->path);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            $license->delete();

            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteStaff(int $id)
    {
        try {
            $user = request()->user();
            $staff = NursingHomeStaff::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$staff) {
                return response()->json(['success' => false, 'message' => 'Staff not found'], 404);
            }

            if ($staff->image && File::exists(public_path($staff->image))) {
                File::delete(public_path($staff->image));
            }

            $staff->delete();

            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getCollections(Request $request)
    {
        $results = $this->service->getCollections($request->all());
        return response()->json([
            'success' => true,
            'data'    => $results
        ]);
    }

}
