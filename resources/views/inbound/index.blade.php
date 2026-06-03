{{-- resources/views/inbound/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Inbound Management')

@section('content')
    <style>
        .inbound-form {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .suggestion-card {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .suggestion-highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
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

        .btn-reset {
            background: #6c757d;
            color: white;
            margin-top: 0.5rem;
        }

        .btn-reset:hover {
            background: #5a6268;
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

        @media (max-width: 480px) {
            .inbound-form {
                padding: 1rem;
            }

            .form-group-mobile label {
                font-size: 0.8rem;
            }

            .suggestion-location {
                font-size: 1.5rem;
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
                        @foreach ($products as $product)
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

                    <div class="form-group-mobile">
                        <label for="ack_code">Acknowledgment Code *</label>
                        <input type="text" id="ack_code" name="ack_code" class="ack-code" placeholder="000000"
                            maxlength="6" pattern="[0-9]{6}" autocomplete="off" required>
                        <div id="ack_status" class="ack-status"></div>
                        <small style="color: #666;">Enter 000000 to confirm placement</small>
                    </div>

                    <input type="hidden" id="selected_location" name="location_code">
                    <input type="hidden" id="selected_batch" name="batch_id">
                    <input type="hidden" id="required_ack" name="required_ack" value="000000">

                    <button type="submit" class="btn btn-primary btn-mobile" id="submitBtn" disabled>✅ Confirm
                        Placement</button>
                    <button type="button" class="btn btn-secondary btn-mobile btn-reset" id="resetBtn">⟳ Reset & Start
                        New</button>
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
            const ackCodeInput = document.getElementById('ack_code');
            const ackStatus = document.getElementById('ack_status');
            const submitBtn = document.getElementById('submitBtn');
            const resetBtn = document.getElementById('resetBtn');
            const requiredAck = document.getElementById('required_ack');
            let currentSuggestion = null;

            // Fixed acknowledgment code
            const FIXED_ACK_CODE = "000000";
            requiredAck.value = FIXED_ACK_CODE;

            // Show custom alert
            function showAlert(message, type = 'success') {
                // Remove existing alerts
                const existingAlert = document.querySelector('.custom-alert');
                const existingOverlay = document.querySelector('.overlay');
                if (existingAlert) existingAlert.remove();
                if (existingOverlay) existingOverlay.remove();

                // Create overlay
                const overlay = document.createElement('div');
                overlay.className = 'overlay';
                document.body.appendChild(overlay);

                // Create alert box
                const alertBox = document.createElement('div');
                alertBox.className = `custom-alert ${type}`;
                alertBox.innerHTML = `
            <div class="alert-icon">${type === 'success' ? '✓' : '✗'}</div>
            <div class="alert-title">${type === 'success' ? 'Success!' : 'Error!'}</div>
            <div class="alert-message">${message}</div>
            <button class="alert-btn" onclick="this.closest('.custom-alert').remove(); document.querySelector('.overlay').remove();">OK</button>
        `;
                document.body.appendChild(alertBox);

                // Auto close after 3 seconds for success
                if (type === 'success') {
                    setTimeout(() => {
                        if (alertBox) alertBox.remove();
                        if (overlay) overlay.remove();
                    }, 3000);
                }
            }

            // Validate acknowledgment code
            function validateAckCode() {
                const enteredCode = ackCodeInput.value;

                if (enteredCode.length === 0) {
                    ackStatus.innerHTML = '';
                    ackStatus.className = 'ack-status';
                    submitBtn.disabled = true;
                    return false;
                }

                if (enteredCode === FIXED_ACK_CODE) {
                    ackStatus.innerHTML = '✓ Code verified. You can proceed.';
                    ackStatus.className = 'ack-status ack-valid';
                    submitBtn.disabled = false;
                    return true;
                } else {
                    ackStatus.innerHTML = '✗ Invalid acknowledgment code. Enter 000000.';
                    ackStatus.className = 'ack-status ack-invalid';
                    submitBtn.disabled = true;
                    return false;
                }
            }

            // Restrict input to numbers only
            ackCodeInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
                validateAckCode();
            });

            // Reset entire form
            function resetForm() {
                productSelect.value = '';
                batchSelect.disabled = true;
                batchSelect.innerHTML = '<option value="">-- First select product --</option>';
                suggestionArea.innerHTML = '';
                placementForm.style.display = 'none';
                ackCodeInput.value = '';
                ackStatus.innerHTML = '';
                submitBtn.disabled = true;
                currentSuggestion = null;
            }

            // Reset button click
            resetBtn.addEventListener('click', function() {
                if (confirm('Reset current selection? You will need to select product and batch again.')) {
                    resetForm();
                }
            });

            productSelect.addEventListener('change', function() {
                if (this.value) {
                    // Load batches for this product
                    fetch('{{ route('inbound.latest-batches') }}', {
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
                            batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';
                            if (data.length === 0) {
                                batchSelect.innerHTML += '<option value="">No batches available</option>';
                                batchSelect.disabled = true;
                            } else {
                                data.forEach(batch => {
                                    batchSelect.innerHTML +=
                                        `<option value="${batch.id}">${batch.batch_number} (Prod: ${batch.production_date})</option>`;
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
                    resetForm();
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
                    fetch('{{ route('inbound.find-place') }}', {
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
                        .then(response => response.json())
                        .then(data => {
                            if (data.found) {
                                currentSuggestion = data;

                                // Highlighted suggestion with large location display
                                suggestionArea.innerHTML = `
                        <div class="suggestion-highlight">
                            <div style="font-size: 0.85rem; opacity: 0.9;">🎯 Suggested Location</div>
                            <div class="suggestion-location">${data.location}</div>
                            <div style="font-size: 0.8rem;">Available Space: ${data.available_space} units</div>
                            <div style="font-size: 0.7rem; opacity: 0.8;">${data.message}</div>
                            <hr style="margin: 10px 0; border-color: rgba(255,255,255,0.2);">
                            <div style="font-size: 0.75rem;">Enter acknowledgment code to confirm</div>
                        </div>
                    `;

                                document.getElementById('selected_location').value = data.location;
                                document.getElementById('selected_batch').value = batchSelect.value;
                                const quantityInput = document.getElementById('quantity');
                                quantityInput.max = data.max_allowed;
                                quantityInput.value = Math.min(1, data.max_allowed);

                                // Reset acknowledgment field
                                ackCodeInput.value = '';
                                ackStatus.innerHTML = '';
                                submitBtn.disabled = true;

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
                const ackCode = ackCodeInput.value;

                if (!quantity || quantity < 1) {
                    showAlert('Please enter a valid quantity', 'error');
                    return;
                }

                // Validate acknowledgment code
                if (ackCode !== FIXED_ACK_CODE) {
                    showAlert('Invalid acknowledgment code. Enter 000000 to confirm placement.', 'error');
                    ackCodeInput.focus();
                    return;
                }

                // Store reference to button
                const submitBtn = document.querySelector('#inboundForm button[type="submit"]');
                const originalText = submitBtn.textContent;

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';

                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('batch_id', batchId);
                formData.append('location_code', locationCode);
                formData.append('quantity', quantity);
                formData.append('ack_code', ackCode);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('inbound.store') }}', {
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

                            // Reset button state for current operation
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;

                            // Reset form fields
                            ackCodeInput.value = '';
                            ackStatus.innerHTML = '';
                            document.getElementById('quantity').value = 1;

                            // Fetch new suggestion for the same product+batch
                            if (batchSelect.value && productSelect.value) {
                                // Show loading in suggestion area
                                suggestionArea.innerHTML = `
                    <div class="suggestion-card">
                        <strong>🔍 Finding next available location...</strong>
                    </div>
                `;

                                // Find new placement suggestion
                                fetch('{{ route('inbound.find-place') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            product_id: productSelect.value,
                                            batch_id: batchSelect.value
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(newData => {
                                        if (newData.found) {
                                            currentSuggestion = newData;

                                            // Update suggestion with new location
                                            suggestionArea.innerHTML = `
                            <div class="suggestion-highlight">
                                <div style="font-size: 0.85rem; opacity: 0.9;">🎯 Suggested Location</div>
                                <div class="suggestion-location">${newData.location}</div>
                                <div style="font-size: 0.8rem;">Available Space: ${newData.available_space} units</div>
                                <div style="font-size: 0.7rem; opacity: 0.8;">${newData.message}</div>
                                <hr style="margin: 10px 0; border-color: rgba(255,255,255,0.2);">
                                <div style="font-size: 0.75rem;">Enter 000000 to confirm placement</div>
                            </div>
                        `;

                                            document.getElementById('selected_location').value = newData
                                                .location;
                                            document.getElementById('selected_batch').value = batchSelect.value;
                                            const quantityInput = document.getElementById('quantity');
                                            quantityInput.max = newData.max_allowed;
                                            quantityInput.value = Math.min(1, newData.max_allowed);

                                            // Reset acknowledgment field
                                            ackCodeInput.value = '';
                                            ackStatus.innerHTML = '';
                                            submitBtn.disabled = true;
                                        } else {
                                            suggestionArea.innerHTML = `
                            <div class="suggestion-card" style="background: #ffebee; border-left-color: #f44336;">
                                <strong>⚠️ No More Space</strong><br>
                                ${newData.message}<br>
                                <small>This batch is fully placed. Select a different batch or product.</small>
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
                            Could not find next location. Please try again.
                        </div>
                    `;
                                    });
                            }
                        } else {
                            showAlert(data.message, 'error');
                            // Reset button on error
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('Failed to process request. Please try again.', 'error');
                        // Reset button on error
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    });
            });
        </script>
    @endpush
@endsection
