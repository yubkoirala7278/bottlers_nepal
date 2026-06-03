<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    /**
     * Store a new batch (API)
     * Expects: product_id, batch_number, production_date
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'required|string|unique:batches,batch_number',
            'production_date' => 'required|date|before_or_equal:today',
        ]);

        $product = Product::find($data['product_id']);

        $expiryDate = Batch::calculateExpiryDate($product->sku, $data['production_date']);

        $batch = null;

        DB::beginTransaction();
        try {
            $batch = Batch::create([
                'product_id' => $data['product_id'],
                'batch_number' => $data['batch_number'],
                'production_date' => $data['production_date'],
                'expiry_date' => $expiryDate,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create batch',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Batch created successfully',
            'data' => $batch,
        ], 201);
    }
}
