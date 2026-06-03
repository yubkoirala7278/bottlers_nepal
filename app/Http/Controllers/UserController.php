<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admins' => User::where('role', 'admin')->count(),
            'inbound' => User::where('role', 'inbound_staff')->count(),
            'outbound' => User::where('role', 'outbound_staff')->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,inbound_staff,outbound_staff',
            'is_active' => 'boolean',
        ], [
            'email.unique' => 'This email address is already registered.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        // Prevent editing own role to avoid lockout
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('warning', 'You cannot edit your own account from here. Use Profile Settings instead.');
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Prevent editing own role
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot modify your own account.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => 'required|in:admin,inbound_staff,outbound_staff',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Check if user has any activity before deleting
        // You can add additional checks here

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }


    public function toggleStatus(User $user)
    {
        try {
            // Prevent toggling own status
            if ($user->id === auth()->id()) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot change your own status.'
                    ], 403);
                }
                return redirect()->route('users.index')
                    ->with('error', 'You cannot change your own status.');
            }

            $user->is_active = !$user->is_active;
            $user->save();

            $status = $user->is_active ? 'activated' : 'deactivated';

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "User {$status} successfully.",
                    'is_active' => $user->is_active
                ]);
            }

            return redirect()->route('users.index')
                ->with('success', "User {$status} successfully.");
        } catch (\Exception $e) {
            \Log::error('Toggle status error: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('users.index')
                ->with('error', 'Failed to update user status.');
        }
    }

    public function profile()
    {
        $user = auth()->user();
        return view('users.profile', compact('user'));
    }

     public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        
        return back()->with('success', 'Profile updated successfully.');
    }

     // Update Password
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'new_password.different' => 'New password cannot be the same as your current password.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.confirmed' => 'New password confirmation does not match.',
            'current_password.required' => 'Please enter your current password.',
        ]);
        
        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        
        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        
        // Logout the user
        Auth::logout();
        
        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Redirect to login page with message
        return redirect()->route('login')->with('success', 'Password changed successfully! Please login with your new password.');
    }
}
