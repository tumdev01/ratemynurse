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
use App\Http\Controllers\API\MemberContactController;
use App\Http\Controllers\API\AnalyticsController;
use App\Http\Controllers\API\SubscriptionRequestController;
use App\Models\Nursing;
use App\Models\NursingHome;
use App\Models\Member;
use App\Http\Resources\NursingResource;
use App\Http\Resources\MemberResource;
use App\Http\Resources\NursingHomeResource;
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
    Route::get('/nursing/{id}', [NursingController::class, 'getNursingById']); // get Nursing Info/Detail by Nurse ID
    Route::get('/nursing-filter-elements', [NursingController::class, 'getFilterElements']);
    // NursingHome
    Route::get('/nursing-homes/{id}', [NursingHomeController::class,'getNursingHome'])->where('id', '[0-9]+');

    //Main Menu Nursing
    Route::get('nursing-locations', [NursingController::class, 'getLocations']);

    Route::get('/provinces', [ProvinceController::class, 'getProvinces']);

    Route::post('/rate', [RateController::class, 'create']);

    Route::get('/members', [MemberController::class, 'getMembers']);

    Route::post('/notification', [NotificationController::class, 'createNotification']);
    Route::get('/notification', [NotificationController::class, 'getNotifications']);
    // Route::post('/notification/{id}/read/', [NotificationController::class, 'setNotificationAsRead'])->where('id', '[0-9]+');
});

// Job routes — ทุก user_type สร้างประกาศได้
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/job/create', [JobController::class, 'store']);
    Route::post('/job/user/job-list', [JobController::class, 'getJobList']);
    Route::post('/job/{id}/close', [JobController::class, 'close'])->where('id', '[0-9]+');
});

Route::middleware(['auth:sanctum', 'member.role'])->group(function() {
    Route::get('/member/{id}', [MemberController::class, 'getUserInfo'])->where('id', '[0-9]+');
    Route::post('/info', [MemberController::class, 'getUserInfo']);

    Route::prefix('member')->group(function () {
        Route::get('/contacts', [MemberContactController::class, 'getContacts']);
        Route::get('/contact/{id}', [MemberContactController::class, 'getContactById'])->where('id', '[0-9]+');
        Route::delete('/contact/{id}', [MemberContactController::class, 'deleteContact'])->where('id', '[0-9]+');
        Route::post('/contact/create', [MemberContactController::class, 'create']);
        Route::put('/contact/{id}', [MemberContactController::class, 'updateContact'])->where('id', '[0-9]+');
        Route::post('/contact/rate', [RateController::class, 'rateProvider']);
        Route::delete('/job-interview/{id}', [JobInterviewController::class, 'destroy'])->where('id', '[0-9]+');
        Route::post('/profile/update', [MemberController::class,'updateProfile']);
    });
    
    Route::prefix('favorite')->group(function() {
        Route::post('/add', [FavoriteController::class, 'add']);
        Route::post('/remove', [FavoriteController::class, 'remove']);
        Route::post('/toggle', [FavoriteController::class, 'toggle']);

        Route::get('/ids', [FavoriteController::class, 'getFavoriteIds']);
        Route::get('/', [FavoriteController::class, 'getFavoritesPaginate']);
    });
});

// Route role only for Nursing role
Route::middleware(['auth:sanctum', 'nursing.role'])->group(function () {
    Route::get('/nursing/{id}', [NursingController::class, 'getNursingById'])->where('id', '[0-9]+');
    Route::post('/nursing/profile/update', [NursingController::class, 'updateProfile']);
    Route::post('/cv/{id}/delete', [NursingCvImageController::class, 'delete']);
    Route::post('/detail_image/{id}/delete', [NursingDetailImageController::class, 'delete']);

    Route::get('/nursing/contacts', [NursingController::class, 'getContacts']);

    Route::get('nursing/provider/favorites', [FavoriteController::class, 'getFavoritesForProviderPaginate']);
    Route::delete('nursing/provider/favorites/{id}', [FavoriteController::class, 'removeAsProvider'])->where('id', '[0-9]+');
});

