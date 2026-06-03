<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Batch;
use App\Models\WarehouseLocation;
use App\Models\Inventory;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InboundController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->orderBy('sku')->get();
        return view('inbound.index', compact('products'));
    }

    public function getLatestBatches(Request $request)
    {
        $productId = $request->product_id;
        $batches = Batch::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'production_date' => $batch->production_date->format('Y-m-d'),
                    'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                ];
            });

        return response()->json($batches);
    }

    public function findPlace(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'batch_id' => 'required|exists:batches,id',
            ]);

            $productId = $request->product_id;
            $batchId = $request->batch_id;

            $batch = Batch::find($batchId);

            // Rule a: Find existing location with same product+sku+batch
            $existingLocation = WarehouseLocation::whereHas('inventory', function ($query) use ($batchId) {
                $query->where('batch_id', $batchId);
            })->with(['inventory' => function ($query) use ($batchId) {
                $query->where('batch_id', $batchId);
            }])->first();

            if ($existingLocation && $existingLocation->available_space > 0) {
                return response()->json([
                    'found' => true,
                    'type' => 'existing_batch',
                    'location' => $existingLocation->location_code,
                    'available_space' => $existingLocation->available_space,
                    'max_allowed' => min($existingLocation->available_space, 50),
                    'message' => "Same batch found at {$existingLocation->location_code}. {$existingLocation->available_space} spaces available."
                ]);
            }

            // Rule b: Check reserved place for product+sku+batch
            $reservedForBatch = Reservation::where('batch_id', $batchId)
                ->where('reservation_type', 'product_batch')
                ->with('warehouseLocation')
                ->first();

            if ($reservedForBatch && $reservedForBatch->warehouseLocation) {
                $location = $reservedForBatch->warehouseLocation;
                if ($location->available_space > 0 && !$location->inventory()->exists()) {
                    return response()->json([
                        'found' => true,
                        'type' => 'reserved_batch',
                        'location' => $location->location_code,
                        'available_space' => $location->available_space,
                        'max_allowed' => min($location->available_space, 50),
                        'message' => "Reserved location {$location->location_code} found for this batch. {$location->available_space} spaces available."
                    ]);
                }
            }

            // Rule c: Check reserved place for product+sku only
            $reservedForProduct = Reservation::where('product_id', $productId)
                ->whereNull('batch_id')
                ->where('reservation_type', 'product_only')
                ->with('warehouseLocation')
                ->first();

            if ($reservedForProduct && $reservedForProduct->warehouseLocation) {
                $location = $reservedForProduct->warehouseLocation;
                if ($location->available_space > 0 && !$location->inventory()->exists()) {
                    return response()->json([
                        'found' => true,
                        'type' => 'reserved_product',
                        'location' => $location->location_code,
                        'available_space' => $location->available_space,
                        'max_allowed' => min($location->available_space, 50),
                        'message' => "Reserved location {$location->location_code} found for this product. {$location->available_space} spaces available."
                    ]);
                }
            }

            // Rule d: Find any empty space (depth first)
            $emptyLocation = WarehouseLocation::where('current_fill', 0)
                ->whereDoesntHave('inventory')
                ->whereDoesntHave('reservation')
                ->orderBy('level')
                ->orderBy('height', 'desc')
                ->first();

            if ($emptyLocation) {
                return response()->json([
                    'found' => true,
                    'type' => 'empty',
                    'location' => $emptyLocation->location_code,
                    'available_space' => $emptyLocation->max_depth,
                    'max_allowed' => $emptyLocation->max_depth,
                    'message' => "Empty location {$emptyLocation->location_code} found. {$emptyLocation->max_depth} spaces available."
                ]);
            }

            // Check partially filled locations that might have space for same batch
            $partialLocation = WarehouseLocation::where('current_fill', '<', DB::raw('max_depth'))
                ->whereHas('inventory', function ($query) use ($batchId) {
                    $query->where('batch_id', $batchId);
                })
                ->orderBy('level')
                ->orderBy('height', 'desc')
                ->first();

            if ($partialLocation && $partialLocation->available_space > 0) {
                return response()->json([
                    'found' => true,
                    'type' => 'partial',
                    'location' => $partialLocation->location_code,
                    'available_space' => $partialLocation->available_space,
                    'max_allowed' => min($partialLocation->available_space, 50),
                    'message' => "Partial space at {$partialLocation->location_code}. {$partialLocation->available_space} spaces available."
                ]);
            }

            return response()->json([
                'found' => false,
                'message' => 'No suitable location found. Warehouse might be full.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Find place error: ' . $e->getMessage());
            return response()->json([
                'found' => false,
                'message' => 'Error finding location: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'batch_id' => 'required|exists:batches,id',
                'location_code' => 'required|exists:warehouse_locations,location_code',
                'quantity' => 'required|integer|min:1|max:50',
                'ack_code' => 'required|string|size:6',
            ]);
            // Verify acknowledgment code format
            if (!preg_match('/^\d{6}$/', $request->ack_code)) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Invalid acknowledgment code format. Must be 6 digits.']);
                }
                return back()->with('error', 'Invalid acknowledgment code format.');
            }

            $location = WarehouseLocation::where('location_code', $request->location_code)->first();
            $batch = Batch::find($request->batch_id);

            if (!$location) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Location not found']);
                }
                return back()->with('error', 'Location not found.');
            }

            // Check available space
            if ($location->available_space < $request->quantity) {
                $message = "Not enough space at this location. Available: {$location->available_space}, Requested: {$request->quantity}";
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $message]);
                }
                return back()->with('error', $message);
            }

            // Check if mixing different batches
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

            // Check reservation
            $reservation = Reservation::where('warehouse_location_id', $location->id)->first();
            if ($reservation) {
                if ($reservation->batch_id && $reservation->batch_id != $request->batch_id) {
                    $message = 'This location is reserved for a different batch.';
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $message]);
                    }
                    return back()->with('error', $message);
                }
                if ($reservation->product_id && $reservation->product_id != $request->product_id) {
                    $message = 'This location is reserved for a different product.';
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $message]);
                    }
                    return back()->with('error', $message);
                }
            }

            DB::beginTransaction();

            // Get current depth positions
            $inventory = Inventory::firstOrNew([
                'batch_id' => $request->batch_id,
                'warehouse_location_id' => $location->id,
            ]);

            $depthPositions = $inventory->depth_positions ?: [];

            // Add new depth positions (depth first: from highest to lowest)
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

            // Update location fill level
            $location->current_fill += $request->quantity;
            $location->save();

            DB::commit();

            $response = [
                'success' => true,
                'message' => "Successfully placed {$request->quantity} items at {$request->location_code}",
                'data' => [
                    'location' => $request->location_code,
                    'quantity' => $request->quantity,
                    'new_fill' => $location->current_fill,
                    'available_space' => $location->available_space
                ]
            ];

            if ($request->ajax()) {
                return response()->json($response);
            }

            return redirect()->route('inbound.index')->with('success', $response['message']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Inbound store error: ' . $e->getMessage());

            $message = 'Failed to place items: ' . $e->getMessage();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return back()->with('error', $message);
        }
    }
}
