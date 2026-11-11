<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ReportController;
use App\Livewire\Dashboard;
use App\Livewire\Drivers\DriverList;
use App\Livewire\Fuel\FuelList;
use App\Livewire\Repairs\RepairList;
use App\Livewire\Reports\ReportPage;
use App\Livewire\Settings\SettingsPage;
use App\Livewire\Trips\TripList;
use App\Livewire\Vehicles\VehicleDetail;
use App\Livewire\Vehicles\VehicleList;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('auth.login'))->name('login');
    Route::post('/login', LoginController::class)->name('login.post');
});

Route::post('/logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');

// Protected application routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/vehicles', VehicleList::class)->name('vehicles.index');
    Route::get('/vehicles/{vehicle}', VehicleDetail::class)->name('vehicles.show');
    Route::get('/drivers', DriverList::class)->name('drivers.index');
    Route::get('/trips', TripList::class)->name('trips.index');
    Route::get('/repairs', RepairList::class)->name('repairs.index');
    Route::get('/fuel', FuelList::class)->name('fuel.index');
    Route::get('/reports', ReportPage::class)->name('reports.index');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/settings', SettingsPage::class)->name('settings.index');
});
