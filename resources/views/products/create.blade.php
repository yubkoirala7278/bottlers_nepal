{{-- resources/views/products/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')
<div class="form-container">
    <h1 style="margin-bottom: 1.5rem;">Add New Product</h1>
    
    <form method="POST" action="{{ route('products.store') }}">
        @csrf
        
        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                   placeholder="e.g., Coke, Fanta, Sprite">
            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="sku">SKU *</label>
            <input type="text" id="sku" name="sku" value="{{ old('sku') }}" required 
                   placeholder="e.g., 100ml, 250ml, 500ml, 1000ml">
            @error('sku')
                <div class="error">{{ $message }}</div>
            @enderror
            <small style="color: #666; display: block; margin-top: 0.25rem;">
                Format: Volume in ml (100ml, 175ml, 250ml, 500ml, 1000ml, 1500ml, 2250ml)
            </small>
        </div>
        
        <div class="form-group">
            <label for="color_code">Color Code (Hex)</label>
            <input type="color" id="color_code" name="color_code" value="{{ old('color_code', '#C8102E') }}">
            <input type="text" name="color_code_text" value="{{ old('color_code', '#C8102E') }}" 
                   pattern="^#[a-fA-F0-9]{6}$" placeholder="#C8102E"
                   style="margin-top: 0.5rem;">
            @error('color_code')
                <div class="error">{{ $message }}</div>
            @enderror
            <small style="color: #666; display: block; margin-top: 0.25rem;">
                Coke: #C8102E | Fanta: #FF8300 | Sprite: #009639
            </small>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Product</button>
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