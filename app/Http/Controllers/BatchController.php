<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Batch;
use Carbon\Carbon;

class BatchController extends Controller
{
    public function index()
    {
        $batches = Batch::with('product')->orderBy('created_at', 'desc')->paginate(15);
        return view('batches.index', compact('batches'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->orderBy('sku')->get();
        return view('batches.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'required|string|unique:batches,batch_number',
            'production_date' => 'required|date|before_or_equal:today',
        ]);

        $product = Product::find($request->product_id);
        $expiryDate = Batch::calculateExpiryDate($product->sku, $request->production_date);

        $batch = Batch::create([
            'product_id' => $request->product_id,
            'batch_number' => $request->batch_number,
            'production_date' => $request->production_date,
            'expiry_date' => $expiryDate,
        ]);

        return redirect()->route('batches.index')
            ->with('success', 'Batch created successfully. Expiry date: ' . $expiryDate->format('Y-m-d'));
    }

    public function destroy(Batch $batch)
    {
        // Check if batch has inventory
        if ($batch->inventory()->exists()) {
            return back()->with('error', 'Cannot delete batch with existing inventory.');
        }

        $batch->delete();
        return redirect()->route('batches.index')
            ->with('success', 'Batch deleted successfully.');
    }
}
