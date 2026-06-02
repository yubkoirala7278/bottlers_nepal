<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WarehouseLocationController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\BulkOperationController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Warehouse Matrix View
    Route::get('/warehouse-matrix-full', [WarehouseLocationController::class, 'matrixFull'])
        ->name('warehouse.matrix.full');
    Route::get('/warehouse-matrix', [WarehouseLocationController::class, 'matrix'])->name('warehouse.matrix');
    Route::get('/warehouse-matrix-data', [WarehouseLocationController::class, 'getMatrixData'])->name('warehouse.matrix.data');

    // Product Management (Admin only)
    Route::resource('products', ProductController::class)->middleware('role:admin');

    // Batch Management (Admin only)
    Route::resource('batches', BatchController::class)->middleware('role:admin');

    // Inbound Management (Admin and Inbound Staff)
    Route::middleware('role:admin,inbound_staff')->group(function () {
        Route::get('/inbound', [InboundController::class, 'index'])->name('inbound.index');
        Route::post('/inbound/find-place', [InboundController::class, 'findPlace'])->name('inbound.find-place');
        Route::post('/inbound/store', [InboundController::class, 'store'])->name('inbound.store');
        Route::post('/inbound/latest-batches', [InboundController::class, 'getLatestBatches'])->name('inbound.latest-batches');
    });

    // Outbound Management (Admin and Outbound Staff)
    Route::middleware('role:admin,outbound_staff')->group(function () {
        Route::get('/outbound', [OutboundController::class, 'index'])->name('outbound.index');
        Route::post('/outbound/oldest-batch', [OutboundController::class, 'getOldestBatch'])->name('outbound.oldest-batch');
        Route::post('/outbound/pickup', [OutboundController::class, 'pickup'])->name('outbound.pickup');
    });

    // Admin Bulk Operations
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/bulk-inbound', [BulkOperationController::class, 'inbound'])->name('bulk.inbound');
        Route::post('/bulk-inbound', [BulkOperationController::class, 'processBulkInbound'])->name('bulk.inbound.process');
        Route::get('/bulk-outbound', [BulkOperationController::class, 'outbound'])->name('bulk.outbound');
        Route::post('/bulk-outbound/locations', [BulkOperationController::class, 'getBatchLocations'])->name('bulk.outbound.locations');
        Route::post('/bulk-outbound', [BulkOperationController::class, 'processBulkOutbound'])->name('bulk.outbound.process');
        Route::get('/reservations', [BulkOperationController::class, 'reservations'])->name('reservations');
        Route::post('/reservations', [BulkOperationController::class, 'storeReservation'])->name('reservations.store');
        Route::delete('/reservations/{id}', [BulkOperationController::class, 'deleteReservation'])->name('reservations.delete');
    });
});

Route::post('/test-inbound', function (Request $request) {
    return response()->json(['success' => true, 'data' => $request->all()]);
});
