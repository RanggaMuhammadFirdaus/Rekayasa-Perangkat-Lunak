<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\PresenceController;
use App\Http\Livewire\PresenceForm;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::middleware('auth')->group(function () {
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        // positions
        Route::resource('/positions', PositionController::class)->only(['index', 'create']);
        Route::get('/positions/edit', [PositionController::class, 'edit'])->name('positions.edit');
        // employees
        Route::resource('/employees', EmployeeController::class)->only(['index', 'create']);
        Route::get('/employees/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        // attendances (absensi)
        Route::resource('/attendances', AttendanceController::class)->only(['index', 'create']);
        Route::get('/attendances/edit', [AttendanceController::class, 'edit'])->name('attendances.edit');

        // presences (kehadiran)
        Route::resource('/presences', PresenceController::class)->only(['index']);

        Route::get('/presences/{attendance}', [PresenceController::class, 'show'])->name('presences.show');
        // not present data
        Route::get('/presences/{attendance}/not-present', [PresenceController::class, 'notPresent'])->name('presences.not-present');
        Route::post('/presences/{attendance}/not-present', [PresenceController::class, 'notPresent']);
        Route::get('/presences/{attendance}/guest', [PresenceController::class, 'create'])->name('presences.guest');
        Route::post('/presences/{attendance}/guest/create', [PresenceController::class, 'guest'])->name('guest.create');

        // present (url untuk menambahkan/mengubah user yang tidak hadir menjadi hadir)
        Route::post('/presences/{attendance}/present', [PresenceController::class, 'presentUser'])->name('presences.present');
        Route::post('/presences/{attendance}/acceptPermission', [PresenceController::class, 'acceptPermission'])->name('presences.acceptPermission');
        // employees permissions

        Route::get('/presences/{attendance}/permissions', [PresenceController::class, 'permissions'])->name('presences.permissions');
    });

    Route::middleware('role:user')->name('home.')->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('index');


        Route::get('/absensi/{attendance}', [HomeController::class, 'show'])->name('show');
        Route::get('/absensi/{attendance}/permission', [HomeController::class, 'permission'])->name('permission');
    });

    Route::delete('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

Route::middleware('guest')->group(function () {
    // auth
    Route::get('/login', [AuthController::class, 'index'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'authenticate']);
});
