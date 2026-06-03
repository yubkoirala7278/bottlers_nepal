<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Batch;
use App\Models\WarehouseLocation;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class OutboundController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->orderBy('sku')->get();
        return view('outbound.index', compact('products'));
    }

    public function getOldestBatch(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            // Find oldest batch (by production date) for this product that has inventory with quantity > 0
            $oldestBatch = Batch::where('product_id', $request->product_id)
                ->whereHas('inventory', function ($query) {
                    $query->where('quantity', '>', 0);  // Only batches with positive quantity
                })
                ->orderBy('production_date', 'asc')
                ->with(['inventory' => function ($query) {
                    $query->where('quantity', '>', 0);  // Only inventory with positive quantity
                    $query->with('warehouseLocation');
                }])
                ->first();

            if (!$oldestBatch) {
                return response()->json([
                    'found' => false,
                    'success' => false,
                    'message' => 'No inventory found for this product.'
                ]);
            }

            // Filter inventory to only those with quantity > 0
            $validInventory = $oldestBatch->inventory->filter(function ($inv) {
                return $inv->quantity > 0;
            });

            if ($validInventory->isEmpty()) {
                return response()->json([
                    'found' => false,
                    'success' => false,
                    'message' => 'No inventory found for this product.'
                ]);
            }

            // Get the best location (LIFO - highest depth first)
            $bestLocation = null;
            $bestDepth = 0;
            $bestInventory = null;

            foreach ($validInventory as $inventory) {
                $depthPositions = $inventory->depth_positions ?: [];
                if (!empty($depthPositions)) {
                    $maxDepth = max($depthPositions);
                    if ($maxDepth > $bestDepth) {
                        $bestDepth = $maxDepth;
                        $bestLocation = $inventory->warehouseLocation;
                        $bestInventory = $inventory;
                    }
                } elseif ($bestLocation === null) {
                    $bestLocation = $inventory->warehouseLocation;
                    $bestInventory = $inventory;
                }
            }

            if (!$bestLocation || !$bestInventory || $bestInventory->quantity <= 0) {
                return response()->json([
                    'found' => false,
                    'success' => false,
                    'message' => 'No valid location found for this batch.'
                ]);
            }

            return response()->json([
                'found' => true,
                'success' => true,
                'batch' => [
                    'id' => $oldestBatch->id,
                    'batch_number' => $oldestBatch->batch_number,
                    'production_date' => $oldestBatch->production_date->format('Y-m-d'),
                    'expiry_date' => $oldestBatch->expiry_date->format('Y-m-d'),
                ],
                'location' => [
                    'code' => $bestLocation->location_code,
                    'id' => $bestLocation->id,
                    'quantity' => $bestInventory->quantity,
                    'max_pick' => $bestInventory->quantity,
                    'next_depth' => $bestDepth,
                ],
                'total_quantity' => $validInventory->sum('quantity'),  // Sum only positive quantities
            ]);
        } catch (\Exception $e) {
            \Log::error('Get oldest batch error: ' . $e->getMessage());
            return response()->json([
                'found' => false,
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pickup(Request $request)
    {
        try {
            \Log::info('Pickup request received', $request->all());

            $request->validate([
                'batch_id' => 'required|exists:batches,id',
                'location_code' => 'required|string',
                'quantity' => 'required|integer|min:1',
            ]);

            // Find location by code (case insensitive)
            $location = WarehouseLocation::where('location_code', 'LIKE', $request->location_code)->first();

            if (!$location) {
                \Log::error('Location not found: ' . $request->location_code);
                return response()->json([
                    'success' => false,
                    'message' => "Location '{$request->location_code}' not found in system."
                ]);
            }

            \Log::info('Location found', ['id' => $location->id, 'code' => $location->location_code]);

            $inventory = Inventory::where('batch_id', $request->batch_id)
                ->where('warehouse_location_id', $location->id)
                ->first();

            if (!$inventory) {
                \Log::error('Inventory not found', [
                    'batch_id' => $request->batch_id,
                    'location_id' => $location->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No inventory found at this location.'
                ]);
            }

            \Log::info('Inventory found', [
                'quantity' => $inventory->quantity,
                'requested' => $request->quantity
            ]);

            if ($inventory->quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Not enough items. Available: {$inventory->quantity}, Requested: {$request->quantity}"
                ]);
            }

            DB::beginTransaction();

            try {
                // Get current depth positions and sort (LIFO - from highest to lowest)
                $depthPositions = $inventory->depth_positions ?: [];
                sort($depthPositions, SORT_DESC); // Highest first

                \Log::info('Depth positions', ['positions' => $depthPositions]);

                // Remove picked items from depth positions (from highest)
                $remainingPositions = $depthPositions;
                for ($i = 0; $i < $request->quantity; $i++) {
                    if (!empty($remainingPositions)) {
                        array_shift($remainingPositions);
                    }
                }

                // Update inventory
                $newQuantity = $inventory->quantity - $request->quantity;

                if ($newQuantity == 0) {
                    $inventory->delete();
                    \Log::info('Inventory deleted as quantity became 0');
                } else {
                    $inventory->quantity = $newQuantity;
                    $inventory->depth_positions = array_values($remainingPositions);
                    $inventory->save();
                    \Log::info('Inventory updated', ['new_quantity' => $newQuantity, 'remaining_positions' => $remainingPositions]);
                }

                // Update location fill level
                $location->current_fill -= $request->quantity;
                $location->save();
                \Log::info('Location updated', ['new_fill' => $location->current_fill]);

                // Re-index remaining items (shift to fill gaps)
                if ($newQuantity > 0) {
                    $this->reindexLocationDepths($location);
                    \Log::info('Location depths reindexed');
                }

                DB::commit();

                // Get updated batch info for next pickup
                $batch = Batch::find($request->batch_id);
                $remainingInventory = Inventory::where('batch_id', $request->batch_id)->sum('quantity');

                // Get next pickup info if available
                $nextPickup = null;
                if ($remainingInventory > 0) {
                    // Get the next inventory for this batch
                    $nextInventory = Inventory::where('batch_id', $request->batch_id)
                        ->where('quantity', '>', 0)
                        ->with('warehouseLocation')
                        ->first();

                    if ($nextInventory) {
                        $nextDepths = $nextInventory->depth_positions ?: [];
                        $nextDepth = !empty($nextDepths) ? max($nextDepths) : 0;
                        $nextPickup = [
                            'location_code' => $nextInventory->warehouseLocation->location_code,
                            'location_id' => $nextInventory->warehouseLocation->id,
                            'quantity' => $nextInventory->quantity,
                            'next_depth' => $nextDepth
                        ];
                    }
                }

                $responseData = [
                    'success' => true,
                    'message' => "Successfully picked {$request->quantity} items from {$request->location_code}",
                    'data' => [
                        'picked' => $request->quantity,
                        'location' => $request->location_code,
                        'remaining_in_batch' => $remainingInventory,
                        'remaining_in_location' => $newQuantity,
                        'batch_completed' => ($remainingInventory == 0)
                    ]
                ];

                if ($nextPickup) {
                    $responseData['data']['next_pickup'] = $nextPickup;
                }

                return response()->json($responseData);
            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Transaction error: ' . $e->getMessage());
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . json_encode($e->errors())
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Pickup error: ' . $e->getMessage());
            \Log::error('Pickup trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to pick items: ' . $e->getMessage()
            ], 500);
        }
    }

    private function reindexLocationDepths($location)
    {
        // Get all inventory at this location ordered by creation
        $inventories = Inventory::where('warehouse_location_id', $location->id)
            ->orderBy('id')
            ->get();

        if ($inventories->isEmpty()) {
            return;
        }

        // Collect all depth positions
        $allDepths = [];
        foreach ($inventories as $inv) {
            $depths = $inv->depth_positions ?: [];
            $allDepths = array_merge($allDepths, $depths);
        }

        // Sort in descending order
        rsort($allDepths);

        // Reassign depths sequentially from highest
        $currentDepth = $location->max_depth;
        foreach ($inventories as $inv) {
            $newDepths = [];
            $count = $inv->quantity;
            for ($i = 0; $i < $count; $i++) {
                if ($currentDepth >= 1) {
                    $newDepths[] = $currentDepth;
                    $currentDepth--;
                }
            }
            // Sort new depths in descending order
            rsort($newDepths);
            $inv->depth_positions = $newDepths;
            $inv->save();
        }
    }
}
