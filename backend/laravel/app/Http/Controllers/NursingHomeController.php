<?php

namespace App\Http\Controllers;
use App\Repositories\NursingHomeRepository;
use App\Http\Requests\NursingHomeCreateRequest;
use App\Http\Requests\NursingHomeUpdateRequest;
use App\Http\Requests\NursingHomeCreateStaffRequest;
use App\Http\Requests\RateCreateRequest;
use App\Models\User;
use App\Models\NursingHome;
use App\Models\NursingHomeProfile;
use App\Models\NursingHomeStaff;
use App\Models\Image;
use App\Enums\UserType;
use App\Enums\HomeServiceType;
use App\Enums\AdditionalServiceType;
use App\Enums\SpecialFacilityType;
use App\Enums\NursingHomeRateType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;
use App\Repositories\RateRepository;


class NursingHomeController extends Controller {
    protected $nursing_home_repository;
    protected $rate_repository;

    public function __construct(NursingHomeRepository $nursing_home_repository, RateRepository $rate_repository)
    {
        $this->nursing_home_repository = $nursing_home_repository;
        $this->rate_repository = $rate_repository;
    }
    public function index() {
        return view('pages.nursinghome.index');
    }

    public function create() {
        return view('pages.nursinghome.create');
    }

    public function store(NursingHomeCreateRequest $request) {
        try {
            DB::transaction(function () use ($request) {
                $user = null;

                if ($request->filled('user_id')) {
                    $user = NursingHome::find($request->user_id);
                }

                if (!$user) {
                    $user = NursingHome::create([
                        'firstname' => $request->name,
                        'lastname'  => $request->name,
                        'email'     => $request->email,
                        'password'  => Hash::make($request->main_phone),
                        'user_type' => UserType::NURSING_HOME->value,
                        'status'    => 1,
                        'phone'     => $request->main_phone
                    ]);
                }

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

                    // ====== Create Profile ======
                    $profile = NursingHomeProfile::create([
                        'user_id'   => $user->id,
                        'name'      => $request->name,
                        'description' => $request->description,
                        'main_phone'  => $request->main_phone,
                        'res_phone'   => $request->res_phone,
                        'facebook'    => $request->facebook,
                        'website'     => $request->website,
                        'address'     => $request->address,
                        'license_no'  => $request->license_no,
                        'license_start_date' => $request->license_start_date,
                        'license_exp_date'   => $request->license_exp_date,
                        'license_by' => $request->license_by,
                        'certificates' => $request->certificates,
                        'hospital_no' => $request->hospital_no,

                        // Additional Info
                        'manager_name' => $request->manager_name,
                        'graduated'    => $request->graduated,
                        'graduated_paper' => $request->graduated_paper,
                        'exp_year' => $request->exp_year,
                        'manager_phone' => $request->manager_phone,
                        'manager_email' => $request->manager_email,
                        'assist_name'   => $request->assist_name,
                        'assist_no'     => $request->assist_no,
                        'assist_expert' => $request->assist_expert,
                        'assist_phone'  => $request->assist_phone,

                        'home_service_type'      => $home_service_type,
                        'etc_service'            => $request->etc_service,
                        'additional_service_type'=> $additional_service_type,

                        'building_no'  => $request->building_no ?? 0,
                        'total_room'   => $request->total_room ?? 0,
                        'private_room_no' => $request->private_room_no ?? 0,
                        'duo_room_no'  => $request->duo_room_no ?? 0,
                        'shared_room_three_beds' => $request->shared_room_three_beds ?? 0,
                        'max_serve_no' => $request->max_serve_no ?? 0,
                        'area'         => $request->area ?? 0,
                        'special_facilities' => $special_facilities,
                        'facilities'   => $facilities,

                        'ambulance' => $request->ambulance ?? 0,
                        'ambulance_amount' => $request->ambulance_amount ?? 0,
                        'van_shuttle' => $request->van_shuttle ?? 0,
                        'special_medical_equipment' => $request->special_medical_equipment ?? NULL,

                        // staff
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

                        // ค่าบริการ
                        'cost_per_day' => $request->cost_per_day ?? 0,
                        'cost_per_month' => $request->cost_per_month ?? 0,
                        'deposit' => $request->deposit ?? 0,
                        'registration_fee' => $request->registration_fee ?? 0,
                        'special_food_expenses' => $request->special_food_expenses ?? 0,
                        'physical_therapy_fee' => $request->physical_therapy_fee ?? 0,
                        'delivery_fee' => $request->delivery_fee ?? 0,
                        'laundry_service' => $request->laundry_service ?? 0,

                        // การรับประกัน
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

                    // ====== Images ======
                    if ($request->hasFile('profiles')) {
                        $first = true;
                        foreach ($request->file('profiles') as $file) {
                            if ($file->isValid()) {
                                $filename = time() . '_' . $file->getClientOriginalName();
                                $extension = $file->getClientOriginalExtension();
                                $hashedName = md5(uniqid($user->id, true)) . '.' . $extension;
                                $destPath = 'images/' . $hashedName;
                                $destFullPath = public_path($destPath);

                                File::ensureDirectoryExists(dirname($destFullPath));
                                File::copy($file->getRealPath(), $destFullPath);

                                Image::create([
                                    'user_id'       => $user->id,
                                    'imageable_id'  => $profile->id,
                                    'imageable_type'=> NursingHomeProfile::class,
                                    'type'          => 'NURSING_HOME',
                                    'name'          => $filename,
                                    'path'          => $destPath,
                                    'filetype'      => $file->getClientMimeType(),
                                    'is_cover'      => $first,
                                ]);

                                $first = false;
                            }
                        }
                    }
                }
            });

            return redirect()->route('nursinghome.index')->with('success', 'บันทึกเรียบร้อยแล้ว');
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return redirect()->back()
                        ->withInput()
                        ->with('error', 'อีเมลนี้มีผู้ใช้งานแล้ว');
            }
            throw $e;
        }
    }

