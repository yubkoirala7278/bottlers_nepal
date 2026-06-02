{{-- resources/views/batches/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Batches Management')

@section('content')
<div class="batches-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <h1>Batches Management</h1>
    <a href="{{ route('batches.create') }}" class="btn btn-primary">+ Create New Batch</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Batch Number</th>
                <th>Production Date</th>
                <th>Expiry Date</th>
                <th>Days Until Expiry</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batches as $batch)
            @php
                $daysUntilExpiry = \Carbon\Carbon::now()->diffInDays($batch->expiry_date, false);
                $status = $daysUntilExpiry < 0 ? 'Expired' : ($daysUntilExpiry < 30 ? 'Expiring Soon' : 'Good');
                $statusClass = $daysUntilExpiry < 0 ? 'danger' : ($daysUntilExpiry < 30 ? 'warning' : 'success');
            @endphp
            <tr>
                <td>{{ $batch->id }}</td>
                <td>{{ $batch->product->full_name }}</td>
                <td><strong>{{ $batch->batch_number }}</strong></td>
                <td>{{ $batch->production_date->format('Y-m-d') }}</td>
                <td>{{ $batch->expiry_date->format('Y-m-d') }}</td>
                <td>{{ $daysUntilExpiry >= 0 ? $daysUntilExpiry . ' days' : 'Expired' }}</td>
                <td>
                    <span style="padding: 0.25rem 0.75rem; border-radius: 20px; background: {{ 
                        $statusClass == 'danger' ? '#dc3545' : ($statusClass == 'warning' ? '#ffc107' : '#28a745') 
                    }}; color: white; font-size: 0.75rem;">
                        {{ $status }}
                    </span>
                </td>
                <td>
                    <form action="{{ route('batches.destroy', $batch) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this batch? This will also delete all inventory associated with it.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">No batches found. Click "Create New Batch" to create one.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $batches->links() }}
</div>
@endsection