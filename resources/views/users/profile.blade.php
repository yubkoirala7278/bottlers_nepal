{{-- resources/views/users/profile.blade.php --}}
@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <style>
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .profile-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .profile-info h2 {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
            color: #1f2937;
        }

        .profile-info p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 0.5rem;
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

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #374151;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
            font-size: 0.875rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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
        }

        .btn-submit:hover {
            background: #4338ca;
        }

        .info-text {
            font-size: 0.7rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .error {
            color: #dc2626;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 1.5rem 0;
        }
    </style>

    <div class="profile-container">
        <h1 style="margin-bottom: 1.5rem; font-size: 1.5rem;">My Profile</h1>

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-icon">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="profile-info">
                    <h2>{{ auth()->user()->name }}</h2>
                    <p>{{ auth()->user()->email }}</p>
                    <span
                        class="role-badge role-{{ auth()->user()->role == 'admin'
                            ? 'admin'
                            : (auth()->user()->role == 'inbound_staff'
                                ? 'inbound'
                                : 'outbound') }}">
                        {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                    </span>
                </div>
            </div>

            <!-- Update Profile Form -->
            <form method="POST" action="{{ route('users.profile.update') }}">
                @csrf
                @method('PUT')

                <h3 class="section-title">Personal Information</h3>

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}"
                        required>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                        required>
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-submit">Update Profile</button>
            </form>

            <div class="divider"></div>

            <!-- Change Password Form -->
            <form method="POST" action="{{ route('users.password.update') }}">
                @csrf
                @method('PUT')

                <h3 class="section-title">Change Password</h3>

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                    @error('current_password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <div class="info-text">Minimum 8 characters</div>
                    @error('new_password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
                </div>

                <button type="submit" class="btn-submit">Change Password</button>
            </form>
        </div>
    </div>
@endsection
