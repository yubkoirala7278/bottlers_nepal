{{-- resources/views/outbound/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Outbound Management')

@section('content')
<style>
    .outbound-container {
        max-width: 600px;
        margin: 0 auto;
    }
    
    .outbound-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #333;
        font-size: 0.9rem;
    }
    
    .form-group select,
    .form-group input {
        width: 100%;
        padding: 0.875rem;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s;
        background: white;
    }
    
    .form-group select:focus,
    .form-group input:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .batch-details {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        border-radius: 12px;
        margin: 1rem 0;
    }
    
    .location-card {
        background: #e8f5e9;
        border: 2px solid #4caf50;
        border-radius: 12px;
        padding: 1rem;
        margin: 1rem 0;
        text-align: center;
    }
    
    .location-code {
        font-size: 1.5rem;
        font-weight: bold;
        color: #2e7d32;
    }
    
    .pick-form {
        background: #f5f5f5;
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1rem;
    }
    
    .btn-pick {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        width: 100%;
        padding: 0.875rem;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .btn-pick:hover:not(:disabled) {
        transform: translateY(-2px);
    }
    
    .btn-pick:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 1rem 0;
    }
    
    .quantity-control button {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        border: none;
        background: white;
        font-size: 1.5rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .quantity-control button:active {
        transform: scale(0.95);
    }
    
    .quantity-control button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    
    .quantity-control input {
        flex: 1;
        text-align: center;
        font-size: 1.25rem;
        font-weight: bold;
        padding: 0.75rem;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        background: white;
    }
    
    .quantity-control input:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .alert-success-custom {
        background: #d4edda;
        color: #155724;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1rem;
        border-left: 4px solid #28a745;
        animation: slideIn 0.3s ease-out;
    }
    
    .alert-error-custom {
        background: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1rem;
        border-left: 4px solid #dc3545;
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .loading {
        text-align: center;
        padding: 1rem;
    }
    
    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #667eea;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .validation-error {
        color: #dc3545;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: block;
    }
    
    @media (max-width: 480px) {
        .outbound-card {
            padding: 1rem;
        }
        
        .location-code {
            font-size: 1.25rem;
        }
        
        .quantity-control button {
            width: 44px;
            height: 44px;
            font-size: 1.25rem;
        }
    }
</style>

<div class="outbound-container">
    <h1 style="margin-bottom: 1.5rem;">📤 Outbound Management</h1>
    
    <div id="messageArea"></div>
    
    <div class="outbound-card">
        <form id="outboundForm">
            @csrf
            <div class="form-group">
                <label for="product_id">Select Product to Pick</label>
                <select id="product_id" name="product_id" required>
                    <option value="">-- Select Product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        
        <div id="pickupArea" style="display: none;">
            <div class="batch-details" id="batchDetails"></div>
            
            <div class="location-card">
                <div style="font-size: 0.85rem; color: #666; margin-bottom: 0.5rem;">Suggested Pickup Location (LIFO)</div>
                <div class="location-code" id="locationCode">-</div>
                <div style="font-size: 0.85rem; margin-top: 0.5rem;" id="availableInfo">-</div>
            </div>
            
            <div class="pick-form">
                <label style="font-weight: 600;">Number of items to pick</label>
                <div class="quantity-control">
                    <button type="button" id="decrementQty" style="background: #ff5722; color: white;">−</button>
                    <input type="number" id="pickupQuantity" min="1" value="1" step="1">
                    <button type="button" id="incrementQty" style="background: #4caf50; color: white;">+</button>
                </div>
                <div id="quantityError" class="validation-error" style="display: none;"></div>
                
                <button type="button" id="confirmPickupBtn" class="btn-pick">
                    ✅ Confirm Pickup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentBatch = null;
    let currentLocation = null;
    let maxQuantity = 0;
    
    const productSelect = document.getElementById('product_id');
    const pickupArea = document.getElementById('pickupArea');
    const batchDetails = document.getElementById('batchDetails');
    const locationCode = document.getElementById('locationCode');
    const availableInfo = document.getElementById('availableInfo');
    const pickupQuantity = document.getElementById('pickupQuantity');
    const decrementBtn = document.getElementById('decrementQty');
    const incrementBtn = document.getElementById('incrementQty');
    const confirmBtn = document.getElementById('confirmPickupBtn');
    const messageArea = document.getElementById('messageArea');
    const quantityError = document.getElementById('quantityError');
    
    function showMessage(message, type = 'success') {
        const messageDiv = document.createElement('div');
        messageDiv.className = type === 'success' ? 'alert-success-custom' : 'alert-error-custom';
        messageDiv.innerHTML = type === 'success' ? '✅ ' + message : '❌ ' + message;
        messageArea.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }
    
    function validateQuantity(value) {
        let num = parseInt(value);
        
        // Check if it's a valid number
        if (isNaN(num)) {
            quantityError.textContent = 'Please enter a valid number';
            quantityError.style.display = 'block';
            return false;
        }
        
        // Check min
        if (num < 1) {
            quantityError.textContent = `Minimum quantity is 1`;
            quantityError.style.display = 'block';
            return false;
        }
        
        // Check max
        if (num > maxQuantity) {
            quantityError.textContent = `Maximum quantity is ${maxQuantity}`;
            quantityError.style.display = 'block';
            return false;
        }
        
        // Check if it's an integer
        if (!Number.isInteger(num)) {
            quantityError.textContent = 'Please enter a whole number';
            quantityError.style.display = 'block';
            return false;
        }
        
        quantityError.style.display = 'none';
        return true;
    }
    
    function updateQuantityButtons() {
        let currentValue = parseInt(pickupQuantity.value);
        
        if (isNaN(currentValue)) {
            decrementBtn.disabled = true;
            incrementBtn.disabled = true;
            return;
        }
        
        // Update decrement button
        if (currentValue <= 1) {
            decrementBtn.disabled = true;
        } else {
            decrementBtn.disabled = false;
        }
        
        // Update increment button
        if (currentValue >= maxQuantity) {
            incrementBtn.disabled = true;
        } else {
            incrementBtn.disabled = false;
        }
    }
    
    function handleQuantityChange() {
        let value = pickupQuantity.value;
        let num = parseInt(value);
        
        if (validateQuantity(num)) {
            pickupQuantity.value = num;
        } else {
            // Reset to last valid value or 1
            let lastValid = Math.min(Math.max(1, num), maxQuantity);
            if (!isNaN(lastValid) && lastValid >= 1 && lastValid <= maxQuantity) {
                pickupQuantity.value = lastValid;
                validateQuantity(lastValid);
            } else {
                pickupQuantity.value = 1;
                validateQuantity(1);
            }
        }
        
        updateQuantityButtons();
    }
    
    // Manual input event
    pickupQuantity.addEventListener('input', function() {
        let value = this.value;
        let num = parseInt(value);
        
        if (!isNaN(num)) {
            if (num < 1) {
                this.value = 1;
                validateQuantity(1);
            } else if (num > maxQuantity) {
                this.value = maxQuantity;
                validateQuantity(maxQuantity);
            } else {
                validateQuantity(num);
            }
        } else if (value === '') {
            // Allow empty temporarily
            quantityError.style.display = 'none';
        } else {
            validateQuantity(num);
        }
        
        updateQuantityButtons();
    });
    
    pickupQuantity.addEventListener('blur', function() {
        handleQuantityChange();
    });
    
    decrementBtn.addEventListener('click', () => {
        let currentValue = parseInt(pickupQuantity.value);
        if (!isNaN(currentValue) && currentValue > 1) {
            let newValue = currentValue - 1;
            pickupQuantity.value = newValue;
            validateQuantity(newValue);
            updateQuantityButtons();
        }
    });
    
    incrementBtn.addEventListener('click', () => {
        let currentValue = parseInt(pickupQuantity.value);
        if (!isNaN(currentValue) && currentValue < maxQuantity) {
            let newValue = currentValue + 1;
            pickupQuantity.value = newValue;
            validateQuantity(newValue);
            updateQuantityButtons();
        }
    });
    
productSelect.addEventListener('change', function() {
    if (!this.value) {
        pickupArea.style.display = 'none';
        return;
    }
    
    // Show loading
    pickupArea.style.display = 'block';
    batchDetails.innerHTML = '<div class="loading"><div class="spinner"></div><p>Finding oldest batch...</p></div>';
    locationCode.textContent = 'Searching...';
    pickupArea.style.opacity = '0.5';
    confirmBtn.disabled = true;
    
    fetch('{{ route("outbound.oldest-batch") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ product_id: this.value })
    })
    .then(response => response.json())
    .then(data => {
        pickupArea.style.opacity = '1';
        
        if (data.found && data.success) {
            currentBatch = data.batch;
            currentLocation = {
                code: data.location.code,
                id: data.location.id,
                quantity: data.location.quantity
            };
            maxQuantity = data.location.max_pick;
            
            batchDetails.innerHTML = `
                <div>
                    <strong>📦 Batch Number:</strong> ${data.batch.batch_number}<br>
                    <strong>📅 Production Date:</strong> ${data.batch.production_date}<br>
                    <strong>⏰ Expiry Date:</strong> ${data.batch.expiry_date}<br>
                    <strong>📊 Total Available:</strong> ${data.total_quantity} units<br>
                    <strong>📍 Next pickup depth:</strong> ${data.location.next_depth}
                </div>
            `;
            
            locationCode.textContent = data.location.code;
            availableInfo.innerHTML = `Available: ${data.location.quantity} units | Max pickup: ${maxQuantity}`;
            
            pickupQuantity.value = Math.min(1, maxQuantity);
            pickupQuantity.max = maxQuantity;
            pickupQuantity.min = 1;
            
            validateQuantity(parseInt(pickupQuantity.value));
            updateQuantityButtons();
            confirmBtn.disabled = false;
            
            pickupArea.style.display = 'block';
        } else {
            batchDetails.innerHTML = `
                <div style="color: #fff;">
                    <strong>❌ ${data.message || 'No inventory found'}</strong>
                </div>
            `;
            locationCode.textContent = 'N/A';
            availableInfo.textContent = 'No stock available';
            confirmBtn.disabled = true;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        pickupArea.style.opacity = '1';
        batchDetails.innerHTML = `
            <div style="color: #fff;">
                <strong>❌ Error loading batch information</strong>
            </div>
        `;
        showMessage('Failed to load batch information', 'error');
        confirmBtn.disabled = true;
    });
});
    
confirmBtn.addEventListener('click', function() {
    if (!currentBatch || !currentLocation) {
        showMessage('No batch selected', 'error');
        return;
    }
    
    const quantity = parseInt(pickupQuantity.value);
    
    if (!validateQuantity(quantity)) {
        showMessage(`Please enter a valid quantity between 1 and ${maxQuantity}`, 'error');
        return;
    }
    
    // Disable button during processing
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';
    
    const formData = new FormData();
    formData.append('batch_id', currentBatch.id);
    formData.append('location_code', currentLocation.code); // Use the code string
    formData.append('quantity', quantity);
    formData.append('_token', '{{ csrf_token() }}');
    
    console.log('Sending pickup request:', {
        batch_id: currentBatch.id,
        location_code: currentLocation.code,
        quantity: quantity
    });
    
    fetch('{{ route("outbound.pickup") }}', {
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
            showMessage(data.message, 'success');
            
            // Update the UI with remaining stock
            if (data.data.batch_completed) {
                // Batch is completely picked
                showMessage(`Batch ${currentBatch.batch_number} is now complete!`, 'success');
                // Reset form
                productSelect.value = '';
                pickupArea.style.display = 'none';
                currentBatch = null;
                currentLocation = null;
            } else if (data.data.next_pickup) {
                // Update with next pickup location
                currentLocation = {
                    code: data.data.next_pickup.location_code,
                    quantity: data.data.next_pickup.quantity
                };
                maxQuantity = data.data.next_pickup.quantity;
                
                locationCode.textContent = data.data.next_pickup.location_code;
                availableInfo.innerHTML = `Available: ${data.data.next_pickup.quantity} units | Next depth: ${data.data.next_pickup.next_depth}`;
                
                // Update batch total
                const batchTotalElem = batchDetails.querySelector('div');
                if (batchTotalElem) {
                    const html = batchTotalElem.innerHTML;
                    batchTotalElem.innerHTML = html.replace(
                        /Total Available: \d+ units/,
                        `Total Available: ${data.data.remaining_in_batch} units`
                    );
                }
                
                pickupQuantity.value = Math.min(1, maxQuantity);
                pickupQuantity.max = maxQuantity;
                validateQuantity(parseInt(pickupQuantity.value));
                updateQuantityButtons();
                
                showMessage(`Moving to next location: ${data.data.next_pickup.location_code}`, 'success');
            } else if (data.data.remaining_in_location !== undefined) {
                // Same location, just update quantity
                const newRemaining = data.data.remaining_in_location;
                maxQuantity = newRemaining;
                currentLocation.quantity = newRemaining;
                availableInfo.innerHTML = `Available: ${newRemaining} units | Max pickup: ${newRemaining}`;
                
                pickupQuantity.value = Math.min(1, maxQuantity);
                pickupQuantity.max = maxQuantity;
                validateQuantity(parseInt(pickupQuantity.value));
                updateQuantityButtons();
                
                if (newRemaining === 0) {
                    locationCode.textContent = 'Location Empty';
                    availableInfo.textContent = 'No more items at this location';
                    confirmBtn.disabled = true;
                } else {
                    // Keep the same location but refresh the display
                    locationCode.textContent = currentLocation.code;
                }
            }
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMsg = 'Failed to process pickup. Please try again.';
        if (error.message) {
            errorMsg = error.message;
        }
        showMessage(errorMsg, 'error');
    })
    .finally(() => {
        confirmBtn.disabled = false;
        confirmBtn.textContent = '✅ Confirm Pickup';
    });
});
</script>
@endpush
@endsection