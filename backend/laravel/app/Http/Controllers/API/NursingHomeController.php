<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\NursingHomeRepository;
use App\Http\Requests\NursingHomeCreateRequest;
use App\Models\NursingHome;
use App\Models\NursingHomeProfile;
use App\Models\Image;
use App\Enums\UserType;
use App\Enums\HomeServiceType;
use App\Enums\AdditionalServiceType;
use App\Enums\SpecialFacilityType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;

class NursingHomeController extends Controller {
    protected $nursing_home_repository;

    public function __construct(NursingHomeRepository $nursing_home_repository)
    {
        $this->nursing_home_repository = $nursing_home_repository;
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
        $limit = $request->input('limit');
        $certified = $request->input('certified');
        $orderby  = $request->input('order_by');
        $order     = $request->input('order');
        $province  = $request->input('province');
        $homes = $this->nursing_home_repository->getNuringHomePagination([
            'limit' => $limit,
            'certified' => $certified,
            'orderby' => $orderby,
            'order' => $order,
            'province' => $province
        ]);
        
        return response()->json([
            'data' => $homes->items(),
            'total' => $homes->total(),
            'per_page' => $homes->perPage(),
            'current_page' => $homes->currentPage(),
            'last_page' => $homes->lastPage(),
        ]);
    }

    public function getNursingHome(int $id) {
        $result = $this->nursing_home_repository->getInfo((int) $id);
        return response()->json($result);
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

                    NursingHomeProfile::create([
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
                                    'user_id' => $user->id,
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

}
