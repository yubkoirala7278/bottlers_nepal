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
            return back()->with('error', 'Not enough space at this location.');
        }
        
        // Check for mixed batches
        $existingInventory = Inventory::where('warehouse_location_id', $location->id)
            ->where('batch_id', '!=', $request->batch_id)
            ->first();
        
        if ($existingInventory) {
            return back()->with('error', 'Cannot mix different batches in the same location.');
        }
        
        DB::beginTransaction();
        try {
            $inventory = Inventory::firstOrNew([
                'batch_id' => $request->batch_id,
                'warehouse_location_id' => $location->id,
            ]);
            
            $depthPositions = $inventory->depth_positions ?: [];
            $currentFill = $location->current_fill;
            
            for ($i = 0; $i < $request->quantity; $i++) {
                $newDepth = $location->max_depth - $currentFill - $i;
                if ($newDepth >= 1) {
                    $depthPositions[] = $newDepth;
                }
            }
            
            $inventory->quantity = ($inventory->quantity ?: 0) + $request->quantity;
            $inventory->depth_positions = $depthPositions;
            $inventory->save();
            
            $location->current_fill += $request->quantity;
            $location->save();
            
            DB::commit();
            
            return redirect()->route('admin.bulk.inbound')
                ->with('success', "Successfully added {$request->quantity} items to {$request->location_code}");
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to add items: ' . $e->getMessage());
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
            ->get();
        
        $locations = [];
        foreach ($inventories as $inv) {
            $locations[] = [
                'location_code' => $inv->warehouseLocation->location_code,
                'quantity' => $inv->quantity,
                'max_pick' => $inv->quantity,
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
        $locations = WarehouseLocation::orderBy('level')->orderBy('height')->get();
        $reservations = Reservation::with(['warehouseLocation', 'product', 'batch'])->get();
        
        return view('admin.reservations', compact('products', 'locations', 'reservations'));
    }
    
    public function storeReservation(Request $request)
    {
        $request->validate([
            'location_code' => 'required|exists:warehouse_locations,location_code',
            'reservation_type' => 'required|in:product_batch,product_only',
            'product_id' => 'required_if:reservation_type,product_only,product_batch|exists:products,id',
            'batch_id' => 'required_if:reservation_type,product_batch|exists:batches,id',
        ]);
        
        $location = WarehouseLocation::where('location_code', $request->location_code)->first();
        
        // Check if location already reserved
        $existingReservation = Reservation::where('warehouse_location_id', $location->id)->first();
        if ($existingReservation) {
            return back()->with('error', 'This location is already reserved.');
        }
        
        Reservation::create([
            'warehouse_location_id' => $location->id,
            'product_id' => $request->product_id,
            'batch_id' => $request->batch_id,
            'reservation_type' => $request->reservation_type,
        ]);
        
        return redirect()->route('admin.reservations')
            ->with('success', 'Reservation created successfully.');
    }
    
    public function deleteReservation($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();
        
        return redirect()->route('admin.reservations')
            ->with('success', 'Reservation deleted successfully.');
    }
}