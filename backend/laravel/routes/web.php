<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NursingHomeController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\EmployeeController;
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
        Route::get('/{id}/edit', [NursingHomeController::class, 'edit'])->where('id', '[0-9]+')->name('nursing-home.edit');
        Route::get('/create', [NursingHomeController::class, 'create'])->name('nursing-home.create');
        Route::post('/create', [NursingHomeController::class, 'store'])->name('nursing-home.store');
    });

    Route::prefix('employee')->group(function() {
        Route::get('create', [EmployeeController::class, 'create'])->name('employee.create');
        Route::post('create', [EmployeeController::class, 'store'])->name('employee.store');
    });
    
});
