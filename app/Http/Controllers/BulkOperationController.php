<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Batch;
use App\Models\WarehouseLocation;
use App\Models\Inventory;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class BulkOperationController extends Controller
{
    public function inbound()
    {
        $products = Product::orderBy('name')->orderBy('sku')->get();
        $locations = WarehouseLocation::orderBy('level')->orderBy('height')->get();
        return view('admin.bulk-inbound', compact('products', 'locations'));
    }

    // Update the processBulkInbound method in BulkOperationController.php
    public function processBulkInbound(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'required|exists:batches,id',
            'location_code' => 'required|exists:warehouse_locations,location_code',
            'quantity' => 'required|integer|min:1|max:50',
        ]);

        $location = WarehouseLocation::where('location_code', $request->location_code)->first();
        $batch = Batch::find($request->batch_id);

        // Validate space
        if ($location->available_space < $request->quantity) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Not enough space at this location.']);
            }
            return back()->with('error', 'Not enough space at this location.');
        }

        // Check for mixed batches
        $existingInventory = Inventory::where('warehouse_location_id', $location->id)
            ->where('batch_id', '!=', $request->batch_id)
            ->first();

        if ($existingInventory) {
            $message = 'Cannot mix different batches in the same location.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);
        }

        DB::beginTransaction();
        try {
            $inventory = Inventory::firstOrNew([
                'batch_id' => $request->batch_id,
                'warehouse_location_id' => $location->id,
            ]);

            $depthPositions = $inventory->depth_positions ?: [];
            $currentFill = $location->current_fill;

            // DEPTH-FIRST PLACEMENT
            // Places items from highest depth number down to lowest
            // Example: If location has max_depth=50 and current_fill=25
            // New items will be placed at depths: 25, 24, 23, 22... (as we go down)
            // This ensures the lowest depth (1) is filled last

            for ($i = 0; $i < $request->quantity; $i++) {
                // Calculate next available depth from the top (highest number)
                $newDepth = $location->max_depth - $currentFill - $i;
                if ($newDepth >= 1) {
                    $depthPositions[] = $newDepth;
                }
            }

            // Sort depths in descending order (highest first) for LIFO picking later
            rsort($depthPositions);

            $inventory->quantity = ($inventory->quantity ?: 0) + $request->quantity;
            $inventory->depth_positions = $depthPositions;
            $inventory->save();

            // Update location fill level
            $location->current_fill += $request->quantity;
            $location->save();

            DB::commit();

            // Log the placement for debugging
            \Log::info('Bulk inbound placement', [
                'location' => $request->location_code,
                'quantity' => $request->quantity,
                'previous_fill' => $currentFill,
                'new_fill' => $location->current_fill,
                'depths_occupied' => $depthPositions
            ]);

            $message = "Successfully placed {$request->quantity} items at {$request->location_code} (Depth-first: positions " . implode(', ', array_slice($depthPositions, -$request->quantity)) . ")";

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('admin.bulk.inbound')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Failed to place items: ' . $e->getMessage();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);
        }
    }

    public function outbound()
    {
        $products = Product::orderBy('name')->orderBy('sku')->get();
        return view('admin.bulk-outbound', compact('products'));
    }

    public function getBatchLocations(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'required|exists:batches,id',
        ]);

        $inventories = Inventory::where('batch_id', $request->batch_id)
            ->with('warehouseLocation')
            ->where('quantity', '>', 0)
            ->get();

        $locations = [];
        foreach ($inventories as $inv) {
            $depthPositions = $inv->depth_positions ?: [];
            $locations[] = [
                'location_code' => $inv->warehouseLocation->location_code,
                'quantity' => $inv->quantity,
                'max_pick' => $inv->quantity,
                'depth_positions' => $depthPositions,
                'next_depth' => !empty($depthPositions) ? max($depthPositions) : 0,
            ];
        }

        return response()->json($locations);
    }

    public function processBulkOutbound(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'location_code' => 'required|exists:warehouse_locations,location_code',
            'quantity' => 'required|integer|min:1',
        ]);

        $location = WarehouseLocation::where('location_code', $request->location_code)->first();
        $inventory = Inventory::where('batch_id', $request->batch_id)
            ->where('warehouse_location_id', $location->id)
            ->first();

        if (!$inventory || $inventory->quantity < $request->quantity) {
            $message = 'Not enough items at this location.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);
        }

        DB::beginTransaction();
        try {
            // Get current depth positions and sort for LIFO (highest first)
            $depthPositions = $inventory->depth_positions ?: [];

            // Log before state
            \Log::info('Before pickup', [
                'location' => $request->location_code,
                'quantity_before' => $inventory->quantity,
                'depths_before' => $depthPositions
            ]);

            // Sort in descending order (highest depth first) for LIFO picking
            rsort($depthPositions);

            // LIFO PICKING: Remove from highest depth first
            $pickedDepths = [];
            $remainingDepths = $depthPositions;

            for ($i = 0; $i < $request->quantity; $i++) {
                if (!empty($remainingDepths)) {
                    $pickedDepths[] = array_shift($remainingDepths); // Remove highest depth
                }
            }

            // Log what was picked
            \Log::info('Picked depths', ['picked' => $pickedDepths]);

            // Update inventory
            $newQuantity = $inventory->quantity - $request->quantity;

            if ($newQuantity == 0) {
                $inventory->delete();
                \Log::info('Inventory deleted - location now empty');
            } else {
                // Keep remaining depths (already in descending order)
                $inventory->quantity = $newQuantity;
                $inventory->depth_positions = array_values($remainingDepths);
                $inventory->save();
                \Log::info('After pickup', [
                    'quantity_after' => $newQuantity,
                    'depths_after_raw' => $remainingDepths
                ]);
            }

            // Update location fill level
            $location->current_fill -= $request->quantity;
            $location->save();

            // CRITICAL: Auto-shift remaining items to fill gaps from highest depth
            if ($newQuantity > 0) {
                $this->reindexLocationDepths($location);

                // Log after reindex
                $updatedInventory = Inventory::where('warehouse_location_id', $location->id)->first();
                if ($updatedInventory) {
                    \Log::info('After reindex', [
                        'depths_after_reindex' => $updatedInventory->depth_positions
                    ]);
                }
            }

            DB::commit();

            $message = "Successfully picked {$request->quantity} items from {$request->location_code}";
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('admin.bulk.outbound')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Bulk outbound error: ' . $e->getMessage());
            $message = 'Failed to pick items: ' . $e->getMessage();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);
        }
    }

    // Make sure reindexLocationDepths is working correctly
    private function reindexLocationDepths($location)
    {
        // Get all inventory at this location
        $inventories = Inventory::where('warehouse_location_id', $location->id)
            ->orderBy('id')
            ->get();

        if ($inventories->isEmpty()) {
            return;
        }

        // Collect all remaining items
        $allItems = [];
        foreach ($inventories as $inv) {
            $batchId = $inv->batch_id;
            $quantity = $inv->quantity;
            for ($i = 0; $i < $quantity; $i++) {
                $allItems[] = $batchId;
            }
        }

        // Reassign depths from highest (max_depth) down to 1
        $currentDepth = $location->max_depth;
        $newInventoryMap = [];

        foreach ($allItems as $batchId) {
            if (!isset($newInventoryMap[$batchId])) {
                $newInventoryMap[$batchId] = [];
            }
            if ($currentDepth >= 1) {
                $newInventoryMap[$batchId][] = $currentDepth;
                $currentDepth--;
            }
        }

        // Update inventory records
        foreach ($inventories as $inv) {
            if (isset($newInventoryMap[$inv->batch_id])) {
                $newDepths = $newInventoryMap[$inv->batch_id];
                rsort($newDepths); // Keep highest first for LIFO
                $inv->depth_positions = $newDepths;
                $inv->quantity = count($newDepths);
                $inv->save();
            }
        }
    }

    public function reservations()
    {
        $products = Product::orderBy('name')->orderBy('sku')->get();
        $batches = Batch::with('product')->orderBy('created_at', 'desc')->get();
        $locations = WarehouseLocation::orderBy('level')->orderBy('height')->get();
        $reservations = Reservation::with(['warehouseLocation', 'product', 'batch.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get reserved locations for display
        $reservedLocations = [];
        foreach ($reservations as $res) {
            $reservedLocations[$res->warehouse_location_id] = true;
        }

        return view('admin.reservations', compact('products', 'batches', 'locations', 'reservations', 'reservedLocations'));
    }

    public function storeReservation(Request $request)
    {
        // Debug log to see what's coming
        \Log::info('Reservation request data:', $request->all());

        $rules = [
            'location_code' => 'required|exists:warehouse_locations,location_code',
            'reservation_type' => 'required|in:product_batch,product_only',
        ];

        // Different validation rules based on reservation type
        if ($request->reservation_type === 'product_batch') {
            $rules['product_id'] = 'required|exists:products,id';
            $rules['batch_id'] = 'required|exists:batches,id';
        } else {
            $rules['product_id'] = 'required|exists:products,id';
            // batch_id is NOT required for product_only
        }

        $request->validate($rules);

        $location = WarehouseLocation::where('location_code', $request->location_code)->first();

        if (!$location) {
            return back()->with('error', 'Location not found.');
        }

        // Check if location already has inventory
        if ($location->current_fill > 0) {
            return back()->with('error', 'Cannot reserve a location that already has products stored.');
        }

        // Check if location already reserved
        $existingReservation = Reservation::where('warehouse_location_id', $location->id)->first();
        if ($existingReservation) {
            return back()->with('error', 'This location is already reserved.');
        }

        // Create reservation
        $reservationData = [
            'warehouse_location_id' => $location->id,
            'product_id' => $request->product_id,
            'reservation_type' => $request->reservation_type,
        ];

        // Only add batch_id if it's product_batch type
        if ($request->reservation_type === 'product_batch') {
            $reservationData['batch_id'] = $request->batch_id;
        } else {
            $reservationData['batch_id'] = null;
        }

        Reservation::create($reservationData);

        return redirect()->route('admin.reservations')
            ->with('success', 'Reservation created successfully.');
    }

    public function updateReservation(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        \Log::info('Update reservation request:', $request->all());

        $rules = [
            'location_code' => 'required|exists:warehouse_locations,location_code',
            'reservation_type' => 'required|in:product_batch,product_only',
        ];

        if ($request->reservation_type === 'product_batch') {
            $rules['product_id'] = 'required|exists:products,id';
            $rules['batch_id'] = 'required|exists:batches,id';
        } else {
            $rules['product_id'] = 'required|exists:products,id';
        }

        $request->validate($rules);

        $location = WarehouseLocation::where('location_code', $request->location_code)->first();

        if (!$location) {
            return back()->with('error', 'Location not found.');
        }

        // Check if new location already has inventory
        if ($location->current_fill > 0 && $location->id != $reservation->warehouse_location_id) {
            return back()->with('error', 'Cannot reserve a location that already has products stored.');
        }

        // Check if new location already reserved by another reservation
        $existingReservation = Reservation::where('warehouse_location_id', $location->id)
            ->where('id', '!=', $id)
            ->first();
        if ($existingReservation) {
            return back()->with('error', 'This location is already reserved by another reservation.');
        }

        $updateData = [
            'warehouse_location_id' => $location->id,
            'product_id' => $request->product_id,
            'reservation_type' => $request->reservation_type,
        ];

        if ($request->reservation_type === 'product_batch') {
            $updateData['batch_id'] = $request->batch_id;
        } else {
            $updateData['batch_id'] = null;
        }

        $reservation->update($updateData);

        return redirect()->route('admin.reservations')
            ->with('success', 'Reservation updated successfully.');
    }

    public function deleteReservation($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return redirect()->route('admin.reservations')
            ->with('success', 'Reservation deleted successfully.');
    }
}
