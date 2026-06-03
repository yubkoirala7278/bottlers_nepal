<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Inventory;
use App\Models\Reservation;
use App\Models\WarehouseLocation;
use App\Models\Batch;

class AdminController extends Controller
{
    /**
     * GET /admin/get-inventory-with-batches
     *
     * Returns every inventory record (qty > 0) merged with any
     * reservation that does not already have a matching inventory row.
     */
    public function getInventoryWithBatches(): JsonResponse
    {
        $inventory = Inventory::with(['batch', 'batch.product', 'warehouseLocation'])
            ->where('quantity', '>', 0)
            ->get()
            ->map(fn($item) => [
                'warehouse_location_id' => $item->warehouse_location_id,
                'batch_id'              => $item->batch_id,
                'batch_number'          => $item->batch->batch_number,
                'product_id'            => $item->batch->product_id,
                'quantity'              => $item->quantity,
                'depth_positions'       => $item->depth_positions,
                'is_reserved'           => false,
            ]);

        $reservations = Reservation::with(['warehouseLocation', 'product', 'batch'])
            ->get()
            ->map(fn($res) => [
                'warehouse_location_id' => $res->warehouse_location_id,
                'is_reserved'           => true,
                'reserved_for'          => $res->batch_id
                    ? $res->batch->product->name . ' ' . $res->batch->product->sku . ' (Batch: ' . $res->batch->batch_number . ')'
                    : $res->product->name . ' ' . $res->product->sku,
            ]);

        // Merge: only add a reservation row when no inventory row exists for that location
        $result = $inventory->toArray();

        foreach ($reservations as $reservation) {
            $locationAlreadyPresent = collect($result)
                ->contains('warehouse_location_id', $reservation['warehouse_location_id']);

            if (!$locationAlreadyPresent) {
                $result[] = $reservation;
            }
        }

        return response()->json($result);
    }

    /**
     * GET /admin/get-all-locations
     *
     * Returns all warehouse locations.
     */
    public function getAllLocations(): JsonResponse
    {
        return response()->json(WarehouseLocation::all());
    }

    /**
     * POST /admin/get-location-depths
     *
     * Returns the depth positions and batch number for a given
     * location code + batch ID combination.
     */
    public function getLocationDepths(Request $request): JsonResponse
    {
        $location = WarehouseLocation::where('location_code', $request->location_code)
            ->firstOrFail();

        $inventory = Inventory::where('warehouse_location_id', $location->id)
            ->where('batch_id', $request->batch_id)
            ->first();

        return response()->json([
            'depths'       => $inventory ? ($inventory->depth_positions ?: []) : [],
            'batch_number' => $inventory ? $inventory->batch->batch_number : null,
        ]);
    }

    /**
     * GET /admin/get-batches-by-product/{productId}
     *
     * Returns batches belonging to a product, newest first.
     */
    public function getBatchesByProduct(int $productId): JsonResponse
    {
        $batches = Batch::where('product_id', $productId)
            ->orderByDesc('created_at')
            ->get(['id', 'batch_number', 'production_date']);

        return response()->json($batches);
    }

    /**
     * GET /admin/get-reservation/{id}
     *
     * Returns a single reservation with its related location, product and batch.
     */
    public function getReservation(int $id): JsonResponse
    {
        $reservation = Reservation::with(['warehouseLocation', 'product', 'batch'])
            ->findOrFail($id);

        return response()->json([
            'id'               => $reservation->id,
            'location_code'    => $reservation->warehouseLocation->location_code,
            'reservation_type' => $reservation->reservation_type,
            'product_id'       => $reservation->product_id,
            'batch_id'         => $reservation->batch_id,
        ]);
    }
}