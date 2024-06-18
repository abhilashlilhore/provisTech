<?php 
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $ipAddress = $request->ip();
        $sessionKey = 'active_session_' . $ipAddress;

        if (Cache::has($sessionKey)) {
            // Provide a response indicating an active session exists
            return redirect()->route('login')->withErrors([
                'message' => '<p style="color:red;">Active session already exists for this IP.</p>',
                'continue' => true // Optional flag to allow continuation
            ]);
        }

        // Mark this session as active
        Cache::put($sessionKey, true, now()->addMinutes(30)); // Set session duration

        return $next($request);
    }

    public function terminate($request, $response)
    {
        $ipAddress = $request->ip();
        $sessionKey = 'active_session_' . $ipAddress;

        if ($response->status() === Response::HTTP_OK) {
            // Clear the session key on successful response
            Cache::forget($sessionKey);
        }
    }
}
