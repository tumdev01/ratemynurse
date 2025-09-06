<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\OtpService;
use App\Services\SmsService;

class OtpController extends Controller
{
    protected $otp;

    public function __construct(OtpService $otp)
    {
        $this->otp = $otp;
    }

    // Step 1: Request OTP
    public function requestOtp(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        try {
            // หา user ตามเบอร์
            $user = User::where('phone', $request->phone)->firstOrFail();

            // สร้าง OTP 6 หลัก หมดอายุ 30 วินาที
            $otp = $this->otp->generate($user->id, $request->phone, 5, 30);

            // ส่ง OTP
            $smsService = new SmsService();
            $response = $smsService->send($request->phone, "รหัส OTP ของคุณคือ $otp หมดอายุภายใน 30 วินาที");

            if ($response->successful()) {
                $response_body = $response->json();

                if ($response_body['code'] === "000") {
                    return response()->json(['message' => 'กรุณายืนยัน OTP'], 200);
                } elseif ($response_body['code'] === "107") {
                    return response()->json(['message' => 'เบอร์โทรศัพท์ไม่ถูกต้อง'], 400);
                }
            }

            return response()->json(['message' => 'เกิดข้อผิดพลาดในการส่ง OTP'], 500);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'ไม่พบผู้ใช้'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Step 2: Verify OTP
    public function verifyOtp(\App\Http\Requests\API\VerifyOtpRequest $request)
    {
        $userOrStatus = $this->otp->verify($request->phone, $request->otp);

        if ($userOrStatus === 'invalid') {
            return response()->json(['message' => 'Invalid OTP'], 401);
        }

        if ($userOrStatus === 'expired') {
            return response()->json(['message' => 'OTP expired, please request a new one'], 410);
        }

        $user = $userOrStatus;

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user_id'      => $user->id,
        ]);
    }


    // API สำหรับดึงข้อมูล subset ของ user
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'uuid'      => $user->uuid ?? null,
            'firstname' => $user->firstname,
            'lastname'  => $user->lastname,
            'role'      => $user->user_type,
        ]);
    }
}
