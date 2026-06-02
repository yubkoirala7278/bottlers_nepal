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
            return back()->with('error', 'Not enough items at this location.');
        }

        DB::beginTransaction();
        try {
            $depthPositions = $inventory->depth_positions ?: [];
            sort($depthPositions, SORT_DESC);

            $pickedCount = 0;
            while ($pickedCount < $request->quantity && !empty($depthPositions)) {
                array_shift($depthPositions);
                $pickedCount++;
            }

            $newQuantity = $inventory->quantity - $request->quantity;

            if ($newQuantity == 0) {
                $inventory->delete();
            } else {
                $inventory->quantity = $newQuantity;
                $inventory->depth_positions = array_values($depthPositions);
                $inventory->save();
            }

            $location->current_fill -= $request->quantity;
            $location->save();

            DB::commit();

            return redirect()->route('admin.bulk.outbound')
                ->with('success', "Successfully picked {$request->quantity} items from {$request->location_code}");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to pick items: ' . $e->getMessage());
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
