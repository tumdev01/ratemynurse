<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;

class SmsService
{
    public function send($phone , $message)
    {
        return Http::withOptions([
            'verify' => false,
        ])
        ->withHeaders([
            'api_key' => config('services.sms.api_key'),
            'secret_key' => config('services.sms.api_secret'),
        ])
        ->asJson()
        ->post(config('services.sms.endpoint'), [
            'message' => $message,
            'phone' => $phone,
            'sender' => config('services.sms.sender'),
        ]);
    }
}