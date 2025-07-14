<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Company\DashboardController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('company')->middleware(['auth', 'check.company', 'handle.inertia'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->middleware('check.permission:company.dashboard.index')->name('company.dashboard.index');
});


