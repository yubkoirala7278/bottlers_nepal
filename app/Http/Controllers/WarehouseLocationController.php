<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WarehouseLocation;
use App\Models\Inventory;
use App\Models\Batch;
use App\Models\Reservation;

class WarehouseLocationController extends Controller
{
    public function index()
    {
        $locations = WarehouseLocation::orderBy('level')->orderBy('height')->get();
        return view('warehouse.index', compact('locations'));
    }
    public function matrixFull()
    {
        return view('warehouse.matrix-full');
    }

    public function matrix()
    {
        $levels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        $heights = [6, 5, 4, 3, 2, 1];

        $matrix = [];
        foreach ($levels as $level) {
            foreach ($heights as $height) {
                $locationCode = $level . $height;
                $location = WarehouseLocation::with(['inventory.batch.product', 'reservation'])
                    ->where('location_code', $locationCode)
                    ->first();

                $matrix[$level][$height] = $location;
            }
        }

        return view('warehouse.matrix', compact('matrix', 'levels', 'heights'));
    }

    public function getMatrixData()
    {
        try {
            $levels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
            $heights = [6, 5, 4, 3, 2, 1];

            $data = [];

            foreach ($levels as $level) {
                foreach ($heights as $height) {
                    $locationCode = $level . $height;
                    $location = WarehouseLocation::with(['inventory.batch.product', 'reservation.batch.product', 'reservation.product'])
                        ->where('location_code', $locationCode)
                        ->first();

                    if ($location) {
                        if ($location->inventory->isNotEmpty()) {
                            $inv = $location->inventory->first();
                            $data[$locationCode] = [
                                'product_name' => $inv->batch->product->name,
                                'sku' => $inv->batch->product->sku,
                                'batch_number' => $inv->batch->batch_number,
                                'quantity' => $inv->quantity,
                                'max_depth' => $location->max_depth,
                                'color_code' => $inv->batch->product->color_code,
                                'fill_percentage' => ($inv->quantity / $location->max_depth) * 100,
                            ];
                        } elseif ($location->reservation) {
                            $reservation = $location->reservation;
                            $reservedText = '';
                            if ($reservation->batch) {
                                $reservedText = $reservation->batch->product->name . ' ' .
                                    $reservation->batch->product->sku . ' (Batch: ' .
                                    $reservation->batch->batch_number . ')';
                            } elseif ($reservation->product) {
                                $reservedText = $reservation->product->name . ' ' . $reservation->product->sku;
                            }

                            $data[$locationCode] = [
                                'is_reserved' => true,
                                'reserved_for' => $reservedText,
                                'max_depth' => $location->max_depth,
                                'quantity' => 0,
                            ];
                        } else {
                            $data[$locationCode] = [
                                'quantity' => 0,
                                'max_depth' => $location->max_depth,
                                'is_empty' => true,
                            ];
                        }
                    }
                }
            }

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Matrix data error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
