<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Nursing
    Route::post('/nursings', [NursingController::class, 'getNursing']);
    Route::post('/nursings/all', [NursingController::class, 'getNursingPagination']);
    Route::post('/nursing-homes', [NursingHomeController::class, 'getNursingHomes']);
});
