{{-- resources/views/admin/bulk-inbound.blade.php --}}
@extends('layouts.app')

@section('title', 'Bulk Inbound - Admin')

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

        /* Legend */
        .legend {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: #4b5563;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }

        .legend-dot.empty {
            background: #10b981;
        }

        .legend-dot.same-batch {
            background: #f59e0b;
        }

        .legend-dot.different {
            background: #ef4444;
        }

        .legend-dot.reserved {
            background: #3b82f6;
        }

        /* Location Grid */
        .location-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 0.75rem;
            max-height: 480px;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .location-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 0.5rem;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
        }

        .location-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .location-card.selected {
            border-width: 2px;
            background: #f0fdf4;
        }

        /* Location Status Styles */
        .location-card.empty {
            border-left: 3px solid #10b981;
        }

        .location-card.empty.selected {
            background: #ecfdf5;
            border-color: #10b981;
        }

        .location-card.same-batch {
            border-left: 3px solid #f59e0b;
            background: #fffbeb;
        }

        .location-card.same-batch.selected {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .location-card.different-batch {
            border-left: 3px solid #ef4444;
            background: #fef2f2;
            opacity: 0.6;
            cursor: not-allowed;
        }

        .location-card.different-batch:hover {
            transform: none;
            box-shadow: none;
        }

        .location-card.reserved {
            border-left: 3px solid #3b82f6;
            background: #eff6ff;
            opacity: 0.6;
            cursor: not-allowed;
        }

        .location-card.reserved:hover {
            transform: none;
            box-shadow: none;
        }

        .location-code {
            font-weight: 700;
            font-size: 0.875rem;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .location-fill {
            font-size: 0.7rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .location-batch {
            font-size: 0.65rem;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 0.25rem;
        }

        .status-text {
            display: inline-block;
            font-size: 0.6rem;
            font-weight: 600;
            padding: 0.125rem 0.375rem;
            border-radius: 4px;
            margin-top: 0.25rem;
        }

        .status-text.same-batch {
            background: #fef3c7;
            color: #92400e;
        }

        .status-text.empty {
            background: #d1fae5;
            color: #065f46;
        }

        .status-text.different {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-text.reserved {
            background: #dbeafe;
            color: #1e40af;
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

        @media (max-width: 640px) {
            .bulk-card {
                padding: 1rem;
            }

            .location-grid {
                grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
                gap: 0.5rem;
            }

            .location-code {
                font-size: 0.75rem;
            }

            .quantity-btn {
                width: 36px;
                height: 36px;
            }
        }
    </style>

    <div class="bulk-container">
        <h1 style="margin-bottom: 1.5rem; font-size: 1.5rem; color: #1f2937;">Bulk Inbound</h1>

        <div class="info-box">
            <h4>Information</h4>
            <ul>
                <li>Green border - Empty locations (fully available)</li>
                <li>Orange border - Same batch locations (can add more)</li>
                <li>Red border - Different batch locations (cannot mix)</li>
                <li>Blue border - Reserved locations</li>
            </ul>
        </div>

        <div class="bulk-card">
            <form method="POST" action="{{ route('admin.bulk.inbound.process') }}" id="bulkInboundForm">
                @csrf

                <div class="form-group">
                    <label for="product_id">Product</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">Select product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} - {{ $product->sku }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="batch_id">Batch</label>
                    <select id="batch_id" name="batch_id" required disabled>
                        <option value="">Select product first</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Warehouse Locations</label>
                    <div class="legend">
                        <div class="legend-item">
                            <div class="legend-dot empty"></div>
                            <span>Empty</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot same-batch"></div>
                            <span>Same Batch</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot different"></div>
                            <span>Different Batch</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot reserved"></div>
                            <span>Reserved</span>
                        </div>
                    </div>
                    <div id="locationGrid" class="location-grid">
                        <div class="loading">
                            <div class="spinner"></div>
                            <p>Select a batch to view locations</p>
                        </div>
                    </div>
                    <input type="hidden" id="selected_location" name="location_code" required>
                </div>

                <div id="quantitySection" style="display: none;">
                    <div class="quantity-section">
                        <label style="font-weight: 600; font-size: 0.875rem;">Quantity to Place</label>
                        <div class="quantity-control">
                            <button type="button" class="quantity-btn" id="decrementQty">−</button>
                            <input type="number" id="quantity" name="quantity" min="1" max="50"
                                value="1" class="quantity-input">
                            <button type="button" class="quantity-btn" id="incrementQty">+</button>
                        </div>
                        <div id="spaceInfo" class="info-text"></div>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn" style="display: none;">Confirm Placement</button>
            </form>
        </div>
    </div>

    {{-- Update the JavaScript in resources/views/admin/bulk-inbound.blade.php --}}
    @push('scripts')
        <script>
            let currentMaxQuantity = 0;
            let currentLocationData = null;

            const productSelect = document.getElementById('product_id');
            const batchSelect = document.getElementById('batch_id');
            const locationGrid = document.getElementById('locationGrid');
            const quantitySection = document.getElementById('quantitySection');
            const quantityInput = document.getElementById('quantity');
            const decrementBtn = document.getElementById('decrementQty');
            const incrementBtn = document.getElementById('incrementQty');
            const spaceInfo = document.getElementById('spaceInfo');
            const selectedLocationInput = document.getElementById('selected_location');
            const submitBtn = document.getElementById('submitBtn');
            const bulkForm = document.getElementById('bulkInboundForm');

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
                            } else {
                                data.forEach(batch => {
                                    batchSelect.innerHTML +=
                                        `<option value="${batch.id}">${batch.batch_number} (Prod: ${batch.production_date})</option>`;
                                });
                                batchSelect.disabled = false;
                            }
                            locationGrid.innerHTML =
                                '<div class="loading"><div class="spinner"></div><p>Select a batch to view locations</p></div>';
                            quantitySection.style.display = 'none';
                            submitBtn.style.display = 'none';
                            selectedLocationInput.value = '';
                        });
                } else {
                    batchSelect.disabled = true;
                    batchSelect.innerHTML = '<option value="">Select product first</option>';
                    locationGrid.innerHTML = '<div class="loading"><p>Select a product first</p></div>';
                    quantitySection.style.display = 'none';
                    submitBtn.style.display = 'none';
                }
            });

            function loadLocations() {
                if (batchSelect.value && productSelect.value) {
                    locationGrid.innerHTML =
                        '<div class="loading"><div class="spinner"></div><p>Loading locations...</p></div>';

                    const selectedBatchId = parseInt(batchSelect.value);
                    const selectedProductId = parseInt(productSelect.value);

                    Promise.all([
                            fetch('{{ route('admin.get.inventory.with.batches') }}').then(res => res.json()),
                            fetch('{{ route('admin.get.all.locations') }}').then(res => res.json())
                        ])
                        .then(([inventoryData, locations]) => {
                            renderLocationGrid(locations, inventoryData, selectedBatchId, selectedProductId);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            locationGrid.innerHTML =
                                '<div class="loading" style="color: #dc2626;"><p>Error loading locations. Please refresh.</p></div>';
                        });
                }
            }

            batchSelect.addEventListener('change', function() {
                if (this.value && productSelect.value) {
                    loadLocations();
                }
            });

            function renderLocationGrid(locations, inventoryData, selectedBatchId, selectedProductId) {
                if (!locations || locations.length === 0) {
                    locationGrid.innerHTML = '<div class="loading"><p>No locations found</p></div>';
                    return;
                }

                locationGrid.innerHTML = '';

                locations.forEach(location => {
                    const locationCode = location.location_code;
                    const locationId = location.id;
                    const maxDepth = location.max_depth;
                    const currentFill = location.current_fill;
                    const availableSpace = maxDepth - currentFill;

                    const inventoryAtLocation = inventoryData.find(inv => inv.warehouse_location_id === locationId);
                    const reservationAtLocation = inventoryData.find(inv => inv.warehouse_location_id === locationId &&
                        inv.is_reserved === true);

                    let statusClass = '';
                    let statusLabel = '';
                    let canSelect = false;
                    let batchInfo = '';
                    let clickHandler = null;

                    // Check if location has inventory
                    if (inventoryAtLocation && inventoryAtLocation.quantity > 0 && !inventoryAtLocation.is_reserved) {
                        const inventoryBatchId = inventoryAtLocation.batch_id;
                        const inventoryBatchNumber = inventoryAtLocation.batch_number;
                        const inventoryQuantity = inventoryAtLocation.quantity;

                        if (inventoryBatchId === selectedBatchId) {
                            statusClass = 'same-batch';
                            statusLabel = 'Same Batch';
                            canSelect = true;
                            batchInfo = `Batch: ${inventoryBatchNumber} | ${inventoryQuantity}/${maxDepth}`;
                            clickHandler = () => selectLocation(locationCode, availableSpace, true, inventoryQuantity,
                                maxDepth, inventoryBatchNumber);
                        } else {
                            statusClass = 'different-batch';
                            statusLabel = 'Different Batch';
                            canSelect = false;
                            batchInfo = `Has: ${inventoryBatchNumber}`;
                        }
                    }
                    // Check if location is reserved
                    else if (reservationAtLocation && reservationAtLocation.is_reserved) {
                        const reservedBatchId = reservationAtLocation.reserved_batch_id;
                        const reservedProductId = reservationAtLocation.reserved_product_id;
                        const reservedBatchNumber = reservationAtLocation.reserved_batch_number;

                        // If reservation is for the same batch AND same product, allow placement
                        if (reservedBatchId === selectedBatchId && reservedProductId === selectedProductId) {
                            statusClass = 'same-batch';
                            statusLabel = 'Reserved for this batch';
                            canSelect = true;
                            batchInfo = `Reserved for this batch | ${availableSpace}/${maxDepth} spaces`;
                            clickHandler = () => selectLocation(locationCode, availableSpace, true, 0, maxDepth,
                                reservedBatchNumber);
                        }
                        // If reservation is for a different batch, block
                        else if (reservedBatchId && reservedBatchId !== selectedBatchId) {
                            statusClass = 'reserved';
                            statusLabel = 'Reserved - Different';
                            canSelect = false;
                            batchInfo = `Reserved for batch: ${reservedBatchNumber}`;
                        }
                        // If reservation is product-only and same product, allow placement
                        else if (reservedProductId === selectedProductId && !reservedBatchId) {
                            statusClass = 'same-batch';
                            statusLabel = 'Reserved for product';
                            canSelect = true;
                            batchInfo = `Reserved for this product | ${availableSpace}/${maxDepth} spaces`;
                            clickHandler = () => selectLocation(locationCode, availableSpace, true, 0, maxDepth, null);
                        }
                        // If reservation is for a different product, block
                        else if (reservedProductId && reservedProductId !== selectedProductId) {
                            statusClass = 'reserved';
                            statusLabel = 'Reserved - Different';
                            canSelect = false;
                            batchInfo = `Reserved for different product`;
                        } else {
                            statusClass = 'reserved';
                            statusLabel = 'Reserved';
                            canSelect = false;
                            batchInfo = 'Reserved location';
                        }
                    }
                    // Empty location
                    else {
                        statusClass = 'empty';
                        statusLabel = 'Empty';
                        canSelect = true;
                        batchInfo = `${availableSpace} spaces available`;
                        clickHandler = () => selectLocation(locationCode, maxDepth, false, 0, maxDepth, null);
                    }

                    const card = document.createElement('div');
                    card.className = `location-card ${statusClass}`;
                    card.innerHTML = `
            <div class="location-code">${locationCode}</div>
            <div class="location-fill">${currentFill}/${maxDepth}</div>
            <div class="location-batch">${batchInfo}</div>
            <span class="status-text ${statusClass}">${statusLabel}</span>
        `;

                    if (canSelect && clickHandler) {
                        card.style.cursor = 'pointer';
                        card.onclick = clickHandler;
                    } else {
                        card.style.cursor = 'not-allowed';
                    }

                    locationGrid.appendChild(card);
                });
            }

            function selectLocation(locationCode, availableSpace, isSameBatch, currentQuantity, maxDepth, batchNumber) {
                document.querySelectorAll('.location-card').forEach(card => {
                    card.classList.remove('selected');
                    if (card.innerHTML.includes(locationCode)) {
                        card.classList.add('selected');
                    }
                });

                currentMaxQuantity = availableSpace;
                selectedLocationInput.value = locationCode;
                quantityInput.max = currentMaxQuantity;
                quantityInput.value = Math.min(1, currentMaxQuantity);

                let infoHtml = `
        <strong>Location:</strong> ${locationCode}<br>
        <strong>Current Fill:</strong> ${currentQuantity}/${maxDepth}<br>
        <strong>Available Space:</strong> ${availableSpace} units<br>
        <strong>Max to Place:</strong> ${currentMaxQuantity} units
    `;

                if (isSameBatch) {
                    let nextDepth = maxDepth - currentQuantity;
                    infoHtml += `<br><span style="color: #d97706;">✓ Same batch (${batchNumber})</span>`;
                    infoHtml +=
                        `<br><span style="color: #6b7280;">Depth-first: Will fill from depth ${nextDepth} down to ${nextDepth - currentMaxQuantity + 1}</span>`;
                } else {
                    infoHtml += `<br><span style="color: #10b981;">✓ Empty location</span>`;
                    infoHtml +=
                        `<br><span style="color: #6b7280;">Depth-first: Will fill from depth ${maxDepth} down to ${maxDepth - currentMaxQuantity + 1}</span>`;
                }

                spaceInfo.innerHTML = infoHtml;
                quantitySection.style.display = 'block';
                submitBtn.style.display = 'block';
                updateButtons();
            }

            function resetForm() {
                // Reset selection
                selectedLocationInput.value = '';
                quantitySection.style.display = 'none';
                submitBtn.style.display = 'none';
                currentMaxQuantity = 0;

                // Clear selected class from all location cards
                document.querySelectorAll('.location-card').forEach(card => {
                    card.classList.remove('selected');
                });

                // Reload locations to reflect updated inventory
                if (batchSelect.value && productSelect.value) {
                    loadLocations();
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

            // Handle form submission with AJAX to prevent page reload
            bulkForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!productSelect.value || !batchSelect.value || !selectedLocationInput.value) {
                    alert('Please complete all selections');
                    return false;
                }

                const qty = parseInt(quantityInput.value);
                if (qty < 1 || qty > currentMaxQuantity) {
                    alert(`Quantity must be between 1 and ${currentMaxQuantity}`);
                    return false;
                }

                // Disable submit button to prevent double submission
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';

                const formData = new FormData();
                formData.append('product_id', productSelect.value);
                formData.append('batch_id', batchSelect.value);
                formData.append('location_code', selectedLocationInput.value);
                formData.append('quantity', qty);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('admin.bulk.inbound.process') }}', {
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
                            // Show success message
                            const successMsg = document.createElement('div');
                            successMsg.style.cssText =
                                'position: fixed; top: 20px; right: 20px; background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; z-index: 1000; animation: slideIn 0.3s ease-out;';
                            successMsg.innerHTML = '✓ ' + data.message;
                            document.body.appendChild(successMsg);

                            setTimeout(() => {
                                successMsg.remove();
                            }, 3000);

                            // Reset the form and reload locations
                            resetForm();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to process request. Please try again.');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Confirm Placement';
                    });
            });

            // Add animation style
            const style = document.createElement('style');
            style.textContent = `
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
    `;
            document.head.appendChild(style);
        </script>
    @endpush
@endsection
