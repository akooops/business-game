<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ThrottleCompanyRequests
{
    /**
     * Handle an incoming request.
     * 
     * This middleware prevents race conditions by ensuring only one request
     * per company can be processed at a time. It will:
     * - Wait up to 0.5 seconds for the previous request to finish (legitimate rapid actions)
     * - Reject requests that can't acquire lock within 0.5s (spam/misclicks)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  int  $lockSeconds  How long to hold the lock (default: 5 seconds max per request)
     * @param  float  $waitSeconds  How long to wait for lock before rejecting (default: 0.5 seconds)
     */
    public function handle(Request $request, Closure $next, int $lockSeconds = 5, float $waitSeconds = 0.5): Response
    {
        $company = $request->company;
        
        // If no company in request, skip throttling
        if (!$company) {
            return $next($request);
        }
        
        $cacheKey = "company_request_lock:{$company->id}";
        
        // Try to acquire a lock for this company
        $lock = Cache::lock($cacheKey, $lockSeconds);
        
        // Try to get lock, waiting up to $waitSeconds
        // This allows legitimate rapid actions while blocking spam
        if ($lock->block($waitSeconds)) {
            try {
                // Lock acquired - process the request
                $response = $next($request);
                return $response;
            } finally {
                // Always release the lock when done
                $lock->release();
            }
        }
        
        // Couldn't get lock in time - reject as too many requests
        return response()->json([
            'error' => 'Another operation is in progress for this company.',
            'message' => 'Please wait a moment and try again.',
        ], 429); // 429 Too Many Requests
    }
}

