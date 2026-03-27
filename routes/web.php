<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FloorController;

// Redirect root to dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    // ── Dashboard ────────────────────────────────────────────────────────
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/bookings', [AdminDashboardController::class, 'storeBooking'])->name('bookings.store');
    Route::post('/event-bookings', [AdminDashboardController::class, 'storeEventBooking'])->name('bookings.event_store');
    Route::put('/bookings/{id}', [AdminDashboardController::class, 'updateBooking'])->name('bookings.update');

    // ── Floor Management (menggantikan /floor-plan lama) ─────────────────
    Route::get('/floor', [FloorController::class, 'index'])->name('floor.index');
    Route::post('/floor/layout', [FloorController::class, 'saveLayout'])->name('floor.layout.save');

    // Areas
    Route::post('/floor/areas', [FloorController::class, 'storeArea'])->name('floor.area.store');
    Route::put('/floor/areas/{id}', [FloorController::class, 'updateArea'])->name('floor.area.update');
    Route::delete('/floor/areas/{id}', [FloorController::class, 'destroyArea'])->name('floor.area.destroy');

    // Tables
    Route::post('/floor/tables', [FloorController::class, 'storeTable'])->name('floor.table.store');
    Route::put('/floor/tables/{id}', [FloorController::class, 'updateTable'])->name('floor.table.update');
    Route::delete('/floor/tables/{id}', [FloorController::class, 'destroyTable'])->name('floor.table.destroy');
    Route::patch('/floor/tables/{id}/min-spending', [FloorController::class, 'updateMinSpending'])->name('floor.table.min_spending');

    // Legacy floor-plan route (redirect ke baru) untuk tidak break link lama
    Route::get('/floor-plan', fn() => redirect()->route('floor.index'))->name('floor_plan');
    Route::post('/floor-plan/update', [AdminDashboardController::class, 'updateCoordinates'])->name('update_coordinates');

    // ── Customers ────────────────────────────────────────────────────────
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');

    // ── Demographics ──────────────────────────────────────────────────────
    Route::get('/demographics', [\App\Http\Controllers\DemographicController::class, 'index'])->name('demographics');

    // ── User Management ───────────────────────────────────────────────────
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');

    // ── Master Data ───────────────────────────────────────────────────────
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
