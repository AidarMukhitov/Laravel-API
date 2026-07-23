<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * RateLimit middleware — prevents spam by limiting POST /api/contact
 * to a configurable number of attempts per minute per IP.
 *
 * Uses the file cache driver (no Redis required).
 */
class RateLimit
{
    protected string $cachePrefix = 'rate_limit:contact:';

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = $this->cachePrefix . $ip;

        $maxAttempts = (int) env('RATE_LIMIT_ATTEMPTS', 3);
        $decaySeconds = (int) env('RATE_LIMIT_DECAY', 60);

        $attempts = (int) Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            Log::warning('Rate limit exceeded', [
                'ip' => $ip,
                'attempts' => $attempts,
                'max' => $maxAttempts,
            ]);

            return response()->json([
                'message' => 'Too many requests. Please try again later.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // Increment the counter with an expiry
        Cache::put($key, $attempts + 1, now()->addSeconds($decaySeconds));

        return $next($request);
    }
}
