<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return inertia('Auth/Login');
    }

    public function login(LoginRequest $request)
    {
        // Try to authenticate with the default guard
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if user has a company relationship
            if ($user->company) {
                return Inertia::location(route('company.dashboard.index'));
            }
            
            return Inertia::location(route('admin.dashboard.index'));
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    public function logout(Request $request)
    {        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('auth.login');
    }
}
