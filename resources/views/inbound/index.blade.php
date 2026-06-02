{{-- resources/views/inbound/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Inbound Management')

@section('content')
<style>
    .inbound-form {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .suggestion-card {
        background: #e8f5e9;
        border-left: 4px solid #4caf50;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
    }
    
    .form-group-mobile {
        margin-bottom: 1rem;
    }
    
    .form-group-mobile label {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        display: block;
    }
    
    .form-group-mobile input,
    .form-group-mobile select {
        width: 100%;
        padding: 0.75rem;
        font-size: 1rem;
        border: 1px solid #ddd;
        border-radius: 8px;
    }
    
    .btn-mobile {
        width: 100%;
        padding: 0.875rem;
        font-size: 1rem;
        margin-top: 0.5rem;
    }
    
    @media (max-width: 480px) {
        .inbound-form {
            padding: 1rem;
        }
        
        .form-group-mobile label {
            font-size: 0.8rem;
        }
    }
</style>

<div style="max-width: 600px; margin: 0 auto;">
    <h1 style="margin-bottom: 1.5rem;">📥 Inbound Management</h1>
    
    <div class="inbound-form">
        <form id="inboundForm">
            @csrf
            <div class="form-group-mobile">
                <label for="product_id">Select Product *</label>
                <select id="product_id" name="product_id" required>
                    <option value="">-- Select Product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->full_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group-mobile">
                <label for="batch_id">Select Batch *</label>
                <select id="batch_id" name="batch_id" required disabled>
                    <option value="">-- First select product --</option>
                </select>
            </div>
            
            <div id="suggestionArea"></div>
            
            <div id="placementForm" style="display: none;">
                <div class="form-group-mobile">
                    <label for="quantity">Number of Products to Place</label>
                    <input type="number" id="quantity" name="quantity" min="1" max="50" value="1">
                </div>
                
                <input type="hidden" id="selected_location" name="location_code">
                <input type="hidden" id="selected_batch" name="batch_id">
                
                <button type="submit" class="btn btn-primary btn-mobile">✅ Confirm Placement</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const productSelect = document.getElementById('product_id');
    const batchSelect = document.getElementById('batch_id');
    const suggestionArea = document.getElementById('suggestionArea');
    const placementForm = document.getElementById('placementForm');
    let currentSuggestion = null;
    
    productSelect.addEventListener('change', function() {
        if (this.value) {
            // Load batches for this product
            fetch('{{ route("inbound.latest-batches") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ product_id: this.value })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';
                if (data.length === 0) {
                    batchSelect.innerHTML += '<option value="">No batches available</option>';
                    batchSelect.disabled = true;
                } else {
                    data.forEach(batch => {
                        batchSelect.innerHTML += `<option value="${batch.id}">${batch.batch_number} (Prod: ${batch.production_date})</option>`;
                    });
                    batchSelect.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                batchSelect.innerHTML = '<option value="">Error loading batches</option>';
                batchSelect.disabled = true;
            });
        } else {
            batchSelect.disabled = true;
            batchSelect.innerHTML = '<option value="">-- First select product --</option>';
        }
        
        suggestionArea.innerHTML = '';
        placementForm.style.display = 'none';
    });
    
    batchSelect.addEventListener('change', function() {
        if (this.value && productSelect.value) {
            // Show loading state
            suggestionArea.innerHTML = `
                <div class="suggestion-card">
                    <strong>🔍 Finding best location...</strong>
                </div>
            `;
            
            // Find placement suggestion
            fetch('{{ route("inbound.find-place") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    product_id: productSelect.value,
                    batch_id: this.value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.found) {
                    currentSuggestion = data;
                    suggestionArea.innerHTML = `
                        <div class="suggestion-card">
                            <strong>🎯 Suggested Location:</strong><br>
                            <strong style="font-size: 1.25rem;">${data.location}</strong><br>
                            Available Space: ${data.available_space} units<br>
                            ${data.message}<br>
                            <small>Maximum allowed: ${data.max_allowed} units</small>
                        </div>
                    `;
                    
                    document.getElementById('selected_location').value = data.location;
                    document.getElementById('selected_batch').value = batchSelect.value;
                    const quantityInput = document.getElementById('quantity');
                    quantityInput.max = data.max_allowed;
                    quantityInput.value = Math.min(1, data.max_allowed);
                    placementForm.style.display = 'block';
                } else {
                    suggestionArea.innerHTML = `
                        <div class="suggestion-card" style="background: #ffebee; border-left-color: #f44336;">
                            <strong>❌ No Location Found</strong><br>
                            ${data.message}
                        </div>
                    `;
                    placementForm.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                suggestionArea.innerHTML = `
                    <div class="suggestion-card" style="background: #ffebee; border-left-color: #f44336;">
                        <strong>❌ Error</strong><br>
                        Failed to find location. Please try again.
                    </div>
                `;
                placementForm.style.display = 'none';
            });
        } else {
            suggestionArea.innerHTML = '';
            placementForm.style.display = 'none';
        }
    });
    
    document.getElementById('inboundForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const quantity = document.getElementById('quantity').value;
        const locationCode = document.getElementById('selected_location').value;
        const batchId = document.getElementById('selected_batch').value;
        const productId = productSelect.value;
        
        if (!quantity || quantity < 1) {
            alert('Please enter a valid quantity');
            return;
        }
        
        // Show loading state
        const submitBtn = document.querySelector('#inboundForm button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('batch_id', batchId);
        formData.append('location_code', locationCode);
        formData.append('quantity', quantity);
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('{{ route("inbound.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✅ Success: ' + data.message);
                // Reset form
                productSelect.value = '';
                batchSelect.disabled = true;
                batchSelect.innerHTML = '<option value="">-- First select product --</option>';
                suggestionArea.innerHTML = '';
                placementForm.style.display = 'none';
                // Reload to update matrix
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'Failed to process request';
            if (error.message) {
                errorMessage = error.message;
            } else if (typeof error === 'object' && error.errors) {
                errorMessage = Object.values(error.errors).flat().join(', ');
            }
            alert('❌ Error: ' + errorMessage);
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
</script>
@endpush
@endsection