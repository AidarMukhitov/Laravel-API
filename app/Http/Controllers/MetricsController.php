<?php

namespace App\Http\Controllers;

use App\Services\MetricsService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Metrics",
 *     description="Contact form statistics"
 * )
 */
class MetricsController extends Controller
{
    public function __construct(protected MetricsService $metricsService)
    {}

    /**
     * Get contact form submission statistics.
     *
     * @OA\Get(
     *     path="/api/metrics",
     *     tags={"Metrics"},
     *     summary="Get contact statistics",
     *     description="Returns contact form submission counts for today, this week, and total.",
     *     operationId="getMetrics",
     *     @OA\Response(
     *         response=200,
     *         description="Metrics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="today", type="integer", example=5, description="Submissions today"),
     *             @OA\Property(property="week", type="integer", example=12, description="Submissions this week (since Monday)"),
     *             @OA\Property(property="total", type="integer", example=42, description="Total submissions ever")
     *         )
     *     )
     * )
     */
    public function __invoke(): JsonResponse
    {
        $metrics = $this->metricsService->get();

        return response()->json([
            'today' => (int) $metrics['today'],
            'week' => (int) $metrics['week'],
            'total' => (int) $metrics['total'],
        ]);
    }
}
