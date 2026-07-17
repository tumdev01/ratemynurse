<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\MemberRepository;
use App\Http\Requests\MemberCreateRequest;
use App\Http\Requests\MemberUpdateRequest;
use App\Http\Resources\MemberResource;
use App\Http\Resources\MemberProfileResource;
use App\Services\Member\ActionUpdateProfileService;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller 
{
    protected $member_repository;
    protected $service;

    public function __construct(MemberRepository $member_repository, ActionUpdateProfileService $service)
    {
        $this->member_repository = $member_repository;
        $this->service = $service;
    }

    public function getUserInfo(Request $request)
    {
        $member = $request->user();
        $result = $this->member_repository->getUser($member->id);
        return response()->json([
            'status' => 'success',
            'data'   => new MemberResource($result),
        ]);
    }

    public function create(MemberCreateRequest $request, \App\Services\OtpService $otpService)
    {
        try {
            // เบอร์นี้เคยสมัครไปแล้วแต่ยังไม่เคยยืนยัน OTP สำเร็จเลย (เช่น OTP รอบก่อนไม่มาถึง/หมดอายุ) —
            // resend OTP ให้ user เดิมแทนที่จะสร้างซ้ำ (ซ้ำไม่ได้อยู่แล้วเพราะ phone/email unique ที่ DB)
            $pending = $otpService->findResumableUser($request->phone);
            if ($pending) {
                $otpService->sendOtp($pending->id, $pending->phone);

                return response()->json([
                    'success' => true,
                    'message' => 'พบข้อมูลการสมัครที่ยังไม่ยืนยัน OTP กรุณายืนยัน OTP เพื่อเข้าสู่ระบบ',
                    'data' => [
                        'user' => new MemberResource($pending),
                        'otp_required' => true,
                    ],
                ]);
            }

            $member = $this->member_repository->store($request->all());
            if (!$member || !$member->exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถบันทึกผู้ใช้ได้',
                ], 500);
            }

            $otpService->sendOtp($member->id, $member->phone);

            return response()->json([
                'success' => true,
                'message' => 'สมัครสมาชิกสำเร็จ กรุณายืนยัน OTP เพื่อเข้าสู่ระบบ',
                'data' => [
                    'user' => new MemberResource($member),
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

    /**
     * Update member profile
     * 
     * @param MemberUpdateRequest $request
     * @return JsonResponse
     */
    public function updateProfile(MemberUpdateRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // เตรียมข้อมูล
            $data = array_filter(
                array_merge(
                    ['user_id' => $user->id],
                    $request->only([
                        'firstname', 'lastname', 'email', 'phone', 'gender', 
                        'date_of_birth', 'address', 'sub_district_id', 
                        'district_id', 'province_id', 'zipcode', 
                        'facebook', 'lineid', 'cardid'
                    ])
                ),
                fn($value, $key) => $key === 'user_id' || !is_null($value),
                ARRAY_FILTER_USE_BOTH
            );

            // เพิ่มไฟล์รูปภาพ
            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $request->file('profile_image');
            }
            
            // อัปเดตผ่าน Service
            $result = $this->service->updateProfile($data);

            return response()->json([
                'success' => true,
                'message' => 'อัปเดตข้อมูลเรียบร้อยแล้ว',
                'data' => [
                    'member' => new MemberResource($result['member']),
                    'profile' => new MemberProfileResource($result['profile'])
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Update profile error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล',
            ], 500);
        }
    }

}