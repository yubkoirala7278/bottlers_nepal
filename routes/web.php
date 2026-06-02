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
    Route::get('/admin/get-inventory-with-batches', function () {
        $inventory = \App\Models\Inventory::with(['batch', 'batch.product', 'warehouseLocation'])
            ->where('quantity', '>', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'warehouse_location_id' => $item->warehouse_location_id,
                    'batch_id' => $item->batch_id,
                    'batch_number' => $item->batch->batch_number,
                    'product_id' => $item->batch->product_id,
                    'quantity' => $item->quantity,
                    'depth_positions' => $item->depth_positions,
                    'is_reserved' => false,
                ];
            });

        // Also get reservations
        $reservations = \App\Models\Reservation::with(['warehouseLocation', 'product', 'batch'])
            ->get()
            ->map(function ($res) {
                return [
                    'warehouse_location_id' => $res->warehouse_location_id,
                    'is_reserved' => true,
                    'reserved_for' => $res->batch_id ?
                        $res->batch->product->name . ' ' . $res->batch->product->sku . ' (Batch: ' . $res->batch->batch_number . ')' :
                        $res->product->name . ' ' . $res->product->sku,
                ];
            });

        // Merge inventory and reservations
        $result = $inventory->toArray();
        foreach ($reservations as $res) {
            $existing = false;
            foreach ($result as &$inv) {
                if ($inv['warehouse_location_id'] == $res['warehouse_location_id']) {
                    $existing = true;
                    break;
                }
            }
            if (!$existing) {
                $result[] = $res;
            }
        }

        return response()->json($result);
    })->name('admin.get.inventory.with.batches');
    Route::get('/admin/get-all-locations', function () {
        return response()->json(\App\Models\WarehouseLocation::all());
    })->name('admin.get.all.locations');

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
        Route::get('/get-batches-by-product/{productId}', function ($productId) {
            $batches = \App\Models\Batch::where('product_id', $productId)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'batch_number', 'production_date']);
            return response()->json($batches);
        })->name('get.batches');

        Route::get('/get-reservation/{id}', function ($id) {
            $reservation = \App\Models\Reservation::with(['warehouseLocation', 'product', 'batch'])->findOrFail($id);
            return response()->json([
                'id' => $reservation->id,
                'location_code' => $reservation->warehouseLocation->location_code,
                'reservation_type' => $reservation->reservation_type,
                'product_id' => $reservation->product_id,
                'batch_id' => $reservation->batch_id,
            ]);
        })->name('get.reservation');
    });
});

// In routes/web.php
Route::middleware('auth')->post('/admin/get-location-depths', function (Request $request) {
    $location = \App\Models\WarehouseLocation::where('location_code', $request->location_code)->first();
    $inventory = \App\Models\Inventory::where('warehouse_location_id', $location->id)
        ->where('batch_id', $request->batch_id)
        ->first();

    return response()->json([
        'depths' => $inventory ? ($inventory->depth_positions ?: []) : [],
        'batch_number' => $inventory ? $inventory->batch->batch_number : null
    ]);
})->name('admin.get.location.depths');

Route::post('/test-inbound', function (Request $request) {
    return response()->json(['success' => true, 'data' => $request->all()]);
});
