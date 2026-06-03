{{-- resources/views/admin/bulk-outbound.blade.php --}}
@extends('layouts.app')

@section('title', 'Bulk Outbound - Admin')

@section('content')
    <style>
        .bulk-container {
            margin: 0 auto;
        }

        .bulk-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1a1a2e;
            font-size: 0.875rem;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
            background: white;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Locations List */
        .locations-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }

        .location-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }

        .location-item:hover {
            background: #f3f4f6;
            transform: translateX(4px);
        }

        .location-item.selected {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
        }

        .location-info {
            flex: 1;
        }

        .location-code {
            font-weight: 700;
            font-size: 1rem;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .location-details {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .location-quantity {
            text-align: right;
            font-weight: 600;
            color: #4f46e5;
            font-size: 0.875rem;
        }

        .depth-badge {
            display: inline-block;
            background: #e5e7eb;
            padding: 0.125rem 0.375rem;
            border-radius: 4px;
            font-size: 0.65rem;
            margin-left: 0.5rem;
        }

        /* Depth Visualization */
        .depth-visualization {
            background: #f9fafb;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #e5e7eb;
        }

        .depth-title {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
            color: #374151;
        }

        .depth-bars {
            display: flex;
            flex-direction: column;
            gap: 2px;
            max-height: 300px;
            overflow-y: auto;
        }

        .depth-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.7rem;
        }

        .depth-label {
            width: 35px;
            text-align: right;
            color: #6b7280;
        }

        .depth-bar-container {
            flex: 1;
            height: 24px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .depth-bar {
            height: 100%;
            background: #4f46e5;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 4px;
            color: white;
            font-size: 0.6rem;
            font-weight: 600;
        }

        .depth-bar.occupied {
            background: #10b981;
        }

        /* Quantity Control */
        .quantity-section {
            background: #f9fafb;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #e5e7eb;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: white;
            font-size: 1.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #4b5563;
        }

        .quantity-btn:hover:not(:disabled) {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .quantity-input {
            flex: 1;
            text-align: center;
            font-size: 1rem;
            font-weight: 600;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: white;
        }

        .info-text {
            margin-top: 0.75rem;
            padding: 0.5rem;
            background: white;
            border-radius: 6px;
            font-size: 0.75rem;
            color: #4b5563;
            border: 1px solid #e5e7eb;
        }

        .btn-submit {
            background: #4f46e5;
            color: white;
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background: #4338ca;
        }

        .btn-submit:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .info-box {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .info-box h4 {
            margin-bottom: 0.5rem;
            color: #065f46;
            font-size: 0.875rem;
        }

        .info-box p {
            font-size: 0.75rem;
            color: #047857;
        }

        .info-box ul {
            margin-left: 1.25rem;
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: #047857;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #e5e7eb;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 0.5rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #9ca3af;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-size: 0.875rem;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        .toast-success {
            background: #10b981;
        }

        .toast-error {
            background: #ef4444;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .bulk-card {
                padding: 1rem;
            }

            .location-item {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .location-quantity {
                text-align: center;
            }

            .quantity-btn {
                width: 36px;
                height: 36px;
            }
        }
    </style>

    <div class="bulk-container">
        <h1 style="margin-bottom: 1.5rem; font-size: 1.5rem; color: #1f2937;">Bulk Outbound</h1>

        <div class="info-box">
            <h4>LIFO Picking Algorithm (Last In, First Out)</h4>
            <ul>
                <li>Items are picked from the highest depth number first (top of stack)</li>
                <li>After picking, remaining items automatically shift up to fill gaps</li>
                <li>This maintains LIFO order for future picks</li>
                <li>Example: If depths 50,49,48 are occupied, pick from 50 first, then 49, then 48</li>
            </ul>
        </div>

        <div class="bulk-card">
            <form id="bulkOutboundForm">
                @csrf

                <div class="form-group">
                    <label for="product_id">Select Product</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">Select product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} - {{ $product->sku }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="batch_id">Select Batch</label>
                    <select id="batch_id" name="batch_id" required disabled>
                        <option value="">Select product first</option>
                    </select>
                </div>

                <div id="locationsContainer" style="display: none;">
                    <div class="form-group">
                        <label>Select Location to Pick From</label>
                        <div id="locationsList" class="locations-list">
                            <div class="loading">
                                <div class="spinner"></div>
                                <p>Loading locations...</p>
                            </div>
                        </div>
                        <input type="hidden" id="selected_location" name="location_code">
                    </div>

                    <div id="depthVisualization" class="depth-visualization" style="display: none;">
                        <div class="depth-title">Depth Visualization (LIFO Order - Pick from Top)</div>
                        <div id="depthBars" class="depth-bars"></div>
                        <div class="info-text" style="margin-top: 0.5rem; font-size: 0.7rem;">
                            <span style="color: #10b981;">■</span> Occupied |
                            <span style="color: #e5e7eb;">■</span> Empty |
                            <span style="color: #4f46e5;">■</span> Will be picked next (highest depth)
                        </div>
                    </div>

                    <div id="quantitySection" style="display: none;">
                        <div class="quantity-section">
                            <label style="font-weight: 600; font-size: 0.875rem;">Number of Items to Pick</label>
                            <div class="quantity-control">
                                <button type="button" class="quantity-btn" id="decrementQty">−</button>
                                <input type="number" id="pickupQuantity" name="quantity" min="1" value="1"
                                    class="quantity-input">
                                <button type="button" class="quantity-btn" id="incrementQty">+</button>
                            </div>
                            <div id="pickupInfo" class="info-text"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn" style="display: none;">Confirm Pickup</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentBatchId = null;
            let currentLocation = null;
            let currentMaxQuantity = 0;
            let currentDepthPositions = [];

            const productSelect = document.getElementById('product_id');
            const batchSelect = document.getElementById('batch_id');
            const locationsContainer = document.getElementById('locationsContainer');
            const locationsList = document.getElementById('locationsList');
            const depthVisualization = document.getElementById('depthVisualization');
            const quantitySection = document.getElementById('quantitySection');
            const quantityInput = document.getElementById('pickupQuantity');
            const decrementBtn = document.getElementById('decrementQty');
            const incrementBtn = document.getElementById('incrementQty');
            const pickupInfo = document.getElementById('pickupInfo');
            const selectedLocationInput = document.getElementById('selected_location');
            const submitBtn = document.getElementById('submitBtn');

            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerHTML = type === 'success' ? '✓ ' + message : '✗ ' + message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }

            // Load batches when product is selected
            productSelect.addEventListener('change', function() {
                if (this.value) {
                    fetch('{{ route('inbound.latest-batches') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                product_id: this.value
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            batchSelect.innerHTML = '<option value="">Select batch</option>';
                            if (data.length === 0) {
                                batchSelect.innerHTML += '<option value="">No batches available</option>';
                                batchSelect.disabled = true;
                                locationsContainer.style.display = 'none';
                            } else {
                                data.forEach(batch => {
                                    batchSelect.innerHTML +=
                                        `<option value="${batch.id}">${batch.batch_number} (Prod: ${batch.production_date})</option>`;
                                });
                                batchSelect.disabled = false;
                            }
                        });
                } else {
                    batchSelect.disabled = true;
                    batchSelect.innerHTML = '<option value="">Select product first</option>';
                    locationsContainer.style.display = 'none';
                }
            });

            // Load locations when batch is selected
            batchSelect.addEventListener('change', function() {
                if (this.value && productSelect.value) {
                    currentBatchId = this.value;
                    locationsContainer.style.display = 'block';
                    locationsList.innerHTML =
                        '<div class="loading"><div class="spinner"></div><p>Loading locations...</p></div>';
                    depthVisualization.style.display = 'none';
                    quantitySection.style.display = 'none';
                    submitBtn.style.display = 'none';

                    fetch('{{ route('admin.bulk.outbound.locations') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                product_id: productSelect.value,
                                batch_id: this.value
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.length === 0) {
                                locationsList.innerHTML =
                                    '<div class="empty-state">No locations found for this batch</div>';
                            } else {
                                locationsList.innerHTML = '';
                                data.forEach(location => {
                                    const depthPositions = location.depth_positions || [];
                                    const maxDepth = Math.max(...depthPositions, 0);

                                    const locationDiv = document.createElement('div');
                                    locationDiv.className = 'location-item';
                                    locationDiv.dataset.location = location.location_code;
                                    locationDiv.dataset.quantity = location.quantity;
                                    locationDiv.dataset.maxPick = location.max_pick;
                                    locationDiv.dataset.depths = JSON.stringify(depthPositions);
                                    locationDiv.innerHTML = `
                            <div class="location-info">
                                <div class="location-code">${location.location_code}</div>
                                <div class="location-details">
                                    Available: ${location.quantity} units
                                    ${maxDepth > 0 ? `<span class="depth-badge">Next pick: Depth ${maxDepth}</span>` : ''}
                                </div>
                            </div>
                            <div class="location-quantity">
                                ${location.quantity} units
                            </div>
                        `;
                                    locationDiv.onclick = () => selectLocation(locationDiv, location);
                                    locationsList.appendChild(locationDiv);
                                });
                            }
                        });
                }
            });

            function selectLocation(element, location) {
                document.querySelectorAll('.location-item').forEach(item => {
                    item.classList.remove('selected');
                });
                element.classList.add('selected');

                currentLocation = location;
                currentMaxQuantity = location.max_pick;
                currentDepthPositions = location.depth_positions || [];

                selectedLocationInput.value = location.location_code;
                quantityInput.max = currentMaxQuantity;
                quantityInput.value = Math.min(1, currentMaxQuantity);

                // Show depth visualization
                showDepthVisualization(location.location_code, currentDepthPositions);

                // Show pickup info
                const nextPickDepth = currentDepthPositions.length > 0 ? Math.max(...currentDepthPositions) : 0;
                pickupInfo.innerHTML = `
            <strong>Location:</strong> ${location.location_code}<br>
            <strong>Available:</strong> ${location.quantity} units<br>
            <strong>Next Pick Depth:</strong> ${nextPickDepth > 0 ? nextPickDepth : 'None'}<br>
            <strong>Max Pick:</strong> ${currentMaxQuantity} units<br>
            <span style="color: #4f46e5;">LIFO: Will pick from highest depth first (${nextPickDepth})</span>
        `;

                quantitySection.style.display = 'block';
                submitBtn.style.display = 'block';
                updateButtons();
            }

            function showDepthVisualization(locationCode, depthPositions) {
                depthVisualization.style.display = 'block';
                const depthBars = document.getElementById('depthBars');
                depthBars.innerHTML = '';

                // Show from highest depth (50) down to lowest (1)
                for (let depth = 50; depth >= 1; depth--) {
                    const isOccupied = depthPositions.includes(depth);
                    const isNextPick = isOccupied && depth === Math.max(...depthPositions);

                    const row = document.createElement('div');
                    row.className = 'depth-row';
                    row.innerHTML = `
                <div class="depth-label">Depth ${depth}</div>
                <div class="depth-bar-container">
                    <div class="depth-bar ${isOccupied ? 'occupied' : ''}" 
                         style="width: ${isOccupied ? '100%' : '0%'}; ${isNextPick ? 'background: #4f46e5;' : ''}">
                        ${isOccupied ? (isNextPick ? 'Next' : depth) : ''}
                    </div>
                </div>
            `;
                    depthBars.appendChild(row);
                }
            }

            function updateButtons() {
                let val = parseInt(quantityInput.value);
                if (isNaN(val)) val = 1;
                decrementBtn.disabled = val <= 1;
                incrementBtn.disabled = val >= currentMaxQuantity;
            }

            decrementBtn.addEventListener('click', () => {
                let val = parseInt(quantityInput.value);
                if (val > 1) {
                    quantityInput.value = val - 1;
                    updateButtons();
                }
            });

            incrementBtn.addEventListener('click', () => {
                let val = parseInt(quantityInput.value);
                if (val < currentMaxQuantity) {
                    quantityInput.value = val + 1;
                    updateButtons();
                }
            });

            quantityInput.addEventListener('change', () => {
                let val = parseInt(quantityInput.value);
                if (isNaN(val)) val = 1;
                val = Math.min(Math.max(val, 1), currentMaxQuantity);
                quantityInput.value = val;
                updateButtons();
            });

            // Handle form submission with AJAX
            document.getElementById('bulkOutboundForm').addEventListener('submit', function(e) {
                e.preventDefault();

                if (!productSelect.value || !batchSelect.value || !selectedLocationInput.value) {
                    showToast('Please complete all selections', 'error');
                    return false;
                }

                const quantity = parseInt(quantityInput.value);
                if (quantity < 1 || quantity > currentMaxQuantity) {
                    showToast(`Quantity must be between 1 and ${currentMaxQuantity}`, 'error');
                    return false;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';

                const formData = new FormData();
                formData.append('batch_id', batchSelect.value);
                formData.append('location_code', selectedLocationInput.value);
                formData.append('quantity', quantity);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('admin.bulk.outbound.process') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            // Reset and reload
                            resetAndReload();
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Failed to process pickup. Please try again.', 'error');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Confirm Pickup';
                    });
            });

            function resetAndReload() {
                // Reset selection
                selectedLocationInput.value = '';
                quantitySection.style.display = 'none';
                submitBtn.style.display = 'none';
                depthVisualization.style.display = 'none';
                currentLocation = null;
                currentMaxQuantity = 0;

                // Clear selected class
                document.querySelectorAll('.location-item').forEach(item => {
                    item.classList.remove('selected');
                });

                // Reload locations to reflect updated inventory
                if (batchSelect.value && productSelect.value) {
                    locationsList.innerHTML =
                        '<div class="loading"><div class="spinner"></div><p>Reloading locations...</p></div>';

                    fetch('{{ route('admin.bulk.outbound.locations') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                product_id: productSelect.value,
                                batch_id: batchSelect.value
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.length === 0) {
                                locationsList.innerHTML =
                                    '<div class="empty-state">No locations found for this batch</div>';
                                locationsContainer.style.display = 'none';
                            } else {
                                locationsList.innerHTML = '';
                                data.forEach(location => {
                                    const depthPositions = location.depth_positions || [];
                                    const maxDepth = Math.max(...depthPositions, 0);

                                    const locationDiv = document.createElement('div');
                                    locationDiv.className = 'location-item';
                                    locationDiv.dataset.location = location.location_code;
                                    locationDiv.dataset.quantity = location.quantity;
                                    locationDiv.dataset.maxPick = location.max_pick;
                                    locationDiv.dataset.depths = JSON.stringify(depthPositions);
                                    locationDiv.innerHTML = `
                                    <div class="location-info">
                                        <div class="location-code">${location.location_code}</div>
                                        <div class="location-details">
                                            Available: ${location.quantity} units
                                            ${maxDepth > 0 ? `<span class="depth-badge">Next pick: Depth ${maxDepth}</span>` : ''}
                                        </div>
                                    </div>
                                    <div class="location-quantity">
                                        ${location.quantity} units
                                    </div>
                                `;
                                    locationDiv.onclick = () => selectLocation(locationDiv, location);
                                    locationsList.appendChild(locationDiv);
                                });
                            }
                        });
                }
            }
        </script>
    @endpush
@endsection