Route::middleware(['auth:sanctum', 'nursing_home.role'])->group (function () {
    Route::get('/nursing-home/profiles', [NursingHomeController::class, 'getProfiles']);
    Route::post('/nursing-home/profile/update', [NursingHomeController::class, 'updateProfile']);

    Route::get('/nursing-home/contacts', function (Request $request) {
        $user = $request->user();
        $profiles = \App\Models\NursingHomeProfile::where('user_id', $user->id)
            ->select('id', 'name')
            ->get();

        $result = [];
        foreach ($profiles as $profile) {
            $contacts = \App\Models\MemberContact::where('provider_user_id', $user->id)
                ->where('provider_role', 'NURSING_HOME')
                ->where('provider_profile_id', $profile->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $contactsData = $contacts->map(function ($contact) {
                $member = \App\Models\Member::with('profile.coverImage')->find($contact->member_id);
                return [
                    'id' => $contact->id,
                    'description' => $contact->description,
                    'start_date' => $contact->start_date,
                    'end_date' => $contact->end_date,
                    'phone' => $contact->phone,
                    'email' => $contact->email,
                    'provider_role' => $contact->provider_role,
                    'provider_accepted' => $contact->provider_accepted,
                    'created_at' => $contact->created_at,
                    'member' => $member && $member->profile ? [
                        'name' => $member->profile->name,
                        'coverImage' => $member->profile->coverImage?->full_path,
                    ] : null,
                ];
            });

            $result[] = [
                'profile_id' => $profile->id,
                'profile_name' => $profile->name,
                'contacts' => $contactsData,
            ];
        }

        return response()->json(['success' => true, 'data' => $result]);
    });

    Route::get('nursing-home/provider/favorites', [FavoriteController::class, 'getFavoritesForProviderPaginate']);
    Route::delete('nursing-home/provider/favorites/{id}', [FavoriteController::class, 'removeAsProvider'])->where('id', '[0-9]+');

    Route::get('nursing-home/profile/{id}', [NursingHomeController::class, 'getProfile'])->where('id', '[0-9]+');
    Route::post('nursing-home/profile/general', [NursingHomeController::class, 'updateGeneralProfile']);
    Route::post('nursing-home/profile/about', [NursingHomeController::class, 'updateAboutProfile']);
    Route::post('nursing-home/profile/moreinfo', [NursingHomeController::class, 'updateMoreInfoProfile']);
    Route::delete('nursing-home/image/{id}', [NursingHomeController::class, 'deleteImage'])->where('id', '[0-9]+');
    Route::delete('nursing-home/license/{id}', [NursingHomeController::class, 'deleteLicenseImage'])->where('id', '[0-9]+');
    Route::delete('nursing-home/staff/{id}', [NursingHomeController::class, 'deleteStaff'])->where('id', '[0-9]+');
});

Route::get('provinces_list', [ProvinceController::class, 'getProvinces']);
Route::get('districts_list/{province_id}', [DistrictController::class, 'getDistrictsByProvinceId'])
    ->where('province_id', '\d+');
Route::get('sub_districts_list/{district_id}', [SubDistrictController::class, 'getSubDistrictsByDistrictId'])
    ->where('district_id', '\d+');
Route::get('/province/{id}', [ProvinceController::class, 'getProvinceById'])->where('id', '[0-9]+');
Route::get('/province/{tag}', [ProvinceController::class, 'getProvinceByTag']);

Route::post('/otp/request', [OtpController::class, 'requestOtp']);
Route::post('/otp/verify', [OtpController::class, 'verifyOtp']);
Route::post('/check-phone', [OtpController::class, 'checkPhone']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getuser']);

    Route::post('/notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/notification/{id}/read/', [NotificationController::class, 'setNotificationAsRead'])->where('id', '[0-9]+');

    Route::get('/provider/contact/{id}', [MemberContactController::class, 'getContactByIdForProvider'])->where('id', '[0-9]+');
    Route::post('/provider/contact/{id}/accept', [MemberContactController::class, 'providerContactAccept'])->where('id', '[0-9]+');

    Route::post('/user/update', function (Request $request) {
        $user = $request->user();

        $rules = [
            'firstname' => ['required', 'string', 'max:50'],
            'lastname'  => ['required', 'string', 'max:50'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'     => ['required', 'regex:/^0[0-9]{9}$/', 'unique:users,phone,' . $user->id],
        ];

        $validated = $request->validate($rules);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'บันทึกข้อมูลเรียบร้อย',
            'data'    => $user->only(['id', 'firstname', 'lastname', 'email', 'phone']),
        ]);
    });

    Route::prefix('job-nursing')->group(function () {
        Route::post('/apply', [JobInterviewController::class, 'applyNursingJob']);
        Route::post('/interviews', [JobInterviewController::class, 'getInterviews']);
    });
});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    $user = $request->user();

    switch ($user->user_type) {
        case 'NURSING':
            $model = Nursing::with(['profile.subscriptions', 'profile.currentActiveSubscription', 'coverImage', 'notifications', 'readNotifications', 'unreadNotifications', 'profile.rates.rate_details'])->find($user->id);
            $data = (new NursingResource($model))->toArray($request);
            break;

        case 'NURSING_HOME':
            // $model = NursingHome::with(['profile.province', 'profile.district', 'profile.subDistrict', 'notifications', 'readNotifications', 'unreadNotifications'])->find($user->id);
            $model = NursingHome::with([
                'profiles.province:id,name',
                'profiles.district:id,name',
                'profiles.subDistrict:id,name',
                'profiles.coverImage',
                'profiles.rates.rate_details',
                'profiles.subscriptions',
                'profiles.currentActiveSubscription',
                'notifications',
                'readNotifications',
                'unreadNotifications',
            ])->find($user->id);
            $data = (new NursingHomeResource($model))->toArray($request);
            break;

        case 'MEMBER':
            $model = Member::with(['profile.province', 'profile.district', 'profile.subDistrict', 'profile.subscriptions', 'profile.currentActiveSubscription', 'profile.coverImage', 'notifications', 'readNotifications', 'unreadNotifications'])->find($user->id);
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
    Route::post('/nursings', [NursingController::class, 'getNursing']);
    Route::prefix('nursing-home')->group(function() {
        Route::post('/create', [NursingHomeController::class, 'store']);
    });
    Route::post('/job/job-list', [JobController::class, 'getJobList']);
    Route::get('/job/{id}', [JobController::class, 'getJob']);

    Route::post('/member/create', [MemberController::class, 'create']);
    Route::post('/nursinghome/create', [NursingHomeController::class, 'register']);

    Route::post('/nursing/create', [NursingController::class, 'store']);
    Route::get('internal/nursing/{id}', [NursingController::class, 'getNursingById']);
    Route::post('/nursing/compare', [NursingController::class, 'compareNursing']);
    
    Route::post('/nursing-homes', [NursingHomeController::class, 'getNursingHomes']);
    Route::post('/nursing-homes/collections', [NursingHomeController::class, 'getCollections']);
    Route::post('/nursing-homes-listing', [NursingHomeController::class,'getNuringHomePagination']);
    Route::post('/nursings-listing', [NursingController::class, 'getNursingPagination']);
    Route::post('/nursing-home/compare', [NursingHomeController::class, 'compareNursingHome']);
});

