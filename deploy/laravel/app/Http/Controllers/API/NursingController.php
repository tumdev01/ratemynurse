<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nursing;
use App\Repositories\NursingRepository;
use App\Enums\ExpertiseType;
use App\Enums\ZoneType;
use App\Repositories\ProvinceRepository;
use App\Http\Requests\NursingCreateRequest;
use App\Repositories\API\NursingApiRepository;
use App\Http\Resources\NursingResource;

class NursingController extends Controller {
    protected $nursing_repository;
    protected $province_repository;
    protected $nursing_api_repository;
    public function __construct(NursingRepository $nursing_repository, ProvinceRepository $province_repository, NursingApiRepository $nursing_api_repository)
    {
        $this->nursing_repository = $nursing_repository;
        $this->province_repository= $province_repository;
        $this->nursing_api_repository = $nursing_api_repository;
    }

    public function store(NursingCreateRequest $request, \App\Services\OtpService $otpService)
    {
        try {
            $result = $this->nursing_api_repository->createNurse($request->all());

            // Check if creation succeeded
            if (!$result || !$result['user'] || !$result['user']->exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถบันทึกผู้ใช้ได้',
                ], 500);
            }

            $otpService->sendOtp($result['user']->id, $result['user']->phone);

            return response()->json([
                'success' => true,
                'message' => 'สมัครสมาชิกสำเร็จ กรุณายืนยัน OTP เพื่อเข้าสู่ระบบ',
                'data' => [
                    'user' => $result['user'],
                    'otp_required' => true,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getNursing(Request $request)
    {
        $limit = $request->input('limit');
        $certified = $request->input('certified') ?? false;

        $nursings = $this->nursing_repository->getNursing(['limit' => $limit, 'certified' => $certified]);
        return response()->json($nursings);
    }

    public function getNursingPagination(Request $request)
    {
        try {
            $limit = $request->input('limit', 8);
            $page = $request->input('page', 1);
            $certified = $request->input('certified');
            $order = $request->input('order', 'DESC');
            $orderby = $request->input('orderby', 'id');
            $province = $request->input('province');
            $zone = $request->input('zone');
            $search = $request->input('search'); // ← เพิ่ม

            $provinceTotal = $this->nursing_repository->countByProvince([
                'certified' => $certified,
                'province' => $province,
                'search' => $search, // ← เพิ่ม
            ]);

            if ($provinceTotal > 10) {
                $nursings = $this->nursing_repository->getNursingPagination([
                    'limit' => $limit,
                    'page' => $page,
                    'certified' => $certified,
                    'orderby' => $orderby ?? 'id',
                    'order' => $order ?? 'DESC',
                    'province' => $province,
                    'zone' => $zone,
                    'search' => $search, // ← เพิ่ม
                ]);

                return response()->json([
                    'data' => $nursings->items(),
                    'total' => $nursings->total(),
                    'per_page' => $nursings->perPage(),
                    'current_page' => $nursings->currentPage(),
                    'last_page' => $nursings->lastPage(),
                    'from' => $nursings->firstItem(),
                    'to' => $nursings->lastItem(),
                ]);
            }

            $allNursings = $this->nursing_repository->getNursingWithZone([
                'certified' => $certified,
                'orderby' => $orderby ?? 'id',
                'order' => $order ?? 'DESC',
                'province' => $province,
                'zone' => $zone,
                'additional_limit' => 16,
                'search' => $search, // ← เพิ่ม
            ]);

            // Manual pagination
            $total = $allNursings->count();
            $items = $allNursings->forPage($page, $limit)->values();
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

        } catch (\Exception $e) {
            \Log::error('getNursingPagination error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'An error occurred',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => []
            ], 500);
        }
    }

    public function getFilterElements()
    {
        $expertise = ExpertiseType::list();

        return response()->json([
            'expertises' => $expertise
        ]);
    }

    public function getLocations()
    {
        $provinces = $this->province_repository->getProvinceDropdown();
        $provincesGroupBy = $provinces->groupBy('zone');
        $result = $provincesGroupBy->toArray();
        return response()->json([
            'data' => $result
        ]);
    }

    public function getNursingByLocation(Request $request) {
        dd($request->all());
    }

    public function getNursingById(Int $id)
    {
        $result = $this->nursing_repository->getNursingById((int) $id);
        return response()->json($result);
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
            $result = $this->nursing_api_repository->updateProfile($request->all(), $user->id);

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

    // public function compareNursing(Request $request)
    // {
    //     $data = $request->validate([
    //         'nurse_ids'   => ['required', 'array', 'min:1', 'max:3'],
    //         'nurse_ids.*' => ['integer', 'exists:users,id'],
    //     ]);
        
    //     $ids = $data['nurse_ids'];
        
    //     $nurses = Nursing::whereIn('id', $ids)
    //         ->select([
    //             'users.id',
    //             'users.firstname',
    //             'users.lastname'
    //         ])
    //         ->with([
    //             'profile:id,user_id,zipcode,province_id,district_id,sub_district_id,cost,name,certified',
    //             'profile.province:id,name',
    //             'profile.district:id,name',
    //             'profile.subDistrict:id,name',
    //             'costs',
    //             'images:user_id,path,is_cover',
    //             'coverImage:user_id,path,is_cover',

    //             // ✅ แก้ตรงนี้
    //             'profile.rates.rate_details',
    //         ])
    //         ->whereNull('users.deleted_at')
    //         ->get()
    //         ->keyBy('id');
        
    //     // เรียงและเลือกเฉพาะ fields ที่ต้องการ
    //     $sortedNurses = collect($ids)->map(function ($id) use ($nurses) {
    //         $nurse = $nurses->get($id);
            
    //         if (!$nurse) return null;

    //         $rateDetails = $nurse->profile->rates->flatMap->rate_details;
            
    //         return [
    //             'id' => $nurse->id,
    //             'firstname' => $nurse->firstname,
    //             'lastname' => $nurse->lastname,
    //             // เพิ่ม fields อื่นๆ ที่ต้องการจาก users table
                
    //             'profile' => $nurse->profile ? [
    //                 'name' => $nurse->profile->name,
    //                 'cost' => $nurse->profile->cost,
    //                 'nickname' => $nurse->profile->nickname,
    //             ] : null,
    //             'rating_avg' => round($rateDetails->avg('rating'), 1),
    //             'review_count' => $rateDetails->count(),
    //             'cover_image' => $nurse->coverImage,
    //         ];
    //     })->filter()->values();

    //     return response()->json($sortedNurses);
    // }
    public function compareNursing(Request $request)
    {
        $data = $request->validate([
            'nurse_ids'   => ['required', 'array', 'min:1', 'max:3'],
            'nurse_ids.*' => ['integer', 'exists:users,id'],
        ]);
        
        $ids = $data['nurse_ids'];
        
        $nurses = Nursing::whereIn('id', $ids)
            ->with([
                'profile:id,user_id,zipcode,province_id,district_id,sub_district_id,cost,name,certified,nickname,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'costs',
                'lowestCost',
                'cvs',
                'detail:user_id,about,hire_rules,skills,other_skills',
                'coverImage:user_id,path,is_cover',
                'profile.rates.rate_details',
            ])
            ->whereNull('users.deleted_at')
            ->get()
            ->keyBy('id');
        
        // เรียงตาม order ของ IDs ที่ส่งมา
        $sortedNurses = collect($ids)
            ->map(fn($id) => $nurses->get($id))
            ->filter()
            ->values();

        return NursingResource::collection($sortedNurses)
            ->additional([
                'meta' => [
                    'requested_order' => $ids,
                ]
            ]);
    }

    public function getContacts(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        $perPage = $request->get('per_page', 20); // default 20 items per page
        $useCache = $request->get('useCache', true); // ให้เลือกได้ว่าจะใช้ cache หรือไม่
        $result = $this->nursing_api_repository->getContacts(
            $user->id, 
            $perPage,
            $useCache
        );
        return response()->json([
            'success' => true,
            'data' => $result,
        ], 200);
    }
}
