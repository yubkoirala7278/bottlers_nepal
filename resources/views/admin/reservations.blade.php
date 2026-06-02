{{-- resources/views/admin/reservations.blade.php --}}
@extends('layouts.app')

@section('title', 'Location Reservations Management')

@section('content')
    <style>
        .reservations-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .reservation-form {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 0.875rem;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .reservations-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .reservations-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            font-weight: 600;
        }

        .reservation-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
            transition: background 0.3s;
        }

        .reservation-item:hover {
            background: #f8f9fa;
        }

        .reservation-info {
            flex: 1;
        }

        .reservation-location {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
        }

        .reservation-details {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .reservation-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .badge-batch {
            background: #ff9800;
            color: white;
        }

        .badge-product {
            background: #4caf50;
            color: white;
        }

        .reservation-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-edit,
        .btn-delete {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #2196f3;
            color: white;
        }

        .btn-edit:hover {
            background: #1976d2;
        }

        .btn-delete {
            background: #f44336;
            color: white;
        }

        .btn-delete:hover {
            background: #d32f2f;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .location-map-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 0.5rem;
            margin-top: 1rem;
            max-height: 400px;
            overflow-y: auto;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .location-cell-reserve {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .location-cell-reserve:hover {
            transform: scale(1.05);
            border-color: #667eea;
        }

        .location-cell-reserve.reserved {
            background: #ff9800;
            color: white;
            border-color: #ff9800;
        }

        .location-cell-reserve.occupied {
            background: #f44336;
            color: white;
            border-color: #f44336;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .location-cell-reserve.available {
            background: #4caf50;
            color: white;
            border-color: #4caf50;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .close-modal {
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .reservation-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .reservation-actions {
                width: 100%;
            }

            .btn-edit,
            .btn-delete {
                flex: 1;
                text-align: center;
            }
        }
    </style>

    <div class="reservations-container">
        <h1 style="margin-bottom: 1.5rem;">🔒 Location Reservations Management</h1>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $reservations->count() }}</div>
                <div class="stat-label">Active Reservations</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    {{ $locations->whereNotIn('id', array_keys($reservedLocations))->where('current_fill', 0)->count() }}
                </div>
                <div class="stat-label">Available Locations</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $locations->where('current_fill', '>', 0)->count() }}</div>
                <div class="stat-label">Occupied Locations</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $products->count() }}</div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>

        <!-- Create Reservation Form -->
        <div class="reservation-form">
            <h3 style="margin-bottom: 1rem;">➕ Create New Reservation</h3>
            <form method="POST" action="{{ route('admin.reservations.store') }}" id="reservationForm">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="location_code">Select Location *</label>
                        <select id="location_code" name="location_code" required>
                            <option value="">-- Select Location --</option>
                            @foreach ($locations as $location)
                                @php
                                    $isReserved = isset($reservedLocations[$location->id]);
                                    $isOccupied = $location->current_fill > 0;
                                    $status = $isReserved
                                        ? '🔒 RESERVED'
                                        : ($isOccupied
                                            ? '📦 OCCUPIED'
                                            : '✅ AVAILABLE');
                                @endphp
                                <option value="{{ $location->location_code }}"
                                    {{ $isReserved || $isOccupied ? 'disabled' : '' }}
                                    style="{{ $isReserved || $isOccupied ? 'color:#999' : 'color:#333' }}">
                                    {{ $location->location_code }} - {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reservation_type">Reservation Type *</label>
                        <select id="reservation_type" name="reservation_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="product_batch">Specific Product + Batch</option>
                            <option value="product_only">Specific Product Only (Any Batch)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" id="productRow">
                    <div class="form-group">
                        <label for="product_id">Select Product *</label>
                        <select id="product_id" name="product_id">
                            <option value="">-- Select Product --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row" id="batchRow" style="display: none;">
                    <div class="form-group">
                        <label for="batch_id">Select Batch *</label>
                        <select id="batch_id" name="batch_id">
                            <option value="">-- Select Batch --</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}">{{ $batch->batch_number }}
                                    ({{ $batch->product->full_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Create Reservation</button>
            </form>
        </div>

        <!-- Visual Location Map -->
        <div style="background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">🗺️ Warehouse Location Map</h3>
            <div class="location-map-grid">
                @foreach ($locations as $location)
                    @php
                        $isReserved = isset($reservedLocations[$location->id]);
                        $isOccupied = $location->current_fill > 0;
                        $statusClass = $isReserved ? 'reserved' : ($isOccupied ? 'occupied' : 'available');
                        $title = $isReserved
                            ? 'Reserved'
                            : ($isOccupied
                                ? "Occupied: {$location->current_fill}/{$location->max_depth}"
                                : 'Available');
                    @endphp
                    <div class="location-cell-reserve {{ $statusClass }}" data-location="{{ $location->location_code }}"
                        data-status="{{ $statusClass }}" onclick="selectLocation('{{ $location->location_code }}')"
                        title="{{ $title }}">
                        {{ $location->location_code }}
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Reservations List -->
        <div class="reservations-list">
            <div class="reservations-header">
                📋 Active Reservations
            </div>

            @if ($reservations->count() > 0)
                @foreach ($reservations as $reservation)
                    <div class="reservation-item">
                        <div class="reservation-info">
                            <div class="reservation-location">
                                📍 {{ $reservation->warehouseLocation->location_code }}
                                @if ($reservation->reservation_type == 'product_batch')
                                    <span class="reservation-badge badge-batch">Product + Batch</span>
                                @else
                                    <span class="reservation-badge badge-product">Product Only</span>
                                @endif
                            </div>
                            <div class="reservation-details">
                                @if ($reservation->reservation_type == 'product_batch')
                                    <strong>Product:</strong> {{ $reservation->batch->product->full_name }}<br>
                                    <strong>Batch:</strong> {{ $reservation->batch->batch_number }}
                                @else
                                    <strong>Product:</strong> {{ $reservation->product->full_name }}
                                @endif
                            </div>
                            <div class="reservation-details">
                                <strong>Created:</strong> {{ $reservation->created_at->format('Y-m-d H:i:s') }}
                            </div>
                        </div>
                        <div class="reservation-actions">
                            <button class="btn-edit" onclick="editReservation({{ $reservation->id }})">Edit</button>
                            <form method="POST" action="{{ route('admin.reservations.delete', $reservation->id) }}"
                                style="display: inline;"
                                onsubmit="return confirm('Are you sure you want to delete this reservation?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <p>No reservations found.</p>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem;">Create your first reservation using the form above.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Reservation Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Reservation</h3>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit_location_code">Location</label>
                    <select id="edit_location_code" name="location_code" required>
                        <option value="">-- Select Location --</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->location_code }}">
                                {{ $location->location_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_reservation_type">Reservation Type</label>
                    <select id="edit_reservation_type" name="reservation_type" required>
                        <option value="product_batch">Specific Product + Batch</option>
                        <option value="product_only">Specific Product Only</option>
                    </select>
                </div>

                <div class="form-group" id="edit_product_group">
                    <label for="edit_product_id">Product</label>
                    <select id="edit_product_id" name="product_id">
                        <option value="">-- Select Product --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="edit_batch_group" style="display: none;">
                    <label for="edit_batch_id">Batch</label>
                    <select id="edit_batch_id" name="batch_id">
                        <option value="">-- Select Batch --</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}">{{ $batch->batch_number }}
                                ({{ $batch->product->full_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn-submit">Update Reservation</button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const reservationType = document.getElementById('reservation_type');
            const batchRow = document.getElementById('batchRow');
            const productSelect = document.getElementById('product_id');
            const batchSelect = document.getElementById('batch_id');

            // Filter batches based on selected product
            productSelect.addEventListener('change', function() {
                const productId = this.value;
                if (productId && reservationType.value === 'product_batch') {
                    fetch(`/admin/get-batches-by-product/${productId}`)
                        .then(response => response.json())
                        .then(data => {
                            batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';
                            data.forEach(batch => {
                                batchSelect.innerHTML +=
                                    `<option value="${batch.id}">${batch.batch_number} (Prod: ${batch.production_date})</option>`;
                            });
                        });
                }
            });

            // Show/hide batch selection based on reservation type
            reservationType.addEventListener('change', function() {
                const productGroup = document.getElementById('productRow');
                const productSelect = document.getElementById('product_id');
                const batchGroup = document.getElementById('batchRow');
                const batchSelect = document.getElementById('batch_id');

                if (this.value === 'product_batch') {
                    // Product + Batch mode
                    productGroup.style.display = 'block';
                    batchGroup.style.display = 'block';
                    productSelect.required = true;
                    batchSelect.required = true;
                    document.querySelector('label[for="product_id"]').innerHTML = 'Select Product *';
                } else if (this.value === 'product_only') {
                    // Product Only mode
                    productGroup.style.display = 'block';
                    batchGroup.style.display = 'none';
                    productSelect.required = true;
                    batchSelect.required = false;
                    batchSelect.value = '';
                    document.querySelector('label[for="product_id"]').innerHTML = 'Select Product * (Any Batch)';
                } else {
                    productGroup.style.display = 'none';
                    batchGroup.style.display = 'none';
                    productSelect.required = false;
                    batchSelect.required = false;
                }
            });

            document.getElementById('reservationForm').addEventListener('submit', function(e) {
                const reservationType = document.getElementById('reservation_type').value;
                const productId = document.getElementById('product_id').value;
                const batchId = document.getElementById('batch_id').value;

                if (!reservationType) {
                    e.preventDefault();
                    alert('Please select a reservation type');
                    return false;
                }

                if (!productId) {
                    e.preventDefault();
                    alert('Please select a product');
                    return false;
                }

                if (reservationType === 'product_batch' && !batchId) {
                    e.preventDefault();
                    alert('Please select a batch for this reservation');
                    return false;
                }

                return true;
            });

            function selectLocation(locationCode) {
                document.getElementById('location_code').value = locationCode;
                // Scroll to form
                document.querySelector('.reservation-form').scrollIntoView({
                    behavior: 'smooth'
                });
            }

            let currentEditId = null;

            function editReservation(id) {
                currentEditId = id;

                // Fetch reservation details
                fetch(`/admin/get-reservation/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('edit_location_code').value = data.location_code;
                        document.getElementById('edit_reservation_type').value = data.reservation_type;
                        document.getElementById('edit_product_id').value = data.product_id;

                        // Trigger change event to show/hide batch field
                        const event = new Event('change');
                        document.getElementById('edit_reservation_type').dispatchEvent(event);

                        if (data.reservation_type === 'product_batch') {
                            // Load batches for this product
                            fetch(`/admin/get-batches-by-product/${data.product_id}`)
                                .then(response => response.json())
                                .then(batches => {
                                    const batchSelect = document.getElementById('edit_batch_id');
                                    batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';
                                    batches.forEach(batch => {
                                        batchSelect.innerHTML += `<option value="${batch.id}" ${batch.id == data.batch_id ? 'selected' : ''}>
                                ${batch.batch_number} (Prod: ${batch.production_date})
                            </option>`;
                                    });
                                    document.getElementById('edit_batch_id').value = data.batch_id;
                                });
                        }

                        document.getElementById('editForm').action = `/admin/reservations/${id}`;
                        document.getElementById('editModal').style.display = 'flex';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to load reservation details');
                    });
            }

            function closeModal() {
                document.getElementById('editModal').style.display = 'none';
            }

            // Handle edit form reservation type change
            document.getElementById('edit_reservation_type').addEventListener('change', function() {
                const productGroup = document.getElementById('edit_product_group');
                const batchGroup = document.getElementById('edit_batch_group');
                const productSelect = document.getElementById('edit_product_id');
                const batchSelect = document.getElementById('edit_batch_id');

                if (this.value === 'product_batch') {
                    productGroup.style.display = 'block';
                    batchGroup.style.display = 'block';
                    productSelect.required = true;
                    batchSelect.required = true;
                } else if (this.value === 'product_only') {
                    productGroup.style.display = 'block';
                    batchGroup.style.display = 'none';
                    productSelect.required = true;
                    batchSelect.required = false;
                    batchSelect.value = '';
                } else {
                    productGroup.style.display = 'none';
                    batchGroup.style.display = 'none';
                    productSelect.required = false;
                    batchSelect.required = false;
                }
            });

            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('editModal');
                if (event.target === modal) {
                    closeModal();
                }
            }
        </script>
    @endpush
@endsection
