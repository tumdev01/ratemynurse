<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class OtpService
{
    public function generate($id, $identifier, $length = 5, $ttl = 90)
    {
        $otp = str_pad(
            random_int(0, (int) pow(10, $length) - 1),
            $length,
            '0',
            STR_PAD_LEFT
        );

        DB::table('otps')->insert([
            'user_identifier' => $identifier,
            'user_id' => $id,
            'otp_code' => $otp,
            'expires_at' => now()->addSeconds($ttl),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $otp;
    }

    /**
     * สร้าง OTP แล้วส่ง SMS ทันที — ใช้ตอนสมัครสมาชิกสำเร็จ (ต้องยืนยัน OTP ก่อนถึงจะได้ access_token จริง)
     * เหมือน pattern เดียวกับ OtpController::requestOtp() ตอน login
     */
    public function sendOtp($id, $identifier, $length = 5, $ttl = 90)
    {
        $otp = $this->generate($id, $identifier, $length, $ttl);

        $smsService = new SmsService();
        return $smsService->send($identifier, "รหัส OTP ของคุณคือ $otp หมดอายุภายใน {$ttl} วินาที");
    }

    public function verify($identifier, $otp)
    {
        $record = DB::table('otps')
            ->where('user_identifier', $identifier)
            ->where('otp_code', $otp)
            ->where('is_used', false)
            ->first();

        if (! $record) {
            return 'invalid'; // OTP ไม่ถูกต้อง
        }

        if (now()->greaterThan($record->expires_at)) {
            return 'expired'; // OTP หมดอายุ
        }

        // ใช้แล้ว mark ว่า used
        DB::table('otps')->where('id', $record->id)->update(['is_used' => true]);

        // หา user จาก user_identifier
        $user = User::where('phone', $record->user_identifier)->firstOrFail();

        if (! $user->phone_verified_at) {
            $user->forceFill(['phone_verified_at' => now()])->save();
        }

        return $user; // คืนค่า user object แทน 'valid'
    }

    /**
     * หา user ที่สมัครไปแล้วแต่ยังไม่เคยยืนยัน OTP สำเร็จเลย (ค้างจากรอบก่อน เช่น OTP ไม่มาถึง/หมดอายุ)
     * ใช้เช็คก่อนสร้าง user ใหม่ตอนสมัครสมาชิก กันไม่ให้ผู้ใช้ติดล็อกด้วย unique constraint ของเบอร์เดิม
     * ทั้งที่ยังไม่เคย login สำเร็จเลยสักครั้ง — ถ้าเจอ ให้ resend OTP ให้ user เดิมแทนการสร้างซ้ำ
     */
    public function findResumableUser($phone)
    {
        return User::where('phone', $phone)->whereNull('phone_verified_at')->first();
    }

}
