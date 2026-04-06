<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\AuthController::class, 'dashboard'])->name('dashboard');
Route::get('/users', [\App\Http\Controllers\AuthController::class, 'users'])->name('users.index');

Route::get('/select-profile', [\App\Http\Controllers\AuthController::class, 'selectProfileView'])->name('select-profile.get');
Route::post('/select-profile', [\App\Http\Controllers\AuthController::class, 'selectProfilePost'])->name('select-profile.post');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Reports
Route::get('/report/history-attendance', [\App\Http\Controllers\ReportController::class, 'historyAttendance'])->name('report.history');
Route::get('/report/history-attendance/export', [\App\Http\Controllers\ReportController::class, 'exportExcel'])->name('report.history.export');
Route::get('/report/ftw', [\App\Http\Controllers\ReportController::class, 'ftw'])->name('report.ftw');

// Geofence Management
Route::get('/report/geofence', [\App\Http\Controllers\ReportController::class, 'geofence'])->name('report.geofence');
Route::post('/report/geofence', [\App\Http\Controllers\ReportController::class, 'storeGeofence'])->name('report.geofence.store');
Route::put('/report/geofence/{id}', [\App\Http\Controllers\ReportController::class, 'updateGeofence'])->name('report.geofence.update');
Route::delete('/report/geofence/{id}', [\App\Http\Controllers\ReportController::class, 'destroyGeofence'])->name('report.geofence.destroy');

// Employee Management
Route::get('/employees', [\App\Http\Controllers\EmployeeController::class, 'index'])->name('employees.index');
Route::post('/employees', [\App\Http\Controllers\EmployeeController::class, 'store'])->name('employees.store');
Route::put('/employees/{id}', [\App\Http\Controllers\EmployeeController::class, 'update'])->name('employees.update');
Route::delete('/employees/{id}', [\App\Http\Controllers\EmployeeController::class, 'destroy'])->name('employees.destroy');

// Master Data Management
Route::prefix('master')->name('master.')->group(function () {
    // Shifts
    Route::get('/shifts', [\App\Http\Controllers\MasterController::class, 'shifts'])->name('shifts');
    Route::post('/shifts', [\App\Http\Controllers\MasterController::class, 'storeShift'])->name('shifts.store');
    Route::put('/shifts/{id}', [\App\Http\Controllers\MasterController::class, 'updateShift'])->name('shifts.update');
    Route::delete('/shifts/{id}', [\App\Http\Controllers\MasterController::class, 'destroyShift'])->name('shifts.destroy');

    // Departments
    Route::get('/departments', [\App\Http\Controllers\MasterController::class, 'departments'])->name('departments');
    Route::post('/departments', [\App\Http\Controllers\MasterController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/departments/{id}', [\App\Http\Controllers\MasterController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{id}', [\App\Http\Controllers\MasterController::class, 'destroyDepartment'])->name('departments.destroy');

    // Divisions
    Route::get('/divisions', [\App\Http\Controllers\MasterController::class, 'divisions'])->name('divisions');
    Route::post('/divisions', [\App\Http\Controllers\MasterController::class, 'storeDivision'])->name('divisions.store');
    Route::put('/divisions/{id}', [\App\Http\Controllers\MasterController::class, 'updateDivision'])->name('divisions.update');
    Route::delete('/divisions/{id}', [\App\Http\Controllers\MasterController::class, 'destroyDivision'])->name('divisions.destroy');

    // Districts
    Route::get('/districts', [\App\Http\Controllers\MasterController::class, 'districts'])->name('districts');
    Route::post('/districts', [\App\Http\Controllers\MasterController::class, 'storeDistrict'])->name('districts.store');
    Route::put('/districts/{id}', [\App\Http\Controllers\MasterController::class, 'updateDistrict'])->name('districts.update');
    Route::delete('/districts/{id}', [\App\Http\Controllers\MasterController::class, 'destroyDistrict'])->name('districts.destroy');

    // Positions
    Route::get('/positions', [\App\Http\Controllers\MasterController::class, 'positions'])->name('positions');
    Route::post('/positions', [\App\Http\Controllers\MasterController::class, 'storePosition'])->name('positions.store');
    Route::put('/positions/{id}', [\App\Http\Controllers\MasterController::class, 'updatePosition'])->name('positions.update');
    Route::delete('/positions/{id}', [\App\Http\Controllers\MasterController::class, 'destroyPosition'])->name('positions.destroy');

    // Mitra Kerja (Subcontractor)
    Route::get('/mitra', [\App\Http\Controllers\MasterController::class, 'mitra'])->name('mitra');
    Route::post('/mitra', [\App\Http\Controllers\MasterController::class, 'storeMitra'])->name('mitra.store');
    Route::put('/mitra/{id}', [\App\Http\Controllers\MasterController::class, 'updateMitra'])->name('mitra.update');
    Route::delete('/mitra/{id}', [\App\Http\Controllers\MasterController::class, 'destroyMitra'])->name('mitra.destroy');

});

// Roster Management
Route::get('/rosters', [\App\Http\Controllers\RosterController::class, 'index'])->name('rosters.index');
Route::get('/rosters/export', [\App\Http\Controllers\RosterController::class, 'exportCSV'])->name('rosters.export');
Route::post('/rosters/import', [\App\Http\Controllers\RosterController::class, 'importCSV'])->name('rosters.import');
Route::post('/rosters', [\App\Http\Controllers\RosterController::class, 'store'])->name('rosters.store');
Route::delete('/rosters/{id}', [\App\Http\Controllers\RosterController::class, 'destroy'])->name('rosters.destroy');

