{{-- resources/views/products/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="form-container">
    <h1 style="margin-bottom: 1.5rem;">Edit Product</h1>
    
    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required>
            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="sku">SKU *</label>
            <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
            @error('sku')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="color_code">Color Code (Hex)</label>
            <input type="color" id="color_code" name="color_code" value="{{ old('color_code', $product->color_code) }}">
            <input type="text" name="color_code_text" value="{{ old('color_code', $product->color_code) }}" 
                   pattern="^#[a-fA-F0-9]{6}$" placeholder="#C8102E"
                   style="margin-top: 0.5rem;">
            @error('color_code')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Update Product</button>
        </div>
        
        <div class="form-group">
            <a href="{{ route('products.index') }}" class="btn btn-secondary" style="width: 100%; text-align: center;">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const colorPicker = document.getElementById('color_code');
    const colorText = document.querySelector('input[name="color_code_text"]');
    
    colorPicker.addEventListener('change', function() {
        colorText.value = this.value;
    });
    
    colorText.addEventListener('input', function() {
        if (this.value.match(/^#[a-fA-F0-9]{6}$/)) {
            colorPicker.value = this.value;
        }
    });
</script>
@endpush
@endsection