Route::post('/debug', function (\Illuminate\Http\Request $request) {
    return response()->json($request->headers->all());
});

// LINE
Route::prefix('auth/line')->group(function() {
    Route::get('redirect', [LineLoginController::class, 'redirect']);
    Route::get('callback', [LineLoginController::class, 'callback']);
    Route::post('login', [LineLoginController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->prefix('auth/line')->group(function() {
    Route::post('bind', [LineLoginController::class, 'bind']);
    Route::post('unbind', [LineLoginController::class, 'unbind']);
});

// Analytics - Public (no auth, profile_view only with duplicate prevention)
Route::prefix('analytics')->group(function () {
    Route::post('/track-view', [AnalyticsController::class, 'logPublicAction']);
});

// Analytics - Log action & My stats (all authenticated users)
Route::middleware(['auth:sanctum'])->prefix('analytics')->group(function () {
    Route::post('/log', [AnalyticsController::class, 'logAction']);
    Route::get('/my-stats', [AnalyticsController::class, 'myStats']);
    Route::get('/my-stats/timeseries', [AnalyticsController::class, 'timeseries']);
    Route::get('/my-overview', [AnalyticsController::class, 'myOverviewStats']);
});

// Analytics - Reports (internal API only)
Route::middleware(['verify.internal.token'])->prefix('analytics')->group(function () {
    // Today vs Yesterday comparison
    Route::get('/comparison', [AnalyticsController::class, 'comparison']);

    // Daily stats for date range
    Route::get('/daily', [AnalyticsController::class, 'dailyStats']);

    // Monthly stats
    Route::get('/monthly', [AnalyticsController::class, 'monthlyStats']);

    // Stats by action type
    Route::get('/by-action', [AnalyticsController::class, 'statsByAction']);

    // Dashboard for a subject
    Route::get('/dashboard/{subjectId}/{subjectType}', [AnalyticsController::class, 'dashboard'])
        ->where('subjectId', '[0-9]+')
        ->where('subjectType', 'nursing|nursing_home');

    // Top performing subjects
    Route::get('/top-subjects', [AnalyticsController::class, 'topSubjects']);

    // Chart data (daily)
    Route::get('/chart', [AnalyticsController::class, 'chartData']);

    // Chart summary (weekly & monthly)
    Route::get('/chart-summary', [AnalyticsController::class, 'chartSummary']);
});

Route::middleware(['auth:sanctum'])->prefix('subscription')->group(function() {
    Route::post('/submit', [SubscriptionRequestController::class, 'submit']);
});