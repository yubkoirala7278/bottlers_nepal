{{-- resources/views/warehouse/matrix.blade.php --}}
@extends('layouts.app')

@section('title', 'Warehouse Live Matrix')

@section('content')
<style>
    .matrix-container {
        overflow-x: auto;
        background: white;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .matrix-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }
    
    .matrix-table th,
    .matrix-table td {
        border: 1px solid #e0e0e0;
        padding: 12px;
        text-align: center;
        vertical-align: middle;
        transition: all 0.3s ease;
    }
    
    .matrix-table th {
        background: #f8f9fa;
        font-weight: 600;
        position: sticky;
        top: 0;
    }
    
    .location-cell {
        cursor: pointer;
        transition: transform 0.2s;
        min-width: 120px;
    }
    
    .location-cell:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10;
    }
    
    .cell-content {
        font-size: 0.75rem;
    }
    
    .cell-content strong {
        display: block;
        font-size: 0.875rem;
        margin-bottom: 4px;
    }
    
    .empty-cell {
        background: #f5f5f5;
        color: #999;
    }
    
    .reserved-cell {
        background: #fff3e0;
        border: 2px solid #ff9800;
    }
    
    .progress-bar {
        background: #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 5px;
        height: 6px;
    }
    
    .progress-fill {
        background: #4caf50;
        height: 100%;
        transition: width 0.3s;
    }
    
    .refresh-indicator {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        padding: 8px 16px;
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        font-size: 0.75rem;
        z-index: 1000;
    }
    
    @media (max-width: 768px) {
        .location-cell {
            min-width: 80px;
        }
        
        .cell-content {
            font-size: 0.625rem;
        }
        
        .cell-content strong {
            font-size: 0.75rem;
        }
    }
</style>

<div style="margin-bottom: 2rem;">
    <h1>Warehouse Live Matrix</h1>
    <p style="color: #666;">Real-time view of all warehouse locations - Updates every 10 seconds</p>
</div>

<div class="matrix-container">
    <table class="matrix-table">
        <thead>
            <tr>
                <th>Location</th>
                @foreach($levels as $level)
                    <th>{{ $level }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($heights as $height)
            <tr>
                <th>Height {{ $height }}</th>
                @foreach($levels as $level)
                    @php
                        $location = $matrix[$level][$height] ?? null;
                        $cellClass = '';
                        $bgColor = 'white';
                        $content = '';
                        
                        if ($location) {
                            if ($location->inventory->isNotEmpty()) {
                                $inv = $location->inventory->first();
                                $product = $inv->batch->product;
                                $bgColor = $product->color_code;
                                $content = "<div class='cell-content'>
                                    <strong>{$product->name}</strong>
                                    <div>{$product->sku}</div>
                                    <div style='font-size:0.7rem'>Batch: {$inv->batch->batch_number}</div>
                                    <div>{$location->current_fill}/{$location->max_depth}</div>
                                    <div class='progress-bar'>
                                        <div class='progress-fill' style='width: " . ($location->current_fill / $location->max_depth * 100) . "%'></div>
                                    </div>
                                </div>";
                            } elseif ($location->reservation) {
                                $cellClass = 'reserved-cell';
                                $reservedText = $location->reservation->batch_id ? 
                                    "Batch Reserved" : "Product Reserved";
                                $content = "<div class='cell-content'>
                                    <strong>🔒 RESERVED</strong>
                                    <div style='font-size:0.7rem'>{$reservedText}</div>
                                    <div>0/{$location->max_depth}</div>
                                </div>";
                            } else {
                                $cellClass = 'empty-cell';
                                $content = "<div class='cell-content'>
                                    <strong>EMPTY</strong>
                                    <div>0/{$location->max_depth}</div>
                                </div>";
                            }
                        }
                    @endphp
                    <td class="location-cell {{ $cellClass }}" style="background: {{ $bgColor }}; color: {{ $bgColor != 'white' ? 'white' : '#333' }}">
                        {!! $content !!}
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="refresh-indicator" id="refreshIndicator">
    Last updated: <span id="lastUpdate">Just now</span>
</div>

@push('scripts')
<script>
    let autoRefreshInterval;
    
    function updateMatrix() {
        fetch('{{ route("warehouse.matrix.data") }}')
            .then(response => response.json())
            .then(data => {
                const cells = document.querySelectorAll('.location-cell');
                cells.forEach(cell => {
                    // This would update individual cells based on data
                    // For simplicity, we'll reload the page data
                    location.reload();
                });
            })
            .catch(error => console.error('Error fetching matrix data:', error));
    }
    
    function startAutoRefresh() {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        autoRefreshInterval = setInterval(() => {
            updateMatrix();
            document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
        }, 10000);
    }
    
    startAutoRefresh();
</script>
@endpush
@endsection