{{-- resources/views/warehouse/matrix-full.blade.php --}}
@extends('layouts.matrix-layout')

@section('title', 'Live Warehouse Matrix')

@section('content')
<style>
    .matrix-container {
        padding: 1rem;
        min-width: 100%;
        height: 100vh;
        display: flex;
        flex-direction: column;
    }
    
    .matrix-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0.5rem;
        background: rgba(255,255,255,0.05);
        border-radius: 12px;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .title h1 {
        font-size: 1.25rem;
        margin-bottom: 0.25rem;
    }
    
    .title p {
        font-size: 0.75rem;
        opacity: 0.7;
    }
    
    .stats {
        display: flex;
        gap: 1rem;
    }
    
    .stat {
        background: rgba(255,255,255,0.1);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-align: center;
    }
    
    .stat-value {
        font-size: 1.25rem;
        font-weight: bold;
    }
    
    .stat-label {
        font-size: 0.7rem;
        opacity: 0.7;
    }
    
    .refresh-control {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .refresh-btn {
        background: #4caf50;
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.3s;
    }
    
    .refresh-btn:hover {
        background: #45a049;
        transform: scale(1.05);
    }
    
    .auto-refresh {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
    }
    
    .auto-refresh input {
        width: 40px;
        padding: 0.25rem;
        border-radius: 4px;
        border: none;
        text-align: center;
    }
    
    .last-update {
        font-size: 0.7rem;
        opacity: 0.7;
    }
    
    .matrix-scroll {
        overflow-x: auto;
        flex: 1;
        background: rgba(255,255,255,0.05);
        border-radius: 12px;
        padding: 1rem;
    }
    
    .matrix-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }
    
    .matrix-table th,
    .matrix-table td {
        border: 1px solid rgba(255,255,255,0.1);
        padding: 12px;
        text-align: center;
        vertical-align: middle;
        transition: all 0.3s ease;
    }
    
    .matrix-table th {
        background: rgba(0,0,0,0.3);
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .location-cell {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }
    
    .location-cell:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 20;
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
        background: rgba(255,255,255,0.05);
    }
    
    .reserved-cell {
        background: rgba(255,152,0,0.2);
        border: 2px solid #ff9800;
    }
    
    .progress-bar {
        background: rgba(0,0,0,0.3);
        border-radius: 10px;
        overflow: hidden;
        margin-top: 5px;
        height: 4px;
    }
    
    .progress-fill {
            background: #4caf50;
        height: 100%;
        transition: width 0.3s;
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        display: none;
    }
    
    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .depth-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.9);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        white-space: nowrap;
        pointer-events: none;
        z-index: 30;
        display: none;
    }
    
    .location-cell:hover .depth-tooltip {
        display: block;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .updating {
        animation: pulse 1s ease-in-out;
    }
    
    @media (max-width: 768px) {
        .matrix-container {
            padding: 0.5rem;
        }
        
        .matrix-table th,
        .matrix-table td {
            padding: 6px;
            font-size: 0.7rem;
        }
        
        .cell-content {
            font-size: 0.65rem;
        }
        
        .cell-content strong {
            font-size: 0.75rem;
        }
        
        .stats {
            gap: 0.5rem;
        }
        
        .stat {
            padding: 0.25rem 0.5rem;
        }
        
        .stat-value {
            font-size: 1rem;
        }
    }
</style>

<div class="matrix-container">
    <div class="matrix-header">
        <div class="title">
            <h1>🏭 Bottlers Nepal - Live Warehouse Matrix</h1>
            <p>Real-time inventory visualization | Depth-first storage mapping</p>
        </div>
        
        <div class="stats">
            <div class="stat">
                <div class="stat-value" id="totalLocations">0</div>
                <div class="stat-label">Total Locations</div>
            </div>
            <div class="stat">
                <div class="stat-value" id="occupiedLocations">0</div>
                <div class="stat-label">Occupied</div>
            </div>
            <div class="stat">
                <div class="stat-value" id="totalItems">0</div>
                <div class="stat-label">Total Items</div>
            </div>
            <div class="stat">
                <div class="stat-value" id="fillRate">0%</div>
                <div class="stat-label">Fill Rate</div>
            </div>
        </div>
        
        <div class="refresh-control">
            <div class="auto-refresh">
                <span>🔄 Auto</span>
                <input type="number" id="refreshInterval" value="10" min="3" max="60" step="1">
                <span>sec</span>
            </div>
            <button class="refresh-btn" id="manualRefresh">⟳ Refresh Now</button>
            <div class="last-update" id="lastUpdate">Last update: Just now</div>
        </div>
    </div>
    
    <div class="matrix-scroll">
        <div id="matrixContent">
            <div style="text-align: center; padding: 2rem;">
                <div class="spinner"></div>
                <p style="margin-top: 1rem;">Loading warehouse matrix...</p>
            </div>
        </div>
    </div>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

@push('scripts')
<script>
    let autoRefreshTimer = null;
    let currentData = null;
    let levels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
    let heights = [6, 5, 4, 3, 2, 1];
    
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }
    
    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }
    
    function updateLastUpdateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        document.getElementById('lastUpdate').innerHTML = `Last update: ${timeString}`;
    }
    
    function calculateStats(data) {
        let totalLocations = 0;
        let occupiedLocations = 0;
        let totalItems = 0;
        let totalCapacity = 0;
        
        for (const [code, location] of Object.entries(data)) {
            totalLocations++;
            totalCapacity += 50; // Each location has max depth 50
            
            if (location && location.quantity && location.quantity > 0) {
                occupiedLocations++;
                totalItems += location.quantity;
            }
        }
        
        const fillRate = totalCapacity > 0 ? (totalItems / totalCapacity * 100).toFixed(1) : 0;
        
        document.getElementById('totalLocations').textContent = totalLocations;
        document.getElementById('occupiedLocations').textContent = occupiedLocations;
        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('fillRate').textContent = fillRate + '%';
    }
    
    function renderMatrix(data) {
        let html = `
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th>Location</th>
                        ${levels.map(level => `<th>${level}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
        `;
        
        for (const height of heights) {
            html += `<tr>
                <th style="background: rgba(0,0,0,0.3);">Height ${height}</th>`;
            
            for (const level of levels) {
                const locationCode = level + height;
                const location = data[locationCode];
                
                let cellClass = '';
                let bgColor = 'rgba(255,255,255,0.05)';
                let content = '';
                let tooltip = '';
                
                if (location) {
                    if (location.product_name && location.quantity > 0) {
                        bgColor = location.color_code || '#4caf50';
                        const fillPercentage = (location.quantity / location.max_depth) * 100;
                        content = `
                            <div class="cell-content">
                                <strong>${location.product_name}</strong>
                                <div style="font-size:0.7rem">${location.sku}</div>
                                <div style="font-size:0.65rem">Batch: ${location.batch_number}</div>
                                <div style="font-size:0.7rem; font-weight:bold;">${location.quantity}/${location.max_depth}</div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: ${fillPercentage}%; background: rgba(255,255,255,0.3);"></div>
                                </div>
                            </div>
                        `;
                        tooltip = `Depth positions: 1-${location.quantity} occupied`;
                    } else if (location.is_reserved) {
                        cellClass = 'reserved-cell';
                        content = `
                            <div class="cell-content">
                                <strong>🔒 RESERVED</strong>
                                <div style="font-size:0.65rem">${location.reserved_for || 'Unknown'}</div>
                                <div>0/50</div>
                            </div>
                        `;
                        tooltip = 'This location is reserved';
                    } else {
                        cellClass = 'empty-cell';
                        content = `
                            <div class="cell-content">
                                <strong>📦 EMPTY</strong>
                                <div>0/50</div>
                            </div>
                        `;
                        tooltip = 'Available for storage';
                    }
                } else {
                    content = `
                        <div class="cell-content">
                            <strong>---</strong>
                            <div>N/A</div>
                        </div>
                    `;
                }
                
                html += `<td class="location-cell ${cellClass}" style="background: ${bgColor}; color: ${bgColor !== 'rgba(255,255,255,0.05)' ? 'white' : '#ccc'}" data-location="${locationCode}">
                            ${content}
                            <div class="depth-tooltip">${tooltip}</div>
                        </td>`;
            }
            
            html += `</tr>`;
        }
        
        html += `
                </tbody>
            </table>
        `;
        
        document.getElementById('matrixContent').innerHTML = html;
        
        // Add click handlers to cells
        document.querySelectorAll('.location-cell').forEach(cell => {
            cell.addEventListener('click', function() {
                const locationCode = this.dataset.location;
                const location = data[locationCode];
                if (location && location.product_name) {
                    alert(`📍 Location: ${locationCode}\n\nProduct: ${location.product_name}\nSKU: ${location.sku}\nBatch: ${location.batch_number}\nQuantity: ${location.quantity}/${location.max_depth}\nFill Rate: ${(location.quantity / location.max_depth * 100).toFixed(1)}%`);
                } else if (location && location.is_reserved) {
                    alert(`📍 Location: ${locationCode}\n\n🔒 RESERVED\n${location.reserved_for || 'Unknown reservation'}\nSpace: 0/50 available`);
                } else {
                    alert(`📍 Location: ${locationCode}\n\n📦 EMPTY\n50 spaces available for storage`);
                }
            });
        });
    }
    
    function fetchMatrixData() {
        return axios.get('{{ route("warehouse.matrix.data") }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    }
    
    function updateMatrix() {
        const matrixContent = document.getElementById('matrixContent');
        matrixContent.classList.add('updating');
        
        fetchMatrixData()
            .then(response => {
                currentData = response.data;
                renderMatrix(currentData);
                calculateStats(currentData);
                updateLastUpdateTime();
            })
            .catch(error => {
                console.error('Error fetching matrix data:', error);
                matrixContent.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: #f44336;">
                        <strong>❌ Error loading matrix data</strong>
                        <p style="margin-top: 0.5rem;">${error.message || 'Please check your connection'}</p>
                        <button onclick="location.reload()" style="margin-top: 1rem; padding: 0.5rem 1rem; background: #4caf50; border: none; color: white; border-radius: 8px; cursor: pointer;">Retry</button>
                    </div>
                `;
            })
            .finally(() => {
                matrixContent.classList.remove('updating');
            });
    }
    
    function startAutoRefresh() {
        if (autoRefreshTimer) {
            clearInterval(autoRefreshTimer);
        }
        
        const interval = parseInt(document.getElementById('refreshInterval').value) * 1000;
        autoRefreshTimer = setInterval(() => {
            updateMatrix();
        }, interval);
    }
    
    // Event listeners
    document.getElementById('manualRefresh').addEventListener('click', () => {
        updateMatrix();
        startAutoRefresh(); // Reset timer
    });
    
    document.getElementById('refreshInterval').addEventListener('change', () => {
        startAutoRefresh();
    });
    
    // Initial load
    updateMatrix();
    startAutoRefresh();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (autoRefreshTimer) {
            clearInterval(autoRefreshTimer);
        }
    });
</script>
@endpush
@endsection