    public function editStaff(Int $id) {
        $nursinghome = $this->nursing_home_repository->getInfo((int) $id);
        return view('pages.nursinghome.staff', compact('nursinghome'));
    }

    public function createStaff(NursingHomeCreateStaffRequest $request,Int $id)
    {
        $response = $this->nursing_home_repository->createStaff($request, $id);
        if ($response['status'] === 'success') {
            return redirect()
                ->back() // กลับไปหน้าเดิม
                ->with('success', $response['message']); // แสดง flash message
        }
        return redirect()
            ->back()
            ->withErrors($response['errors'] ?? ['เกิดข้อผิดพลาดไม่ทราบสาเหตุ']);
    }

    public function edit(Int $id) {
        $nursinghome = $this->nursing_home_repository->getInfo((int) $id);
        if($nursinghome->home_service_type) {
            $nursinghome->home_service_type = json_decode($nursinghome->home_service_type);
        }
        $nursinghome->additional_service_type = json_decode($nursinghome->additional_service_type) ?? [];
        $nursinghome->special_facilities = json_decode($nursinghome->special_facilities) ?? [];
        $nursinghome->facilities = json_decode($nursinghome->facilities) ?? [];

        return view('pages.nursinghome.edit', compact('nursinghome'));
    }

    public function update(NursingHomeUpdateRequest $request, int $id)
    {
        $result = $this->nursing_home_repository->updateNursingHomeData($request, $id);

        if ($result['status'] === 'success') {
            return redirect()
                ->back() // กลับไปหน้าเดิม
                ->with('success', $result['message']); // แสดง flash message
        }

        return redirect()
            ->back()
            ->withErrors($result['errors'] ?? ['เกิดข้อผิดพลาดไม่ทราบสาเหตุ']);
    }

    public function getNursingHomePagination(Request $request, NursingHomeRepository $repo) {
        $filters = $request->only(['certified','province','orderby','order']);
        return $this->nursing_home_repository->getNursingHomeDataTable($filters);
    }

    public function updateCover(Request $request, $id)
    {
        $image = Image::findOrFail($id);
        Image::where('type', 'NURSING_HOME')
            ->where('user_id', $image->user_id)
            ->update(['is_cover' => 0]);

        $image->is_cover = $request->is_cover;
        $image->save();

        return response()->json(['success' => true]);
    }

    public function deleteImage(Request $request, $id)
    {
        // ✅ หา image จาก id ที่ส่งมา
        $image = Image::findOrFail($id);
        $userId = $image->user_id;

        // ✅ ลบไฟล์จริงออกจาก public (ถ้ามี)
        $filePath = public_path($image->path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // ✅ ลบ record จากฐานข้อมูล
        $image->delete();

        // ✅ ถ้าภาพที่ลบเป็น cover → ตั้งภาพถัดไปเป็น cover
        if ($image->is_cover) {
            $nextImage = Image::where('user_id', $userId)
                ->where('type', 'NURSING_HOME')
                ->orderBy('id', 'asc')
                ->first();

            if ($nextImage) {
                $nextImage->is_cover = true;
                $nextImage->save();
            }
        }

        return response()->json(['success' => true]);
    }

    public function deleteStaff($id)
    {
        $staff = NursingHomeStaff::findOrFail($id);
        $staff->delete();
        return response()->json(['success' => true]);
    }

    public function review($id) 
    {
        $choices = NursingHomeRateType::list();
        $nursinghome = $this->nursing_home_repository->getInfo((int) $id);
        return view('pages.nursinghome.rate', compact('nursinghome', 'choices'));
    }

    public function reviewCreate(RateCreateRequest $request)
    {
        $result = $this->rate_repository->create($request->all());
        $result = $result->getData(true);
        if ($result['status'] === 'success') {
            return redirect()
                ->back() // กลับไปหน้าเดิม
                ->with('success', $result['message']); // แสดง flash message
        }

        return redirect()
            ->back()
            ->withErrors($result['errors'] ?? ['เกิดข้อผิดพลาดไม่ทราบสาเหตุ']);
    }

    public function getNursingHomeUser(Request $request)
    {
        $term = $request->get('term', '');

        $users = NursingHome::query()
            ->select(['id', 'firstname'])
            ->when($term, function ($q) use ($term) {
                $q->where('firstname', 'like', '%' . $term . '%');
            })
            ->orderBy('firstname')
            ->get();

        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'text' => $user->firstname,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function profileView(Int $id)
    {
        $nursinghome = $this->nursing_home_repository->getProfile((int) $id);
        return view('pages.nursinghome.profile', compact('nursinghome'));
    }

    public function profileUpdate(Int $user_id, Request $request)
    {
        $user = User::findOrFail($user_id);

        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname'  => ['required', 'string', 'max:255'],
            'phone'     => [
                'required',
                'max:10',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'email'     => [
                'required',
                'string',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->firstname = $validated['firstname'];
        $user->lastname  = $validated['lastname'];
        $user->phone     = $validated['phone'];
        $user->email     = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->back()
            ->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }
}