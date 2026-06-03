{{-- resources/views/warehouse/matrix-full.blade.php --}}
{{-- Self-contained: no CDN, no external assets, no layout dependency --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bottlers Nepal WMS - Live Warehouse Matrix</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            height: 100%;
            width: 100%;
        }

        body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #1a1a2e;
            color: #fff;
        }

        .matrix-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 1rem;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Header */
        .matrix-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            flex-wrap: wrap;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .title h1 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .title p {
            font-size: 0.7rem;
            opacity: 0.6;
        }

        /* Stats */
        .stats {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .stat {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.4rem 0.85rem;
            border-radius: 8px;
            text-align: center;
            min-width: 70px;
        }

        .stat-value {
            font-size: 1.1rem;
            font-weight: bold;
        }

        .stat-label {
            font-size: 0.65rem;
            opacity: 0.65;
        }

        /* Controls */
        .refresh-control {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .auto-refresh {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
        }

        .auto-refresh input {
            width: 42px;
            padding: 0.25rem;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
            text-align: center;
            font-size: 0.75rem;
        }

        .btn {
            border: none;
            color: white;
            padding: 0.45rem 0.9rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: filter 0.2s, transform 0.2s;
        }

        .btn:hover {
            filter: brightness(1.2);
            transform: scale(1.04);
        }

        .btn-green {
            background: #4caf50;
        }

        .btn-purple {
            background: #667eea;
        }

        .last-update {
            font-size: 0.65rem;
            opacity: 0.6;
            white-space: nowrap;
        }

        /* Matrix scroll area */
        .matrix-scroll {
            flex: 1;
            min-height: 0;
            overflow: auto;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
        }

        #matrixContent {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        #matrixContent>* {
            flex: 1;
            min-height: 0;
        }

        .matrix-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            min-width: 820px;
        }

        .matrix-table th,
        .matrix-table td {
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 8px;
            text-align: center;
            vertical-align: middle;
        }

        /* Row header sticky left */
        .matrix-table tbody th {
            background: rgba(0, 0, 0, 0.4);
            font-weight: 600;
            font-size: 0.8rem;
            position: sticky;
            left: 0;
            z-index: 5;
            white-space: nowrap;
            min-width: 80px;
        }

        /* Column labels in tfoot sticky bottom */
        .matrix-table tfoot th {
            background: rgba(0, 0, 0, 0.5);
            font-weight: 700;
            font-size: 0.85rem;
            position: sticky;
            bottom: 0;
            z-index: 10;
            letter-spacing: 0.05em;
        }

        /* Hide thead - labels shown only in tfoot */
        .matrix-table thead {
            display: none;
        }

        /* Cells */
        .location-cell {
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            position: relative;
            min-width: 90px;
        }

        .location-cell:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.45);
            z-index: 20;
        }

        .cell-content {
            font-size: 0.72rem;
            line-height: 1.4;
        }

        .cell-content strong {
            display: block;
            font-size: 0.82rem;
            margin-bottom: 3px;
        }

        .empty-cell {
            background: rgba(255, 255, 255, 0.04);
        }

        .reserved-cell {
            background: rgba(255, 152, 0, 0.18) !important;
            border: 2px solid #ff9800 !important;
        }

        .progress-bar {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 5px;
            height: 4px;
        }

        .progress-fill {
            height: 100%;
            background: rgba(255, 255, 255, 0.35);
            transition: width 0.3s;
        }

        /* Tooltip */
        .depth-tooltip {
            position: absolute;
            bottom: calc(100% + 4px);
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.92);
            color: #fff;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 0.68rem;
            white-space: nowrap;
            pointer-events: none;
            z-index: 30;
            display: none;
        }

        .location-cell:hover .depth-tooltip {
            display: block;
        }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            flex-direction: column;
            gap: 1rem;
        }

        .loading-overlay.visible {
            display: flex;
        }

        /* Spinner */
        .spinner {
            width: 46px;
            height: 46px;
            border: 4px solid rgba(255, 255, 255, 0.15);
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.45;
            }
        }

        .updating {
            animation: pulse 0.9s ease-in-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .matrix-container {
                padding: 0.5rem;
            }

            .matrix-table th,
            .matrix-table td {
                padding: 5px 4px;
                font-size: 0.65rem;
            }

            .cell-content {
                font-size: 0.6rem;
            }

            .cell-content strong {
                font-size: 0.7rem;
            }

            .stats {
                gap: 0.4rem;
            }

            .stat {
                padding: 0.2rem 0.5rem;
            }

            .stat-value {
                font-size: 0.9rem;
            }

            .title h1 {
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body>

    <div class="matrix-container">

        <div class="matrix-header">
            <div class="title">
                <h1>🏭 Bottlers Nepal — Live Warehouse Matrix</h1>
                <p>Real-time inventory visualization | Depth-first storage mapping</p>
            </div>

            <div class="stats">
                <div class="stat">
                    <div class="stat-value" id="totalLocations">—</div>
                    <div class="stat-label">Total Locations</div>
                </div>
                <div class="stat">
                    <div class="stat-value" id="occupiedLocations">—</div>
                    <div class="stat-label">Occupied</div>
                </div>
                <div class="stat">
                    <div class="stat-value" id="totalItems">—</div>
                    <div class="stat-label">Total Items</div>
                </div>
                <div class="stat">
                    <div class="stat-value" id="fillRate">—</div>
                    <div class="stat-label">Fill Rate</div>
                </div>
            </div>

            <div class="refresh-control">
                <div class="auto-refresh">
                    <span>🔄 Auto</span>
                    <input type="number" id="refreshInterval" value="10" min="3" max="60"
                        step="1">
                    <span>sec</span>
                </div>
                <button class="btn btn-green" id="manualRefresh">⟳ Refresh Now</button>
                <button class="btn btn-purple" id="fullscreenBtn">⛶ Fullscreen</button>
                <div class="last-update" id="lastUpdate">Last update: —</div>
            </div>
        </div>

        <div class="matrix-scroll">
            <div id="matrixContent">
                <div style="text-align:center; padding:3rem;">
                    <div class="spinner" style="margin:0 auto;"></div>
                    <p style="margin-top:1rem; opacity:0.7;">Loading warehouse matrix…</p>
                </div>
            </div>
        </div>

    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <p style="opacity:0.8; font-size:0.85rem;">Refreshing…</p>
    </div>

    <script>
        const LEVELS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        const HEIGHTS = [6, 5, 4, 3, 2, 1];
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        let autoRefreshTimer = null;
        let currentData = null;

        function showOverlay() {
            document.getElementById('loadingOverlay').classList.add('visible');
        }

        function hideOverlay() {
            document.getElementById('loadingOverlay').classList.remove('visible');
        }

        function updateLastUpdateTime() {
            document.getElementById('lastUpdate').textContent =
                'Last update: ' + new Date().toLocaleTimeString();
        }

        function calculateStats(data) {
            let total = 0,
                occupied = 0,
                items = 0,
                capacity = 0;
            for (const loc of Object.values(data)) {
                total++;
                capacity += 50;
                if (loc && loc.quantity > 0) {
                    occupied++;
                    items += loc.quantity;
                }
            }
            const rate = capacity > 0 ? (items / capacity * 100).toFixed(1) : 0;
            document.getElementById('totalLocations').textContent = total;
            document.getElementById('occupiedLocations').textContent = occupied;
            document.getElementById('totalItems').textContent = items;
            document.getElementById('fillRate').textContent = rate + '%';
        }

        function renderMatrix(data) {
            let html = `<table class="matrix-table">
            <thead><tr>
                <th>Location</th>
                ${LEVELS.map(l => `<th>${l}</th>`).join('')}
            </tr></thead>
            <tbody>`;

            for (const height of HEIGHTS) {
                html += `<tr><th>Height ${height}</th>`;
                for (const level of LEVELS) {
                    const code = level + height;
                    const loc = data[code];
                    let cellClass = '',
                        bgColor = 'rgba(255,255,255,0.04)',
                        content = '',
                        tooltip = '';

                    if (loc) {
                        if (loc.product_name && loc.quantity > 0) {
                            bgColor = loc.color_code || '#4caf50';
                            const pct = ((loc.quantity / loc.max_depth) * 100).toFixed(0);
                            content = `<div class="cell-content">
                            <strong>${loc.product_name}</strong>
                            <div>${loc.sku}</div>
                            <div style="font-size:0.62rem">Batch: ${loc.batch_number}</div>
                            <div style="font-weight:700">${loc.quantity}/${loc.max_depth}</div>
                            <div class="progress-bar"><div class="progress-fill" style="width:${pct}%"></div></div>
                        </div>`;
                            tooltip = `Depth: 1–${loc.quantity} occupied`;
                        } else if (loc.is_reserved) {
                            cellClass = 'reserved-cell';
                            content = `<div class="cell-content">
                            <strong>🔒 RESERVED</strong>
                            <div style="font-size:0.62rem">${loc.reserved_for || 'Unknown'}</div>
                            <div>0/50</div>
                        </div>`;
                            tooltip = 'This location is reserved';
                        } else {
                            cellClass = 'empty-cell';
                            content = `<div class="cell-content"><strong>📦 EMPTY</strong><div>0/50</div></div>`;
                            tooltip = 'Available for storage';
                        }
                    } else {
                        content = `<div class="cell-content"><strong>—</strong><div>N/A</div></div>`;
                    }

                    const textColor = bgColor !== 'rgba(255,255,255,0.04)' ? 'white' : '#aaa';
                    html += `<td class="location-cell ${cellClass}"
                    style="background:${bgColor}; color:${textColor}"
                    data-location="${code}">
                    ${content}
                    <div class="depth-tooltip">${tooltip}</div>
                </td>`;
                }
                html += `</tr>`;
            }

            html += `</tbody>
            <tfoot><tr>
                <th>Location</th>
                ${LEVELS.map(l => `<th>${l}</th>`).join('')}
            </tr></tfoot>
        </table>`;

            document.getElementById('matrixContent').innerHTML = html;

            document.querySelectorAll('.location-cell').forEach(cell => {
                cell.addEventListener('click', function() {
                    const code = this.dataset.location;
                    const loc = data[code];
                    if (loc && loc.product_name && loc.quantity > 0) {
                        alert(
                            `📍 Location: ${code}\n\nProduct : ${loc.product_name}\nSKU     : ${loc.sku}\nBatch   : ${loc.batch_number}\nQty     : ${loc.quantity}/${loc.max_depth}\nFill    : ${(loc.quantity / loc.max_depth * 100).toFixed(1)}%`
                        );
                    } else if (loc && loc.is_reserved) {
                        alert(
                            `📍 Location: ${code}\n\n🔒 RESERVED\n${loc.reserved_for || 'Unknown reservation'}`
                        );
                    } else {
                        alert(`📍 Location: ${code}\n\n📦 EMPTY — 50 spaces available`);
                    }
                });
            });
        }

        // Native fetch — no axios or CDN required
        function fetchMatrixData() {
            return fetch('{{ route('warehouse.matrix.data') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF,
                }
            }).then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            });
        }

        function updateMatrix(isInitial = false) {
            const content = document.getElementById('matrixContent');
            isInitial ? showOverlay() : content.classList.add('updating');

            fetchMatrixData()
                .then(data => {
                    currentData = data;
                    renderMatrix(data);
                    calculateStats(data);
                    updateLastUpdateTime();
                })
                .catch(err => {
                    console.error('Matrix fetch error:', err);
                    content.innerHTML = `
                    <div style="text-align:center; padding:2rem; color:#f44336;">
                        <div style="font-size:2rem;">❌</div>
                        <strong>Error loading matrix data</strong>
                        <p style="margin-top:0.5rem; opacity:0.7;">${err.message || 'Check server connection'}</p>
                        <button onclick="updateMatrix(true)"
                            style="margin-top:1rem; padding:0.5rem 1.2rem; background:#4caf50;
                                   border:none; color:white; border-radius:8px; cursor:pointer; font-size:0.85rem;">
                            ⟳ Retry
                        </button>
                    </div>`;
                })
                .finally(() => {
                    hideOverlay();
                    content.classList.remove('updating');
                });
        }

        function startAutoRefresh() {
            clearInterval(autoRefreshTimer);
            const ms = Math.max(3, parseInt(document.getElementById('refreshInterval').value) || 10) * 1000;
            autoRefreshTimer = setInterval(() => updateMatrix(), ms);
        }

        // Fullscreen
        const fsBtn = document.getElementById('fullscreenBtn');

        function syncFullscreenLabel() {
            fsBtn.textContent = document.fullscreenElement ? '✕ Exit Fullscreen' : '⛶ Fullscreen';
        }

        fsBtn.addEventListener('click', () => {
            document.fullscreenElement ?
                document.exitFullscreen() :
                document.documentElement.requestFullscreen();
        });

        document.addEventListener('fullscreenchange', syncFullscreenLabel);

        document.addEventListener('keydown', e => {
            if (e.key === 'F11') {
                e.preventDefault();
                fsBtn.click();
            }
        });

        document.getElementById('manualRefresh').addEventListener('click', () => {
            updateMatrix();
            startAutoRefresh();
        });

        document.getElementById('refreshInterval').addEventListener('change', startAutoRefresh);

        window.addEventListener('beforeunload', () => clearInterval(autoRefreshTimer));

        // Boot
        updateMatrix(true);
        startAutoRefresh();
    </script>

</body>

</html>
