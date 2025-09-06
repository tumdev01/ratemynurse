<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OtpService
{
    public function generate($id, $identifier, $length = 5, $ttl = 60)
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

        return $user; // คืนค่า user object แทน 'valid'
    }

}
