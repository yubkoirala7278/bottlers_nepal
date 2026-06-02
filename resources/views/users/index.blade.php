{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'User Management')

@section('content')
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: bold;
            color: #4f46e5;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1rem;
        }

        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .role-admin {
            background: #ef4444;
            color: white;
        }

        .role-inbound {
            background: #10b981;
            color: white;
        }

        .role-outbound {
            background: #f59e0b;
            color: white;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            padding: 0.375rem 0.75rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
            transition: all 0.2s;
        }

        .btn-edit {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .btn-edit:hover {
            background: #c7d2fe;
        }

        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-delete:hover {
            background: #fecaca;
        }

        .btn-toggle {
            background: #f3f4f6;
            color: #6b7280;
        }

        .btn-toggle:hover {
            background: #e5e7eb;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.3s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        input:checked+.toggle-slider {
            background-color: #10b981;
        }

        input:checked+.toggle-slider:before {
            transform: translateX(20px);
        }
    </style>

    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h1 style="font-size: 1.5rem; color: #1f2937;">User Management</h1>
            <a href="{{ route('users.create') }}" class="btn btn-primary">+ Add New User</a>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #10b981;">{{ $stats['active'] }}</div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #ef4444;">{{ $stats['inactive'] }}</div>
                <div class="stat-label">Inactive</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #ef4444;">{{ $stats['admins'] }}</div>
                <div class="stat-label">Admins</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #10b981;">{{ $stats['inbound'] }}</div>
                <div class="stat-label">Inbound Staff</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #f59e0b;">{{ $stats['outbound'] }}</div>
                <div class="stat-label">Outbound Staff</div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div class="user-avatar">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 600;">{{ $user->name }}</div>
                                        @if ($user->id === auth()->id())
                                            <span style="font-size: 0.7rem; color: #4f46e5;">(You)</span>
                                        @endif
                                    </div>
                                </div>
        </div>
        </td>
        <td>{{ $user->email }}</td>
        <td>
            <span
                class="role-badge role-{{ $user->role == 'admin' ? 'admin' : ($user->role == 'inbound_staff' ? 'inbound' : 'outbound') }}">
                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
            </span>
        </td>
        <td>
            <span class="status-badge status-{{ $user->is_active ? 'active' : 'inactive' }}">
                {{ $user->is_active ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td>{{ $user->created_at->format('Y-m-d') }}</td>
        <td>
            <div class="action-buttons">
                @if ($user->id !== auth()->id())
                    <label class="toggle-switch">
                        <input type="checkbox" class="toggle-status" data-id="{{ $user->id }}"
                            {{ $user->is_active ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                @endif
                <a href="{{ route('users.edit', $user) }}" class="btn-icon btn-edit">Edit</a>
                @if ($user->id !== auth()->id())
                    <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline;"
                        onsubmit="return confirm('Are you sure you want to delete this user?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon btn-delete">Delete</button>
                    </form>
                @endif
            </div>
        </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" style="text-align: center;">No users found.</td>
        </tr>
        @endforelse
        </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $users->links() }}
    </div>
    </div>

    @push('scripts')
<script>
    document.querySelectorAll('.toggle-status').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const userId = this.dataset.id;
            const isActive = this.checked;
            const originalState = !isActive;
            
            // Show loading state
            this.disabled = true;
            
            fetch(`/users/${userId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ 
                    is_active: isActive,
                    _method: 'POST' 
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success message
                    showToast(data.message, 'success');
                    
                    // Update the status badge in the same row
                    const row = toggle.closest('tr');
                    const statusBadge = row.querySelector('.status-badge');
                    if (statusBadge) {
                        if (data.is_active) {
                            statusBadge.className = 'status-badge status-active';
                            statusBadge.textContent = 'Active';
                        } else {
                            statusBadge.className = 'status-badge status-inactive';
                            statusBadge.textContent = 'Inactive';
                        }
                    }
                } else {
                    // Revert the toggle
                    this.checked = originalState;
                    showToast(data.message || 'Error updating user status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert the toggle
                this.checked = originalState;
                showToast(error.message || 'Error updating user status. Please try again.', 'error');
            })
            .finally(() => {
                // Re-enable the toggle
                this.disabled = false;
            });
        });
    });
    
    function showToast(message, type = 'success') {
        // Remove existing toast
        const existingToast = document.querySelector('.toast-message');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast-message toast-${type}`;
        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span>${type === 'success' ? '✓' : '✗'}</span>
                <span>${message}</span>
            </div>
        `;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 20px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            border-radius: 8px;
            font-size: 0.875rem;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        `;
        
        document.body.appendChild(toast);
        
        // Remove toast after 3 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Add animation styles
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
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
</script>
@endpush
@endsection
