<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\SubDistrictController;
use App\Http\Controllers\API\OtpController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'api.role'])->group(function () {
    // Nursing
    Route::post('/nursings', [NursingController::class, 'getNursing']);
    Route::post('/nursings/all', [NursingController::class, 'getNursingPagination']);
    Route::get('/nursing-filter-elements', [NursingController::class, 'getFilterElements']);
    // NursingHome
    Route::post('/nursing-homes', [NursingHomeController::class, 'getNursingHomes']);
    Route::post('/nursing-homes-listing', [NursingHomeController::class,'getNuringHomePagination']);
    Route::get('/nursing-homes/{id}', [NursingHomeController::class,'getNursingHome']);

    //Main Menu Nursing
    Route::get('nursing-locations', [NursingController::class, 'getLocations']);

    Route::get('/provinces', [ProvinceController::class, 'getProvinces']);

});


Route::get('provinces_list', [ProvinceController::class, 'getProvinces']);
Route::get('districts_list/{province_id}', [DistrictController::class, 'getDistrictsByProvinceId'])
    ->where('province_id', '\d+');
Route::get('sub_districts_list/{district_id}', [SubDistrictController::class, 'getSubDistrictsByDistrictId'])
    ->where('district_id', '\d+');

Route::get('/province/{id}', [ProvinceController::class, 'getProvinceById']);

Route::post('/otp/request', [OtpController::class, 'requestOtp']);
Route::post('/otp/verify', [OtpController::class, 'verifyOtp']);
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});

Route::middleware(['verify.internal.token'])->group(function () {
    Route::prefix('nursing-home')->group(function() {
        Route::post('/create', [NursingHomeController::class, 'store']);
    });
});