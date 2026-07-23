<?php

/**
 * @OA\Info(
 *     title="Landing Page API",
 *     version="1.0.0",
 *     description="REST API for developer landing page with contact form, health check, metrics, and AI sentiment analysis.",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 */

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Contact",
 *     description="Contact form submission endpoints"
 * )
 */
class ContactController extends Controller
{
    public function __construct(protected ContactService $contactService)
    {}

    /**
     * Submit a contact form message.
     *
     * @OA\Post(
     *     path="/api/contact",
     *     tags={"Contact"},
     *     summary="Submit a contact form message",
     *     description="Sends email notifications and performs AI sentiment analysis on the comment.",
     *     operationId="submitContact",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone", "email", "comment"},
     *             @OA\Property(property="name", type="string", example="John Doe", description="Full name"),
     *             @OA\Property(property="phone", type="string", example="+1234567890", description="Phone number"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="comment", type="string", example="Great work!", description="Message or comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your message has been sent successfully."),
     *             @OA\Property(property="sentiment", type="string", example="positive", description="AI-detected sentiment of the comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The name field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests (rate limit exceeded)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Too many requests. Please try again later.")
     *         )
     *     )
     * )
     */
    public function store(ContactFormRequest $request): JsonResponse
    {
        $result = $this->contactService->submit($request->validated());

        return response()->json($result, 200);
    }
}
