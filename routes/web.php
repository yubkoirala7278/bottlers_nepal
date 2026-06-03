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
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Root Redirect
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Warehouse Matrix
    |----------------------------------------------------------------------
    */
    Route::prefix('warehouse-matrix')->name('warehouse.matrix')->group(function () {
        Route::get('/', [WarehouseLocationController::class, 'matrix'])->name('');
        Route::get('/full', [WarehouseLocationController::class, 'matrixFull'])->name('.full');
        Route::get('/data', [WarehouseLocationController::class, 'getMatrixData'])->name('.data');
    });

    /*
    |----------------------------------------------------------------------
    | Profile Management (All Authenticated Users)
    |----------------------------------------------------------------------
    */
    Route::prefix('profile')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'profile'])->name('profile');
        Route::put('/', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [UserController::class, 'updatePassword'])->name('password.update');
    });

    /*
    |----------------------------------------------------------------------
    | Inbound Management (Admin + Inbound Staff)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin,inbound_staff')
        ->prefix('inbound')
        ->name('inbound.')
        ->group(function () {
            Route::get('/', [InboundController::class, 'index'])->name('index');
            Route::post('/find-place', [InboundController::class, 'findPlace'])->name('find-place');
            Route::post('/store', [InboundController::class, 'store'])->name('store');
            Route::post('/latest-batches', [InboundController::class, 'getLatestBatches'])->name('latest-batches');
        });

    /*
    |----------------------------------------------------------------------
    | Outbound Management (Admin + Outbound Staff)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin,outbound_staff')
        ->prefix('outbound')
        ->name('outbound.')
        ->group(function () {
            Route::get('/', [OutboundController::class, 'index'])->name('index');
            Route::post('/oldest-batch', [OutboundController::class, 'getOldestBatch'])->name('oldest-batch');
            Route::post('/pickup', [OutboundController::class, 'pickup'])->name('pickup');
        });

    /*
    |----------------------------------------------------------------------
    | Admin-Only Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {

        // Resource routes
        Route::resource('products', ProductController::class);
        Route::resource('batches', BatchController::class);

        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Admin API & Bulk Operations
        Route::prefix('admin')->name('admin.')->group(function () {

            // Inventory & Location Helpers
            Route::get('/get-inventory-with-batches', [AdminController::class, 'getInventoryWithBatches'])
                ->name('get.inventory.with.batches');
            Route::get('/get-all-locations', [AdminController::class, 'getAllLocations'])
                ->name('get.all.locations');
            Route::post('/get-location-depths', [AdminController::class, 'getLocationDepths'])
                ->name('get.location.depths');
            Route::get('/get-batches-by-product/{productId}', [AdminController::class, 'getBatchesByProduct'])
                ->name('get.batches');

            // Bulk Inbound
            Route::get('/bulk-inbound', [BulkOperationController::class, 'inbound'])->name('bulk.inbound');
            Route::post('/bulk-inbound', [BulkOperationController::class, 'processBulkInbound'])->name('bulk.inbound.process');

            // Bulk Outbound
            Route::get('/bulk-outbound', [BulkOperationController::class, 'outbound'])->name('bulk.outbound');
            Route::post('/bulk-outbound/locations', [BulkOperationController::class, 'getBatchLocations'])->name('bulk.outbound.locations');
            Route::post('/bulk-outbound', [BulkOperationController::class, 'processBulkOutbound'])->name('bulk.outbound.process');

            // Reservations
            Route::get('/reservations', [BulkOperationController::class, 'reservations'])->name('reservations');
            Route::post('/reservations', [BulkOperationController::class, 'storeReservation'])->name('reservations.store');
            Route::delete('/reservations/{id}', [BulkOperationController::class, 'deleteReservation'])->name('reservations.delete');
            Route::get('/get-reservation/{id}', [AdminController::class, 'getReservation'])->name('get.reservation');
        });
    });
});