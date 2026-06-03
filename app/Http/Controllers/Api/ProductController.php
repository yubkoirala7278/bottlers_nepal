<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Return all products as JSON
     */
    public function index(Request $request)
    {
        $products = Product::orderBy('name')->get();

        return response()->json([
            'data' => $products,
        ], 200);
    }
}
