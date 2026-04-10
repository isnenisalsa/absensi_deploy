<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\AuthController::class, 'dashboard'])->name('dashboard');
Route::get('/users', [\App\Http\Controllers\AuthController::class, 'users'])->name('users.index');
Route::patch('/users/{nrp}/status', [\App\Http\Controllers\AuthController::class, 'updateUserStatus'])->name('users.update-status');
Route::delete('/users/{nrp}', [\App\Http\Controllers\AuthController::class, 'destroyUser'])->name('users.destroy');

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
Route::get('/report/ftw/export', [\App\Http\Controllers\ReportController::class, 'exportFtw'])->name('report.ftw.export');

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
    Route::post('/departments/import', [\App\Http\Controllers\MasterController::class, 'importDepartments'])->name('departments.import');
    Route::put('/departments/{id}', [\App\Http\Controllers\MasterController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/bulk', [\App\Http\Controllers\MasterController::class, 'bulkDestroyDepartments'])->name('departments.bulk-destroy');
    Route::delete('/departments/{id}', [\App\Http\Controllers\MasterController::class, 'destroyDepartment'])->name('departments.destroy');

    // Divisions
    Route::get('/divisions', [\App\Http\Controllers\MasterController::class, 'divisions'])->name('divisions');
    Route::post('/divisions', [\App\Http\Controllers\MasterController::class, 'storeDivision'])->name('divisions.store');
    Route::post('/divisions/import', [\App\Http\Controllers\MasterController::class, 'importDivisions'])->name('divisions.import');
    Route::put('/divisions/{id}', [\App\Http\Controllers\MasterController::class, 'updateDivision'])->name('divisions.update');
    Route::delete('/divisions/bulk', [\App\Http\Controllers\MasterController::class, 'bulkDestroyDivisions'])->name('divisions.bulk-destroy');
    Route::delete('/divisions/{id}', [\App\Http\Controllers\MasterController::class, 'destroyDivision'])->name('divisions.destroy');

    // Positions
    Route::get('/positions', [\App\Http\Controllers\MasterController::class, 'positions'])->name('positions');
    Route::post('/positions', [\App\Http\Controllers\MasterController::class, 'storePosition'])->name('positions.store');
    Route::put('/positions/{id}', [\App\Http\Controllers\MasterController::class, 'updatePosition'])->name('positions.update');
    Route::delete('/positions/bulk', [\App\Http\Controllers\MasterController::class, 'bulkDestroyPositions'])->name('positions.bulk-destroy');
    Route::delete('/positions/{id}', [\App\Http\Controllers\MasterController::class, 'destroyPosition'])->name('positions.destroy');
    Route::post('/positions/import', [\App\Http\Controllers\MasterController::class, 'importPositions'])->name('positions.import');

    // Lokasi Kerja (Work Location / Geofence)


    // Mitra Kerja (Subcontractor)
    Route::get('/mitra', [\App\Http\Controllers\MasterMitraController::class, 'index'])->name('mitra');
    Route::post('/mitra', [\App\Http\Controllers\MasterMitraController::class, 'store'])->name('mitra.store');
    Route::put('/mitra/{id}', [\App\Http\Controllers\MasterMitraController::class, 'update'])->name('mitra.update');
    Route::delete('/mitra/{id}', [\App\Http\Controllers\MasterMitraController::class, 'destroy'])->name('mitra.destroy');

});

// Roster Management
Route::get('/rosters', [\App\Http\Controllers\RosterController::class, 'index'])->name('rosters.index');
Route::get('/rosters/export', [\App\Http\Controllers\RosterController::class, 'exportCSV'])->name('rosters.export');
Route::post('/rosters/import', [\App\Http\Controllers\RosterController::class, 'importCSV'])->name('rosters.import');
Route::post('/rosters', [\App\Http\Controllers\RosterController::class, 'store'])->name('rosters.store');
Route::delete('/rosters/{id}', [\App\Http\Controllers\RosterController::class, 'destroy'])->name('rosters.destroy');

