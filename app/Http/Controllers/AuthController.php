<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        
        $credentials = $request->only('email', 'password');
        $credentials['is_active'] = true;
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // If the user is a matrix_user, always send them to the warehouse matrix
            if ($user->role === 'matrix_user') {
                return redirect('/warehouse-matrix/full');
            }

            return redirect()->intended('/dashboard');
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records or account is inactive.',
        ])->onlyInput('email');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}