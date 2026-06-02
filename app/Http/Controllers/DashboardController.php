<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Different dashboard based on role
        if ($user->isAdmin()) {
            return view('admin.dashboard');
        } elseif ($user->isInboundStaff()) {
            return view('inbound.dashboard');
        } elseif ($user->isOutboundStaff()) {
            return view('outbound.dashboard');
        }

        return view('dashboard');
    }
}
