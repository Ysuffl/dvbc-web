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

    // ── Dashboard (Staff & Admin) ─────────────────────────────────────────
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/bookings', [AdminDashboardController::class, 'storeBooking'])->name('bookings.store');
    Route::post('/event-bookings', [AdminDashboardController::class, 'storeEventBooking'])->name('bookings.event_store');
    Route::put('/bookings/{id}', [AdminDashboardController::class, 'updateBooking'])->name('bookings.update');

    // ── Customers (Staff & Admin) ─────────────────────────────────────────
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import');

    // ── Admin Only Sections ───────────────────────────────────────────────
    Route::middleware(['role:admin'])->group(function () {
        // Floor Management
        Route::get('/floor', [FloorController::class, 'index'])->name('floor.index');
        Route::post('/floor/layout', [FloorController::class, 'saveLayout'])->name('floor.layout.save');
        Route::post('/floor/areas', [FloorController::class, 'storeArea'])->name('floor.area.store');
        Route::put('/floor/areas/{id}', [FloorController::class, 'updateArea'])->name('floor.area.update');
        Route::delete('/floor/areas/{id}', [FloorController::class, 'destroyArea'])->name('floor.area.destroy');
        Route::post('/floor/tables', [FloorController::class, 'storeTable'])->name('floor.table.store');
        Route::put('/floor/tables/{id}', [FloorController::class, 'updateTable'])->name('floor.table.update');
        Route::delete('/floor/tables/{id}', [FloorController::class, 'destroyTable'])->name('floor.table.destroy');
        Route::patch('/floor/tables/bulk/min-spending', [FloorController::class, 'bulkUpdateMinSpending'])->name('floor.table.bulk_min_spending');
        Route::patch('/floor/tables/{id}/min-spending', [FloorController::class, 'updateMinSpending'])->name('floor.table.min_spending');

        // Legacy floor-plan
        Route::get('/floor-plan', fn() => redirect()->route('floor.index'))->name('floor_plan');
        Route::post('/floor-plan/update', [AdminDashboardController::class, 'updateCoordinates'])->name('update_coordinates');

        // Demographics
        Route::get('/demographics', [\App\Http\Controllers\DemographicController::class, 'index'])->name('demographics');

        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{id}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Master Data
        Route::get('/master', [\App\Http\Controllers\MasterController::class, 'index'])->name('master.index');

        Route::post('/master/levels', [\App\Http\Controllers\MasterController::class, 'storeLevel'])->name('master.level.store');
        Route::put('/master/levels/{id}', [\App\Http\Controllers\MasterController::class, 'updateLevel'])->name('master.level.update');
        Route::delete('/master/levels/{id}', [\App\Http\Controllers\MasterController::class, 'destroyLevel'])->name('master.level.destroy');
        Route::post('/master/templates', [\App\Http\Controllers\MasterController::class, 'storeTemplate'])->name('master.template.store');
        Route::put('/master/templates/{id}', [\App\Http\Controllers\MasterController::class, 'updateTemplate'])->name('master.template.update');
        Route::delete('/master/templates/{id}', [\App\Http\Controllers\MasterController::class, 'destroyTemplate'])->name('master.template.destroy');
        Route::post('/master/tags', [\App\Http\Controllers\MasterController::class, 'storeTag'])->name('master.tag.store');
        Route::put('/master/tags/{id}', [\App\Http\Controllers\MasterController::class, 'updateTag'])->name('master.tag.update');
        Route::delete('/master/tags/{id}', [\App\Http\Controllers\MasterController::class, 'destroyTag'])->name('master.tag.destroy');

        Route::post('/master/tag-groups', [\App\Http\Controllers\MasterController::class, 'storeGroup'])->name('master.tag_group.store');
        Route::put('/master/tag-groups/{id}', [\App\Http\Controllers\MasterController::class, 'updateGroup'])->name('master.tag_group.update');
        Route::delete('/master/tag-groups/{id}', [\App\Http\Controllers\MasterController::class, 'destroyGroup'])->name('master.tag_group.destroy');
    });

    // ── Shared Admin & CS Sections ───────────────────────────────────────
    Route::middleware(['role:admin,cs'])->group(function () {
        // Broadcasting
        Route::get('/broadcast', [\App\Http\Controllers\BroadcastController::class, 'index'])->name('broadcast.index');
        Route::get('/broadcast/status', [\App\Http\Controllers\BroadcastController::class, 'getStatus'])->name('broadcast.status');
        Route::post('/broadcast/start', [\App\Http\Controllers\BroadcastController::class, 'startConnection'])->name('broadcast.start');
        Route::get('/broadcast/qr', [\App\Http\Controllers\BroadcastController::class, 'getQrCode'])->name('broadcast.qr');
        Route::post('/broadcast/disconnect', [\App\Http\Controllers\BroadcastController::class, 'disconnect'])->name('broadcast.disconnect');
        Route::post('/broadcast/send', [\App\Http\Controllers\BroadcastController::class, 'sendBroadcast'])->name('broadcast.send');
        Route::get('/broadcast/customers-by-tag', [\App\Http\Controllers\BroadcastController::class, 'customersByTag'])->name('broadcast.customers_by_tag');
    });
});

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';