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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
            font-size: 2rem;
            font-weight: bold;
            color: #2e7d32;
            letter-spacing: 2px;
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

        .btn-reset {
            background: #6c757d;
            color: white;
            margin-top: 0.5rem;
        }

        .btn-reset:hover {
            background: #5a6268;
            transform: translateY(-2px);
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        .ack-code {
            font-family: monospace;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            letter-spacing: 4px;
        }

        .ack-status {
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .ack-valid {
            color: #4caf50;
        }

        .ack-invalid {
            color: #f44336;
        }

        /* Custom Alert Box */
        .custom-alert {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            text-align: center;
            min-width: 280px;
            max-width: 90%;
            animation: slideIn 0.3s ease-out;
        }

        .custom-alert.success {
            border-top: 4px solid #4caf50;
        }

        .custom-alert.error {
            border-top: 4px solid #f44336;
        }

        .custom-alert .alert-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .custom-alert .alert-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .custom-alert .alert-message {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .custom-alert .alert-btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.9);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .suggestion-highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            margin: 1rem 0;
            text-align: center;
            animation: pulse 1s ease-in-out;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.02);
            }
        }

        .suggestion-location {
            font-size: 2rem;
            font-weight: bold;
            letter-spacing: 4px;
            margin: 10px 0;
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
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 480px) {
            .outbound-card {
                padding: 1rem;
            }

            .location-code {
                font-size: 1.5rem;
            }

            .suggestion-location {
                font-size: 1.5rem;
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

        <div class="outbound-card">
            <form id="outboundForm">
                @csrf
                <div class="form-group">
                    <label for="product_id">Select Product to Pick</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">-- Select Product --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div id="pickupArea" style="display: none;">
                <div class="batch-details" id="batchDetails"></div>

                <div id="suggestionArea"></div>

                <div id="pickupForm" style="display: none;">
                    <div class="pick-form">
                        <label style="font-weight: 600;">Number of items to pick</label>
                        <div class="quantity-control">
                            <button type="button" id="decrementQty" style="background: #ff5722; color: white;">−</button>
                            <input type="number" id="pickupQuantity" min="1" value="1" step="1">
                            <button type="button" id="incrementQty" style="background: #4caf50; color: white;">+</button>
                        </div>
                        <div id="quantityError" class="validation-error" style="display: none;"></div>

                        <div class="form-group" style="margin-top: 1rem;">
                            <label for="ack_code">Acknowledgment Code *</label>
                            <input type="text" id="ack_code" name="ack_code" class="ack-code" placeholder="11111111"
                                maxlength="8" pattern="[0-9]{8}" autocomplete="off" required>
                            <div id="ack_status" class="ack-status"></div>
                            <small style="color: #666;">Enter 11111111 to confirm pickup</small>
                        </div>

                        <input type="hidden" id="selected_batch" name="batch_id">
                        <input type="hidden" id="selected_location" name="location_code">
                        <input type="hidden" id="required_ack" name="required_ack" value="11111111">

                        <button type="button" id="confirmPickupBtn" class="btn-pick" disabled>
                            ✅ Confirm Pickup
                        </button>
                        <button type="button" id="resetBtn" class="btn-pick btn-reset">
                            ⟳ Reset & Start New
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentBatch = null;
            let currentLocation = null;
            let maxQuantity = 0;
            let currentProductId = null;
            const FIXED_ACK_CODE = "11111111";

            const productSelect = document.getElementById('product_id');
            const pickupArea = document.getElementById('pickupArea');
            const batchDetails = document.getElementById('batchDetails');
            const suggestionArea = document.getElementById('suggestionArea');
            const pickupForm = document.getElementById('pickupForm');
            const pickupQuantity = document.getElementById('pickupQuantity');
            const decrementBtn = document.getElementById('decrementQty');
            const incrementBtn = document.getElementById('incrementQty');
            const confirmBtn = document.getElementById('confirmPickupBtn');
            const resetBtn = document.getElementById('resetBtn');
            const quantityError = document.getElementById('quantityError');
            const ackCodeInput = document.getElementById('ack_code');
            const ackStatus = document.getElementById('ack_status');
            const requiredAck = document.getElementById('required_ack');

            requiredAck.value = FIXED_ACK_CODE;

            function showAlert(message, type = 'success') {
                const existingAlert = document.querySelector('.custom-alert');
                const existingOverlay = document.querySelector('.overlay');
                if (existingAlert) existingAlert.remove();
                if (existingOverlay) existingOverlay.remove();

                const overlay = document.createElement('div');
                overlay.className = 'overlay';
                document.body.appendChild(overlay);

                const alertBox = document.createElement('div');
                alertBox.className = `custom-alert ${type}`;
                alertBox.innerHTML = `
            <div class="alert-icon">${type === 'success' ? '✓' : '✗'}</div>
            <div class="alert-title">${type === 'success' ? 'Success!' : 'Error!'}</div>
            <div class="alert-message">${message}</div>
            <button class="alert-btn" onclick="this.closest('.custom-alert').remove(); document.querySelector('.overlay').remove();">OK</button>
        `;
                document.body.appendChild(alertBox);

                if (type === 'success') {
                    setTimeout(() => {
                        if (alertBox) alertBox.remove();
                        if (overlay) overlay.remove();
                    }, 3000);
                }
            }

            function validateAckCode() {
                const enteredCode = ackCodeInput.value;

                if (enteredCode.length === 0) {
                    ackStatus.innerHTML = '';
                    ackStatus.className = 'ack-status';
                    confirmBtn.disabled = true;
                    return false;
                }

                if (enteredCode === FIXED_ACK_CODE) {
                    ackStatus.innerHTML = '✓ Code verified. You can proceed.';
                    ackStatus.className = 'ack-status ack-valid';
                    confirmBtn.disabled = false;
                    return true;
                } else {
                    ackStatus.innerHTML = '✗ Invalid acknowledgment code. Enter 11111111.';
                    ackStatus.className = 'ack-status ack-invalid';
                    confirmBtn.disabled = true;
                    return false;
                }
            }

            ackCodeInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
                validateAckCode();
            });

            function validateQuantity(value) {
                let num = parseInt(value);

                if (isNaN(num)) {
                    quantityError.textContent = 'Please enter a valid number';
                    quantityError.style.display = 'block';
                    return false;
                }

                if (num < 1) {
                    quantityError.textContent = `Minimum quantity is 1`;
                    quantityError.style.display = 'block';
                    return false;
                }

                if (num > maxQuantity) {
                    quantityError.textContent = `Maximum quantity is ${maxQuantity}`;
                    quantityError.style.display = 'block';
                    return false;
                }

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

                decrementBtn.disabled = currentValue <= 1;
                incrementBtn.disabled = currentValue >= maxQuantity;
            }

            function handleQuantityChange() {
                let value = pickupQuantity.value;
                let num = parseInt(value);

                if (validateQuantity(num)) {
                    pickupQuantity.value = num;
                } else {
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
                    quantityError.style.display = 'none';
                } else {
                    validateQuantity(num);
                }
                updateQuantityButtons();
            });

            pickupQuantity.addEventListener('blur', handleQuantityChange);

            decrementBtn.addEventListener('click', () => {
                let currentValue = parseInt(pickupQuantity.value);
                if (!isNaN(currentValue) && currentValue > 1) {
                    pickupQuantity.value = currentValue - 1;
                    validateQuantity(pickupQuantity.value);
                    updateQuantityButtons();
                }
            });

            incrementBtn.addEventListener('click', () => {
                let currentValue = parseInt(pickupQuantity.value);
                if (!isNaN(currentValue) && currentValue < maxQuantity) {
                    pickupQuantity.value = currentValue + 1;
                    validateQuantity(pickupQuantity.value);
                    updateQuantityButtons();
                }
            });

            function resetForm() {
                productSelect.value = '';
                pickupArea.style.display = 'none';
                pickupForm.style.display = 'none';
                suggestionArea.innerHTML = '';
                ackCodeInput.value = '';
                ackStatus.innerHTML = '';
                confirmBtn.disabled = true;
                currentBatch = null;
                currentLocation = null;
                currentProductId = null;
            }

            // Function to fetch next pickup location for same batch
            function fetchNextPickup() {
                if (!currentProductId) return;

                suggestionArea.innerHTML =
                    '<div class="loading"><div class="spinner"></div><p>Finding next available location...</p></div>';
                pickupForm.style.display = 'none';

                fetch('{{ route('outbound.oldest-batch') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: currentProductId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
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
                        <strong>📊 Total Available:</strong> ${data.total_quantity} units
                    </div>
                `;

                            suggestionArea.innerHTML = `
                    <div class="suggestion-highlight">
                        <div style="font-size: 0.85rem; opacity: 0.9;">🎯 Next Pickup Location (LIFO)</div>
                        <div class="suggestion-location">${data.location.code}</div>
                        <div style="font-size: 0.8rem;">Available: ${data.location.quantity} units</div>
                        <div style="font-size: 0.7rem; opacity: 0.8;">Next pickup depth: ${data.location.next_depth}</div>
                        <hr style="margin: 10px 0; border-color: rgba(255,255,255,0.2);">
                        <div style="font-size: 0.75rem;">Enter 11111111 to confirm pickup</div>
                    </div>
                `;

                            document.getElementById('selected_batch').value = currentBatch.id;
                            document.getElementById('selected_location').value = currentLocation.code;

                            pickupQuantity.value = Math.min(1, maxQuantity);
                            pickupQuantity.max = maxQuantity;
                            pickupQuantity.min = 1;
                            validateQuantity(parseInt(pickupQuantity.value));
                            updateQuantityButtons();

                            ackCodeInput.value = '';
                            ackStatus.innerHTML = '';
                            confirmBtn.disabled = true;
                            confirmBtn.textContent = '✅ Confirm Pickup';
                            pickupForm.style.display = 'block';
                        } else {
                            suggestionArea.innerHTML = `
                    <div class="suggestion-highlight" style="background: #f44336;">
                        <div style="font-size: 0.85rem;">⚠️ No More Stock</div>
                        <div class="suggestion-location">Complete</div>
                        <div>No more inventory available for this product</div>
                    </div>
                `;
                            pickupForm.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        suggestionArea.innerHTML = `
                <div class="suggestion-highlight" style="background: #f44336;">
                    <div>❌ Error finding next location</div>
                </div>
            `;
                        pickupForm.style.display = 'none';
                    });
            }

            resetBtn.addEventListener('click', () => {
                if (confirm('Reset current selection? You will need to select product again.')) {
                    resetForm();
                }
            });

            productSelect.addEventListener('change', function() {
                currentProductId = this.value;

                if (!this.value) {
                    pickupArea.style.display = 'none';
                    return;
                }

                pickupArea.style.display = 'block';
                suggestionArea.innerHTML =
                    '<div class="loading"><div class="spinner"></div><p>Finding oldest batch...</p></div>';
                pickupForm.style.display = 'none';
                confirmBtn.disabled = true;

                fetch('{{ route('outbound.oldest-batch') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: this.value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
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
                        <strong>📊 Total Available:</strong> ${data.total_quantity} units
                    </div>
                `;

                            suggestionArea.innerHTML = `
                    <div class="suggestion-highlight">
                        <div style="font-size: 0.85rem; opacity: 0.9;">🎯 Pickup Location (LIFO)</div>
                        <div class="suggestion-location">${data.location.code}</div>
                        <div style="font-size: 0.8rem;">Available: ${data.location.quantity} units</div>
                        <div style="font-size: 0.7rem; opacity: 0.8;">Next pickup depth: ${data.location.next_depth}</div>
                        <hr style="margin: 10px 0; border-color: rgba(255,255,255,0.2);">
                        <div style="font-size: 0.75rem;">Enter 11111111 to confirm pickup</div>
                    </div>
                `;

                            document.getElementById('selected_batch').value = currentBatch.id;
                            document.getElementById('selected_location').value = currentLocation.code;

                            pickupQuantity.value = Math.min(1, maxQuantity);
                            pickupQuantity.max = maxQuantity;
                            pickupQuantity.min = 1;
                            validateQuantity(parseInt(pickupQuantity.value));
                            updateQuantityButtons();

                            ackCodeInput.value = '';
                            ackStatus.innerHTML = '';
                            confirmBtn.disabled = true;
                            pickupForm.style.display = 'block';
                        } else {
                            batchDetails.innerHTML = `
                    <div style="color: #fff;">
                        <strong>❌ ${data.message || 'No inventory found'}</strong>
                    </div>
                `;
                            suggestionArea.innerHTML = '';
                            pickupForm.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        batchDetails.innerHTML = `
                <div style="color: #fff;">
                    <strong>❌ Error loading batch information</strong>
                </div>
            `;
                        suggestionArea.innerHTML = '';
                        pickupForm.style.display = 'none';
                        showAlert('Failed to load batch information', 'error');
                    });
            });

            confirmBtn.addEventListener('click', function() {
                if (!currentBatch || !currentLocation) {
                    showAlert('No batch selected', 'error');
                    return;
                }

                const quantity = parseInt(pickupQuantity.value);
                const ackCode = ackCodeInput.value;

                if (!validateQuantity(quantity)) {
                    showAlert(`Please enter a valid quantity between 1 and ${maxQuantity}`, 'error');
                    return;
                }

                if (ackCode !== FIXED_ACK_CODE) {
                    showAlert('Invalid acknowledgment code. Enter 11111111 to confirm pickup.', 'error');
                    ackCodeInput.focus();
                    return;
                }

                // Store button reference and disable
                const originalText = confirmBtn.textContent;
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Processing...';

                const formData = new FormData();
                formData.append('batch_id', currentBatch.id);
                formData.append('location_code', currentLocation.code);
                formData.append('quantity', quantity);
                formData.append('ack_code', ackCode);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('outbound.pickup') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert(data.message, 'success');

                            // Check if batch is completed (remaining_in_batch = 0)
                            if (data.data.batch_completed || data.data.remaining_in_batch === 0) {
                                showAlert(`Batch ${currentBatch.batch_number} is now complete!`, 'success');
                                // Reset button before fetching next batch
                                confirmBtn.disabled = false;
                                confirmBtn.textContent = originalText;
                                // Fetch next batch for same product
                                setTimeout(() => {
                                    fetchNextPickup();
                                }, 1500);
                                return;
                            }

                            // Check if current location is empty
                            if (data.data.remaining_in_location === 0) {
                                showAlert('Location empty. Finding next location...', 'success');
                                // Reset button before fetching next
                                confirmBtn.disabled = false;
                                confirmBtn.textContent = originalText;
                                setTimeout(() => {
                                    fetchNextPickup();
                                }, 1500);
                                return;
                            }

                            // Check if there's a next pickup location
                            if (data.data.next_pickup && data.data.next_pickup.quantity > 0) {
                                // Update with next pickup location from same batch
                                currentLocation = {
                                    code: data.data.next_pickup.location_code,
                                    quantity: data.data.next_pickup.quantity
                                };
                                maxQuantity = data.data.next_pickup.quantity;

                                suggestionArea.innerHTML = `
                    <div class="suggestion-highlight">
                        <div style="font-size: 0.85rem; opacity: 0.9;">🎯 Next Location (Same Batch)</div>
                        <div class="suggestion-location">${data.data.next_pickup.location_code}</div>
                        <div style="font-size: 0.8rem;">Available: ${data.data.next_pickup.quantity} units</div>
                        <div style="font-size: 0.7rem; opacity: 0.8;">Next pickup depth: ${data.data.next_pickup.next_depth}</div>
                        <hr style="margin: 10px 0; border-color: rgba(255,255,255,0.2);">
                        <div style="font-size: 0.75rem;">Enter 11111111 to confirm pickup</div>
                    </div>
                `;

                                document.getElementById('selected_location').value = currentLocation.code;

                                const batchTotalElem = batchDetails.querySelector('div');
                                if (batchTotalElem) {
                                    batchTotalElem.innerHTML = batchTotalElem.innerHTML.replace(
                                        /Total Available: \d+ units/,
                                        `Total Available: ${data.data.remaining_in_batch} units`
                                    );
                                }

                                pickupQuantity.value = Math.min(1, maxQuantity);
                                pickupQuantity.max = maxQuantity;
                                validateQuantity(parseInt(pickupQuantity.value));
                                updateQuantityButtons();

                                ackCodeInput.value = '';
                                ackStatus.innerHTML = '';
                                confirmBtn.disabled = true;
                                confirmBtn.textContent = originalText;
                                pickupForm.style.display = 'block';

                                showAlert(`Moving to next location: ${data.data.next_pickup.location_code}`,
                                    'success');
                                return;
                            }

                            // Same location has more stock
                            if (data.data.remaining_in_location !== undefined && data.data.remaining_in_location >
                                0) {
                                const newRemaining = data.data.remaining_in_location;
                                maxQuantity = newRemaining;
                                currentLocation.quantity = newRemaining;

                                suggestionArea.innerHTML = `
                    <div class="suggestion-highlight">
                        <div style="font-size: 0.85rem; opacity: 0.9;">🎯 Same Location (More Stock)</div>
                        <div class="suggestion-location">${currentLocation.code}</div>
                        <div style="font-size: 0.8rem;">Available: ${newRemaining} units</div>
                        <div style="font-size: 0.7rem; opacity: 0.8;">Continue picking from same location</div>
                        <hr style="margin: 10px 0; border-color: rgba(255,255,255,0.2);">
                        <div style="font-size: 0.75rem;">Enter 11111111 to confirm pickup</div>
                    </div>
                `;

                                pickupQuantity.value = Math.min(1, maxQuantity);
                                pickupQuantity.max = maxQuantity;
                                validateQuantity(parseInt(pickupQuantity.value));
                                updateQuantityButtons();

                                ackCodeInput.value = '';
                                ackStatus.innerHTML = '';
                                confirmBtn.disabled = true;
                                confirmBtn.textContent = originalText;
                            }
                        } else {
                            showAlert(data.message, 'error');
                            confirmBtn.disabled = false;
                            confirmBtn.textContent = originalText;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('Failed to process pickup. Please try again.', 'error');
                        confirmBtn.disabled = false;
                        confirmBtn.textContent = originalText;
                    });
            });

            // Update fetchNextPickup function
            function fetchNextPickup() {
                if (!currentProductId) return;

                suggestionArea.innerHTML =
                    '<div class="loading"><div class="spinner"></div><p>Finding next available location...</p></div>';
                pickupForm.style.display = 'none';

                fetch('{{ route('outbound.oldest-batch') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: currentProductId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.found && data.success && data.total_quantity > 0) {
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
                    <strong>📊 Total Available:</strong> ${data.total_quantity} units
                </div>
            `;

                            suggestionArea.innerHTML = `
                <div class="suggestion-highlight">
                    <div style="font-size: 0.85rem; opacity: 0.9;">🎯 Next Pickup Location (LIFO)</div>
                    <div class="suggestion-location">${data.location.code}</div>
                    <div style="font-size: 0.8rem;">Available: ${data.location.quantity} units</div>
                    <div style="font-size: 0.7rem; opacity: 0.8;">Next pickup depth: ${data.location.next_depth}</div>
                    <hr style="margin: 10px 0; border-color: rgba(255,255,255,0.2);">
                    <div style="font-size: 0.75rem;">Enter 11111111 to confirm pickup</div>
                </div>
            `;

                            document.getElementById('selected_batch').value = currentBatch.id;
                            document.getElementById('selected_location').value = currentLocation.code;

                            pickupQuantity.value = Math.min(1, maxQuantity);
                            pickupQuantity.max = maxQuantity;
                            pickupQuantity.min = 1;
                            validateQuantity(parseInt(pickupQuantity.value));
                            updateQuantityButtons();

                            ackCodeInput.value = '';
                            ackStatus.innerHTML = '';
                            confirmBtn.disabled = true;
                            confirmBtn.textContent = '✅ Confirm Pickup';
                            pickupForm.style.display = 'block';
                        } else {
                            // No more inventory
                            suggestionArea.innerHTML = `
                <div class="suggestion-highlight" style="background: #f44336;">
                    <div style="font-size: 0.85rem;">✅ All Done!</div>
                    <div class="suggestion-location">Complete</div>
                    <div>No more inventory available for this product</div>
                </div>
            `;
                            pickupForm.style.display = 'none';
                            // Clear batch details
                            batchDetails.innerHTML = `
                <div style="color: #fff;">
                    <strong>✅ No More Stock</strong><br>
                    All inventory has been picked for this product.
                </div>
            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        suggestionArea.innerHTML = `
            <div class="suggestion-highlight" style="background: #f44336;">
                <div>❌ Error finding next location</div>
            </div>
        `;
                        pickupForm.style.display = 'none';
                    });
            }
        </script>
    @endpush
@endsection
