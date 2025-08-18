<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SettingsService;

class CheckCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        $gameStopped = SettingsService::isStopped();

        if($gameStopped){
            abort(403, 'The game is currently stopped. Please try again later.');
        }

        // Check if user has a company relationship
        if (!$user->company) {
            return redirect()->route('admin.dashboard.index')->with('error', 'Access denied. Company account required.');
        }

        // Add company to the request for easy access
        $request->merge(['company' => $user->company]);

        return $next($request);
    }
} 