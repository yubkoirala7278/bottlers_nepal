{{-- resources/views/batches/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create New Batch')

@section('content')
<div class="form-container">
    <h1 style="margin-bottom: 1.5rem;">Create New Batch</h1>
    
    <form method="POST" action="{{ route('batches.store') }}">
        @csrf
        
        <div class="form-group">
            <label for="product_id">Select Product *</label>
            <select id="product_id" name="product_id" required>
                <option value="">-- Select Product --</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->full_name }}
                    </option>
                @endforeach
            </select>
            @error('product_id')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="batch_number">Batch Number *</label>
            <input type="text" id="batch_number" name="batch_number" value="{{ old('batch_number') }}" required 
                   placeholder="e.g., BATCH-2024-001">
            @error('batch_number')
                <div class="error">{{ $message }}</div>
            @enderror
            <small style="color: #666; display: block; margin-top: 0.25rem;">
                Unique batch identifier. Must be unique in the system.
            </small>
        </div>
        
        <div class="form-group">
            <label for="production_date">Production Date *</label>
            <input type="date" id="production_date" name="production_date" value="{{ old('production_date') }}" required>
            @error('production_date')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label>Expiry Date (Auto-calculated)</label>
            <div id="expiry_preview" style="padding: 0.75rem; background: #f8f9fa; border-radius: 8px; color: #666;">
                Select a product and production date to see expiry date
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Batch</button>
        </div>
        
        <div class="form-group">
            <a href="{{ route('batches.index') }}" class="btn btn-secondary" style="width: 100%; text-align: center;">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const productSelect = document.getElementById('product_id');
    const productionDate = document.getElementById('production_date');
    const expiryPreview = document.getElementById('expiry_preview');
    
    function calculateExpiry() {
        const productId = productSelect.value;
        const prodDate = productionDate.value;
        
        if (!productId || !prodDate) {
            expiryPreview.innerHTML = 'Select a product and production date to see expiry date';
            return;
        }
        
        // Get product SKU from selected option
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const productText = selectedOption.text;
        
        // Extract SKU (format: "Product Name - SKU")
        const sku = productText.split(' - ')[1];
        
        // Parse volume from SKU
        const volumeMatch = sku.match(/(\d+)/);
        const volume = volumeMatch ? parseInt(volumeMatch[1]) : 0;
        
        let days = 90; // default
        
        if ([2250, 1500].includes(volume)) {
            days = 90;
        } else if (volume === 1000) {
            days = 75;
        } else if ([250, 175].includes(volume)) {
            days = 180;
        }
        
        const prodDateObj = new Date(prodDate);
        const expiryDate = new Date(prodDateObj);
        expiryDate.setDate(prodDateObj.getDate() + days);
        
        expiryPreview.innerHTML = `
            <strong>Expiry Date:</strong> ${expiryDate.toISOString().split('T')[0]}<br>
            <strong>Days from production:</strong> ${days} days<br>
            <strong>Formula:</strong> ${volume >= 2250 || volume === 1500 ? '2250ml, 1500ml' : (volume === 1000 ? '1000ml' : (volume === 250 || volume === 175 ? '250ml, 175ml' : 'Default'))} => ${days} days
        `;
    }
    
    productSelect.addEventListener('change', calculateExpiry);
    productionDate.addEventListener('change', calculateExpiry);
</script>
@endpush
@endsection