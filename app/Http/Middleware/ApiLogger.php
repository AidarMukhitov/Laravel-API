<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * ApiLogger middleware — logs every API request to storage/logs/api.log.
 *
 * Format: [date] method url ip status response_time_ms
 */
class ApiLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $elapsed = round((microtime(true) - $startTime) * 1000, 2);

        $logEntry = sprintf(
            '[%s] %s %s %s %s %sms',
            now()->format('Y-m-d H:i:s'),
            $request->method(),
            $request->fullUrl(),
            $request->ip(),
            $response->getStatusCode(),
            $elapsed
        );

        Log::channel('api')->info($logEntry);

        return $response;
    }
}
