<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Health",
 *     description="Health check endpoints"
 * )
 */
class HealthController extends Controller
{
    /**
     * Health check endpoint.
     *
     * @OA\Get(
     *     path="/api/health",
     *     tags={"Health"},
     *     summary="Health check",
     *     description="Returns the API health status and current timestamp.",
     *     operationId="healthCheck",
     *     @OA\Response(
     *         response=200,
     *         description="API is healthy",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(property="timestamp", type="string", format="date-time")
     *         )
     *     )
     * )
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
