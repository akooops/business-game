<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ProductsController;

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

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/



Route::middleware(['handle.inertia'])->group(function () {
    Route::get('auth/login', [AuthController::class, 'showLoginForm'])->middleware('guest')->name('auth.login');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('guest')->name('auth.login');
});

Route::middleware(['handle.inertia'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth')->name('auth.logout');
});

Route::prefix('admin')->middleware(['auth', 'handle.inertia'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->middleware('check.permission:admin.dashboard.index')->name('admin.dashboard.index');

    // Permissions
    Route::get('permissions', [PermissionsController::class, 'index'])->middleware('check.permission:admin.permissions.index')->name('admin.permissions.index');

    // Roles
    Route::get('roles', [RolesController::class, 'index'])->middleware('check.permission:admin.roles.index')->name('admin.roles.index');
    Route::get('roles/create', [RolesController::class, 'create'])->middleware('check.permission:admin.roles.store')->name('admin.roles.create');
    Route::post('roles', [RolesController::class, 'store'])->middleware('check.permission:admin.roles.store')->name('admin.roles.store');
    Route::get('roles/{role}', [RolesController::class, 'show'])->middleware('check.permission:admin.roles.show')->name('admin.roles.show');
    Route::get('roles/{role}/edit', [RolesController::class, 'edit'])->middleware('check.permission:admin.roles.update')->name('admin.roles.edit');
    Route::patch('roles/{role}', [RolesController::class, 'update'])->middleware('check.permission:admin.roles.update')->name('admin.roles.update');
    Route::delete('roles/{role}', [RolesController::class, 'destroy'])->middleware('check.permission:admin.roles.destroy')->name('admin.roles.destroy');

    // Users
    Route::get('users', [UsersController::class, 'index'])->middleware('check.permission:admin.users.index')->name('admin.users.index');
    Route::get('users/create', [UsersController::class, 'create'])->middleware('check.permission:admin.users.store')->name('admin.users.create');
    Route::post('users', [UsersController::class, 'store'])->middleware('check.permission:admin.users.store')->name('admin.users.store');
    Route::get('users/{user}', [UsersController::class, 'show'])->middleware('check.permission:admin.users.show')->name('admin.users.show');
    Route::get('users/{user}/edit', [UsersController::class, 'edit'])->middleware('check.permission:admin.users.update')->name('admin.users.edit');
    Route::patch('users/{user}', [UsersController::class, 'update'])->middleware('check.permission:admin.users.update')->name('admin.users.update');
    Route::delete('users/{user}', [UsersController::class, 'destroy'])->middleware('check.permission:admin.users.destroy')->name('admin.users.destroy');

    // Products
    Route::get('products', [ProductsController::class, 'index'])->middleware('check.permission:admin.products.index')->name('admin.products.index');
    Route::get('products/create', [ProductsController::class, 'create'])->middleware('check.permission:admin.products.store')->name('admin.products.create');
    Route::post('products', [ProductsController::class, 'store'])->middleware('check.permission:admin.products.store')->name('admin.products.store');
    Route::get('products/{product}', [ProductsController::class, 'show'])->middleware('check.permission:admin.products.show')->name('admin.products.show');
    Route::get('products/{product}/edit', [ProductsController::class, 'edit'])->middleware('check.permission:admin.products.update')->name('admin.products.edit');
    Route::patch('products/{product}', [ProductsController::class, 'update'])->middleware('check.permission:admin.products.update')->name('admin.products.update');
    Route::delete('products/{product}', [ProductsController::class, 'destroy'])->middleware('check.permission:admin.products.destroy')->name('admin.products.destroy');
});


