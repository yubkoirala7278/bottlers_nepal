{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Products Management')

@section('content')
<div class="products-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <h1>Products Management</h1>
    <a href="{{ route('products.create') }}" class="btn btn-primary">+ Add New Product</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Color</th>
                <th>Color Code</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->sku }}</td>
                <td>
                    <div style="width: 30px; height: 30px; background: {{ $product->color_code }}; border-radius: 5px; border: 1px solid #ddd;"></div>
                </td>
                <td>{{ $product->color_code }}</td>
                <td>{{ $product->created_at->format('Y-m-d H:i') }}</td>
                <td>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary" style="margin-right: 0.5rem;">Edit</a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product? This will also delete all associated batches and inventory.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No products found. Click "Add New Product" to create one.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination-container" style="margin-top: 2rem; text-align: center;">
    {{ $products->links() }}
</div>
@endsection