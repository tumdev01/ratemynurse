<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NursingHomeController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\JobController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Auth::routes(['register'=> false]);

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/nursing-homes/', [NursingHomeController::class, 'getNursingHomePagination'])->name('nursing-homes.data');

    Route::prefix('nursing-home')->group(function() {
        Route::get('/', [NursingHomeController::class, 'index'])->name('nursinghome.index');
        Route::get('/{id}/staffs', [NursingHomeController::class, 'editStaff'])->where('id', '[0-9]+')->name('nursing-home.edit-staff');
        Route::post('/{id}/staff/create', [NursingHomeController::class, 'createStaff'])->where('id', '[0-9]+')->name('nursing-home.crete-staff');
        Route::delete('/staff/{id}/delete', [NursingHomeController::class, 'deleteStaff'])->where('id', '[0-9]+')->name('nursing-home.delete-staff');

        Route::get('/{id}/edit', [NursingHomeController::class, 'edit'])->where('id', '[0-9]+')->name('nursing-home.edit');
        Route::post('/{id}/edit', [NursingHomeController::class, 'update'])->where('id', '[0-9]+')->name('nursing-home.update');
        Route::get('/create', [NursingHomeController::class, 'create'])->name('nursing-home.create');
        Route::post('/create', [NursingHomeController::class, 'store'])->name('nursing-home.store');
        Route::post('/image/{id}/cover', [NursingHomeController::class, 'updateCover'])->name('nursinghome.image.cover');

        Route::get('/{id}/rate', [NursingHomeController::class, 'review'])->where('id', '[0-9]+')->name('nursing-home.edit-rate');
        Route::post('/{id}/rate', [NursingHomeController::class, 'reviewCreate'])->where('id', '[0-9]+')->name('nursing-home.edit-rate.save');

    });

    Route::prefix('employee')->middleware('checkUserType:SUPERADMIN,ADMIN')->group(function() {
        Route::get('/', [EmployeeController::class, 'index'])->name('employee.index');
        Route::get('create', [EmployeeController::class, 'create'])->name('employee.create');
        Route::post('create', [EmployeeController::class, 'store'])->name('employee.store');
    });

    Route::prefix('member')->middleware('checkUserType:SUPERADMIN,ADMIN')->group(function () {
        Route::get('create', [MemberController::class, 'create'])->name('member.create');
    });

    Route::get('/jobs', [JobController::class, 'jobPagination'])->name('job.pagination');
    Route::prefix('job')->group(function() {
        Route::get('/', [JobController::class, 'index'])->name('job.index');
        Route::get('/create', [JobController::class, 'create'])->name('job.create');
        Route::post('/store', [JobController::class, 'store'])->name('job.store');
        Route::get('/{id}/edit', [JobController::class, 'edit'])->where('id', '[0-9]+')->name('job.edit');
        Route::post('/{id}/status-update', [JobController::class, 'updateStatus'])->where('id', '[0-9]+')->name('job.status-update');
    });
    
});


Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/'); // Redirect to your desired page after logout
})->name('logout');
