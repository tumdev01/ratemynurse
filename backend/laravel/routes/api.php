<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\SubDistrictController;
use App\Http\Controllers\API\OtpController;
use App\Http\Controllers\API\RateController;
use App\Http\Controllers\API\JobController;
use App\Http\Controllers\API\JobInterviewController;
use App\Models\Nursing;
use App\Models\NursingHome;
use App\Models\Member;
use App\Http\Resources\NursingResource;
use App\Http\Resources\MemberResource;
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
    Route::get('/nursing/{id}', [NursingController::class, 'getNursingById']); // get Nursing Info/Detail by Nurse ID
    Route::post('/nursings/all', [NursingController::class, 'getNursingPagination']);
    Route::get('/nursing-filter-elements', [NursingController::class, 'getFilterElements']);
    // NursingHome
    Route::post('/nursing-homes', [NursingHomeController::class, 'getNursingHomes']);
    Route::post('/nursing-homes-listing', [NursingHomeController::class,'getNuringHomePagination']);
    Route::get('/nursing-homes/{id}', [NursingHomeController::class,'getNursingHome']);

    //Main Menu Nursing
    Route::get('nursing-locations', [NursingController::class, 'getLocations']);

    Route::get('/provinces', [ProvinceController::class, 'getProvinces']);

    Route::post('/rate', [RateController::class, 'create']);

    Route::get('/members', [MemberController::class, 'getMembers']);

    //Roiute::get('/job-filter', [JobController::class, 'jobFilters']);
});

Route::middleware(['auth:sanctum', 'member.role'])->group(function() {
    Route::get('/member/{id}', [MemberController::class, 'getUserInfo']);
    Route::post('/info', [MemberController::class, 'getUserInfo']);
    Route::post('/job/create', [JobController::class, 'store']);
    Route::post('/job/user/job-list', [JobController::class, 'getJobList']);
});

// Route role only for Nursing role
Route::middleware(['auth:sanctum', 'nursing.role'])->group(function () {
    Route::get('/nursing/{id}', [NursingController::class, 'getNursingById']);
    Route::prefix('job-nursing')->group(function () {
        Route::post('/apply', [JobInterviewController::class, 'applyNursingJob']);
    });
    Route::post('/nursing/profile/update', [NursingController::class, 'updateProfile']);
    Route::post('/cv/{id}/delete', [NursingCvImageController::class, 'delete']);
    Route::post('/detail_image/{id}/delete', [NursingDetailImageController::class, 'delete']);
});


Route::middleware(['auth:sanctum', 'nursing_home.role'])->group (function () {
    Route::get('/nursing-home/profiles', [NursingHomeController::class, 'getProfiles']);
    Route::post('/nursing-home/profile/update', [NursingHomeController::class, 'updateProfile']);
});

Route::get('provinces_list', [ProvinceController::class, 'getProvinces']);
Route::get('districts_list/{province_id}', [DistrictController::class, 'getDistrictsByProvinceId'])
    ->where('province_id', '\d+');
Route::get('sub_districts_list/{district_id}', [SubDistrictController::class, 'getSubDistrictsByDistrictId'])
    ->where('district_id', '\d+');
Route::get('/province/{id}', [ProvinceController::class, 'getProvinceById']);

Route::post('/otp/request', [OtpController::class, 'requestOtp']);
Route::post('/otp/verify', [OtpController::class, 'verifyOtp']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getuser']);
});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    $user = $request->user();

    switch ($user->user_type) {
        case 'NURSING':
            $model = Nursing::with(['profile', 'images', 'coverImage', 'cvs', 'cvs.images'])->find($user->id);
            $data = (new NursingResource($model))->toArray($request);
            break;

        case 'NURSING_HOME':
            $model = NursingHome::with(['homeProfile', 'rooms'])->find($user->id);
            $data = (new NursingHomeResource($model))->toArray($request);
            break;

        case 'MEMBER':
            $model = Member::with(['profile.subscriptions'])->find($user->id);
            $data = (new MemberResource($model))->toArray($request);
            break;

        default:
            return response()->json([
                'success' => false,
                'message' => 'User type not supported'
            ], 400);
    }

    return response()->json([
        'success' => true,
        'data' => $data,
    ]);
});

Route::middleware(['verify.internal.token'])->group(function () {
    Route::prefix('nursing-home')->group(function() {
        Route::post('/create', [NursingHomeController::class, 'store']);
    });
    Route::post('/job/job-list', [JobController::class, 'getJobList']);
    Route::get('/job/{id}', [JobController::class, 'getJob']);

    Route::post('/member/create', [MemberController::class, 'create']);
    Route::post('/nursinghome/create', [NursingHomeController::class, 'userCreate']);
    Route::post('/nursinghome/profile/create', [NursingHomeController::class, 'userCreateProfile']);

    Route::post('/nursing/create', [NursingController::class, 'store']);
});


Route::post('/debug', function (\Illuminate\Http\Request $request) {
    return response()->json($request->headers->all());
});