<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ตัวอย่าง route สำหรับ Firebase Auth token verification และ login
Route::post('/auth/firebase', [\App\Http\Controllers\Auth\FirebaseAuthController::class, 'login']);
