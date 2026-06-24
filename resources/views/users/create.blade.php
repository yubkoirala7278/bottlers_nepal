{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add New User')

@section('content')
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
            font-size: 0.875rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input {
            width: auto;
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

        .password-hint {
            font-size: 0.7rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
    </style>

    <div class="form-container">
        <h1 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Add New User</h1>

        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
                <div class="password-hint">Minimum 8 characters</div>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="inbound_staff" {{ old('role') == 'inbound_staff' ? 'selected' : '' }}>Inbound Staff
                    </option>
                    <option value="outbound_staff" {{ old('role') == 'outbound_staff' ? 'selected' : '' }}>Outbound Staff
                    </option>
                    <option value="matrix_user" {{ old('role') == 'matrix_user' ? 'selected' : '' }}>Matrix User
                    </option>
                </select>
                @error('role')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active') ? 'checked' : 'checked' }}>
                    <label for="is_active" style="margin-bottom: 0;">Active Account</label>
                </div>
            </div>

            <button type="submit" class="btn-submit">Create User</button>

            <div style="margin-top: 1rem; text-align: center;">
                <a href="{{ route('users.index') }}" style="color: #6b7280; text-decoration: none;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
