{{-- resources/views/admin/bulk-outbound.blade.php --}}
@extends('layouts.app')

@section('title', 'Bulk Outbound - Admin')

@section('content')
    <style>
        .bulk-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .bulk-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
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
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .locations-list {
            margin-top: 1rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .location-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .location-item:hover {
            transform: translateX(5px);
            border-color: #667eea;
        }

        .location-item.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }

        .location-code {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .location-info {
            font-size: 0.875rem;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .quantity-control button {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            background: white;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quantity-control button:active {
            transform: scale(0.95);
        }

        .quantity-control input {
            flex: 1;
            text-align: center;
            font-size: 1.25rem;
            font-weight: bold;
            padding: 0.75rem;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .info-box {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .loading {
            text-align: center;
            padding: 2rem;
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

        @media (max-width: 768px) {
            .bulk-card {
                padding: 1rem;
            }

            .location-item {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .quantity-control button {
                width: 40px;
                height: 40px;
            }
        }
    </style>

    <div class="bulk-container">
        <h1 style="margin-bottom: 1.5rem;">📤 Bulk Outbound Management</h1>

        <div class="info-box">
            <h4>ℹ️ Bulk Outbound Instructions</h4>
            <p>Select a product and batch to see all locations where it's stored. Then choose a location and the quantity to
                pick. The system will automatically use LIFO (Last In First Out) - picking from the highest depth first.</p>
        </div>

        <div class="bulk-card">
            <form method="POST" action="{{ route('admin.bulk.outbound.process') }}" id="bulkOutboundForm">
                @csrf

                <div class="form-group">
                    <label for="product_id">Select Product *</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">-- Select Product --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="batch_id">Select Batch *</label>
                    <select id="batch_id" name="batch_id" required disabled>
                        <option value="">-- First select product --</option>
                    </select>
                </div>

                <div id="locationsContainer" style="display: none;">
                    <div class="form-group">
                        <label>Select Location to Pick From *</label>
                        <div id="locationsList" class="locations-list"></div>
                        <input type="hidden" id="selected_location" name="location_code" required>
                    </div>

                    <div class="form-group">
                        <label for="pickup_quantity">Number of Products to Pick *</label>
                        <div class="quantity-control">
                            <button type="button" id="decrementQty"
                                style="background: #ff5722; color: white; border: none;">−</button>
                            <input type="number" id="pickup_quantity" name="quantity" min="1" value="1"
                                required>
                            <button type="button" id="incrementQty"
                                style="background: #4caf50; color: white; border: none;">+</button>
                        </div>
                        <small id="quantityInfo" style="color: #666; display: block; margin-top: 0.5rem;"></small>
                    </div>

                    <button type="submit" class="btn-submit">✅ Confirm Pickup</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentLocations = [];
            let selectedLocationData = null;
            let currentMaxQuantity = 0;

            const productSelect = document.getElementById('product_id');
            const batchSelect = document.getElementById('batch_id');
            const locationsContainer = document.getElementById('locationsContainer');
            const locationsList = document.getElementById('locationsList');
            const quantityInput = document.getElementById('pickup_quantity');
            const decrementBtn = document.getElementById('decrementQty');
            const incrementBtn = document.getElementById('incrementQty');
            const quantityInfo = document.getElementById('quantityInfo');
            const selectedLocationInput = document.getElementById('selected_location');

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
                        });
                } else {
                    batchSelect.disabled = true;
                    batchSelect.innerHTML = '<option value="">-- First select product --</option>';
                    locationsContainer.style.display = 'none';
                }
            });

            // Load locations when batch is selected
            batchSelect.addEventListener('change', function() {
                if (this.value && productSelect.value) {
                    locationsList.innerHTML =
                        '<div class="loading"><div class="spinner"></div><p>Loading locations...</p></div>';
                    locationsContainer.style.display = 'block';

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
                            currentLocations = data;

                            if (data.length === 0) {
                                locationsList.innerHTML =
                                    '<div style="text-align: center; padding: 2rem; color: #999;">No locations found for this batch</div>';
                                return;
                            }

                            locationsList.innerHTML = '';
                            data.forEach(location => {
                                const locationDiv = document.createElement('div');
                                locationDiv.className = 'location-item';
                                locationDiv.dataset.location = location.location_code;
                                locationDiv.dataset.quantity = location.quantity;
                                locationDiv.dataset.maxPick = location.max_pick;
                                locationDiv.innerHTML = `
                        <div>
                            <div class="location-code">📍 ${location.location_code}</div>
                            <div class="location-info">Available: ${location.quantity} units</div>
                        </div>
                        <div class="location-info">Depth positions: ${location.depth_positions?.join(', ') || 'N/A'}</div>
                    `;
                                locationDiv.onclick = () => selectLocation(locationDiv, location);
                                locationsList.appendChild(locationDiv);
                            });
                        });
                } else {
                    locationsContainer.style.display = 'none';
                }
            });

            function selectLocation(element, location) {
                // Remove selected class from all
                document.querySelectorAll('.location-item').forEach(item => {
                    item.classList.remove('selected');
                });

                // Add selected class
                element.classList.add('selected');

                selectedLocationData = location;
                selectedLocationInput.value = location.location_code;
                currentMaxQuantity = location.max_pick;

                quantityInput.max = currentMaxQuantity;
                quantityInput.value = Math.min(1, currentMaxQuantity);
                quantityInfo.innerHTML =
                    `Selected: ${location.location_code} | Available: ${location.quantity} units | Max pick: ${currentMaxQuantity}`;

                updateButtons();
            }

            function updateButtons() {
                let currentValue = parseInt(quantityInput.value);

                if (currentValue <= 1) {
                    decrementBtn.disabled = true;
                } else {
                    decrementBtn.disabled = false;
                }

                if (currentValue >= currentMaxQuantity) {
                    incrementBtn.disabled = true;
                } else {
                    incrementBtn.disabled = false;
                }
            }

            decrementBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                    updateButtons();
                }
            });

            incrementBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue < currentMaxQuantity) {
                    quantityInput.value = currentValue + 1;
                    updateButtons();
                }
            });

            quantityInput.addEventListener('change', () => {
                let value = parseInt(quantityInput.value);
                if (isNaN(value)) value = 1;
                if (value < 1) value = 1;
                if (value > currentMaxQuantity) value = currentMaxQuantity;
                quantityInput.value = value;
                updateButtons();
            });

            // Form validation
            document.getElementById('bulkOutboundForm').addEventListener('submit', function(e) {
                if (!productSelect.value) {
                    e.preventDefault();
                    alert('Please select a product');
                    return false;
                }

                if (!batchSelect.value) {
                    e.preventDefault();
                    alert('Please select a batch');
                    return false;
                }

                if (!selectedLocationInput.value) {
                    e.preventDefault();
                    alert('Please select a location to pick from');
                    return false;
                }

                const quantity = parseInt(quantityInput.value);
                if (quantity < 1 || quantity > currentMaxQuantity) {
                    e.preventDefault();
                    alert(`Please enter a quantity between 1 and ${currentMaxQuantity}`);
                    return false;
                }

                return true;
            });
        </script>
    @endpush
@endsection
