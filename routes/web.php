<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController;

// Change root to redirect to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/bookings', [AdminDashboardController::class, 'storeBooking'])->name('bookings.store');
    Route::post('/event-bookings', [AdminDashboardController::class, 'storeEventBooking'])->name('bookings.event_store');
    Route::get('/floor-plan', [AdminDashboardController::class, 'floorPlan'])->name('floor_plan');
    Route::post('/floor-plan/update', [AdminDashboardController::class, 'updateCoordinates'])->name('update_coordinates');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
    
    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');

    // Master Data Management
    Route::get('/master', [\App\Http\Controllers\MasterController::class, 'index'])->name('master.index');
    Route::post('/master/categories', [\App\Http\Controllers\MasterController::class, 'storeCategory'])->name('master.category.store');
    Route::put('/master/categories/{id}', [\App\Http\Controllers\MasterController::class, 'updateCategory'])->name('master.category.update');
    Route::delete('/master/categories/{id}', [\App\Http\Controllers\MasterController::class, 'destroyCategory'])->name('master.category.destroy');
    Route::post('/master/levels', [\App\Http\Controllers\MasterController::class, 'storeLevel'])->name('master.level.store');
    Route::put('/master/levels/{id}', [\App\Http\Controllers\MasterController::class, 'updateLevel'])->name('master.level.update');
    Route::delete('/master/levels/{id}', [\App\Http\Controllers\MasterController::class, 'destroyLevel'])->name('master.level.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
