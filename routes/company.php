<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Company\DashboardController;
use App\Http\Controllers\Company\TechnologiesController;
use App\Http\Controllers\Company\ProductsController;
use App\Http\Controllers\Company\SuppliersController;
use App\Http\Controllers\Company\WilayasController;
use App\Http\Controllers\Company\CountriesController;

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

    Route::get('/technologies', [TechnologiesController::class, 'index'])->middleware('check.permission:company.technologies.index')->name('company.technologies.index');
    Route::get('/technologies/research', [TechnologiesController::class, 'researchPage'])->middleware('check.permission:company.technologies.index')->name('company.technologies.research-page');
    Route::post('/technologies/{technology}/research', [TechnologiesController::class, 'research'])->middleware('check.permission:company.technologies.research')->name('company.technologies.research');

    Route::get('/products', [ProductsController::class, 'index'])->middleware('check.permission:company.products.index')->name('company.products.index');

    Route::get('/wilayas', [WilayasController::class, 'index'])->middleware('check.permission:company.wilayas.index')->name('company.wilayas.index');
    Route::get('/countries', [CountriesController::class, 'index'])->middleware('check.permission:company.countries.index')->name('company.countries.index');

    Route::get('/suppliers', [SuppliersController::class, 'index'])->middleware('check.permission:company.suppliers.index')->name('company.suppliers.index');
});


