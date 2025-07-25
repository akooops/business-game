<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SettingsService;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $gameRunning = SettingsService::isRunning();

        if($gameRunning && $user->email != 'ilyes24.azzi@gmail.com'){
            abort(403, 'The game is currently running. Please try again later.');
        }
        
        // Check if user has a company relationship
        if ($user->company) {
            return redirect()->route('company.dashboard.index')->with('error', 'Access denied. Admin account required.');
        }

        return $next($request);
    }
} 