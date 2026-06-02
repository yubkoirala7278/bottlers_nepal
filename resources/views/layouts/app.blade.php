{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bottlers Nepal WMS - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('app.css') }}">
    @stack('styles')
</head>
<body>
    @auth
    <nav class="navbar">
        <div class="navbar-container">
            <a href="{{ route('dashboard') }}" class="navbar-brand">
                🍾 Bottlers Nepal WMS
            </a>
            <div class="navbar-menu">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('products.index') }}" class="nav-link">Products</a>
                    <a href="{{ route('batches.index') }}" class="nav-link">Batches</a>
                    <a href="{{ route('warehouse.matrix.full') }}" class="nav-link">Full Screen Matrix</a>
                     <a href="{{ route('admin.reservations') }}" class="nav-link">🔒 Reservations</a>
                    <a href="{{ route('admin.bulk.inbound') }}" class="nav-link">Bulk Inbound</a>
    <a href="{{ route('admin.bulk.outbound') }}" class="nav-link">Bulk Outbound</a>
                    <a href="#" class="nav-link">Users</a>
                @elseif(auth()->user()->isInboundStaff())
                    <a href="{{ route('inbound.index') }}" class="nav-link">Inbound</a>
                    <a href="{{ route('warehouse.matrix') }}" class="nav-link">Warehouse Map</a>
                @elseif(auth()->user()->isOutboundStaff())
                    <a href="{{ route('outbound.index') }}" class="nav-link">Outbound</a>
                    <a href="{{ route('warehouse.matrix') }}" class="nav-link">Warehouse Map</a>
                @endif
                <div class="user-info">
                    <span class="user-role">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
                    <span>{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth
    
    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
    @stack('scripts')
</body>
</html>