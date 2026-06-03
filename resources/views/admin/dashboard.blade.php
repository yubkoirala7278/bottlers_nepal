{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="color: #667eea; margin-bottom: 0.5rem;">Total Products</h3>
            <p style="font-size: 2rem; font-weight: bold;">{{ \App\Models\Product::count() }}</p>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="color: #667eea; margin-bottom: 0.5rem;">Total Batches</h3>
            <p style="font-size: 2rem; font-weight: bold;">{{ \App\Models\Batch::count() }}</p>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="color: #667eea; margin-bottom: 0.5rem;">Warehouse Locations</h3>
            <p style="font-size: 2rem; font-weight: bold;">{{ \App\Models\WarehouseLocation::count() }}</p>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="color: #667eea; margin-bottom: 0.5rem;">Total Inventory</h3>
            <p style="font-size: 2rem; font-weight: bold;">{{ \App\Models\Inventory::sum('quantity') }}</p>
        </div>
    </div>

    <div
        style="margin-top: 2rem; background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1rem;">Recent Batches</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Batch Number</th>
                        <th>Product</th>
                        <th>Production Date</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (\App\Models\Batch::with('product')->latest()->limit(10)->get() as $batch)
                        @php
                            $daysUntilExpiry = \Carbon\Carbon::now()->diffInDays($batch->expiry_date, false);
                            $statusClass =
                                $daysUntilExpiry < 0 ? 'danger' : ($daysUntilExpiry < 30 ? 'warning' : 'success');
                        @endphp
                        <tr>
                            <td>{{ $batch->batch_number }}</td>
                            <td>{{ $batch->product->full_name }}</td>
                            <td>{{ $batch->production_date->format('Y-m-d') }}</td>
                            <td>{{ $batch->expiry_date->format('Y-m-d') }}</td>
                            <td>
                                <span
                                    style="padding: 0.25rem 0.75rem; border-radius: 20px; background: {{ $statusClass == 'danger' ? '#dc3545' : ($statusClass == 'warning' ? '#ffc107' : '#28a745') }}; color: white; font-size: 0.75rem;">
                                    {{ $daysUntilExpiry >= 0 ? ($daysUntilExpiry < 30 ? 'Expiring Soon' : 'Good') : 'Expired' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
