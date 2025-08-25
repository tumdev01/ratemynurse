<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NursingHomeController;
use App\Http\Controllers\ProvinceController;
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

    Route::get('/nursing-home', [NursingHomeController::class, 'index'])->name('nursinghome.index');
    Route::get('/nursing-homes/', [NursingHomeController::class, 'getNursingHomePagination'])->name('nursing-homes.data');
    Route::get('/nursing-home/{id}/edit', [NursingHomeController::class, 'edit'])->where('id', '[0-9]+')->name('nursing-home.edit');
    Route::get('/nursing-home/create', [NursingHomeController::class, 'create'])->name('nursing-home.create');
    Route::post('/nursing-home/create', [NursingHomeController::class, 'store'])->name('nursing-home.store');
});
