<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Landing Page API",
 *     version="1.0.0",
 *     description="REST API for developer landing page with contact form, health check, metrics, and AI sentiment analysis.",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local development server"
 * )
 *
 * @OA\Response(
 *     response="ServerError",
 *     description="Internal server error",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="Internal server error.")
 *     )
 * )
 */
abstract class Controller
{
    //
}
