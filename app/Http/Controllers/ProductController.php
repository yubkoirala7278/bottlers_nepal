<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->orderBy('sku')->paginate(15);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'color_code' => 'nullable|string|regex:/^#[a-f0-9]{6}$/i',
        ], [
            'name.required' => 'Product name is required',
            'sku.required' => 'SKU is required',
            'color_code.regex' => 'Color code must be a valid hex color (e.g., #C8102E)',
        ]);

        // Check for uniqueness
        $exists = Product::where('name', $request->name)
            ->where('sku', $request->sku)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'sku' => 'Product with this name and SKU already exists.'
            ])->withInput();
        }

        $product = Product::create([
            'name' => $request->name,
            'sku' => $request->sku,
            'color_code' => $request->color_code ?: '#000000',
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'color_code' => 'nullable|string|regex:/^#[a-f0-9]{6}$/i',
        ]);

        // Check uniqueness excluding current product
        $exists = Product::where('name', $request->name)
            ->where('sku', $request->sku)
            ->where('id', '!=', $product->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'sku' => 'Product with this name and SKU already exists.'
            ])->withInput();
        }

        $product->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'color_code' => $request->color_code ?: '#000000',
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Check if product has batches
        if ($product->batches()->exists()) {
            return back()->with('error', 'Cannot delete product with existing batches.');
        }

        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
