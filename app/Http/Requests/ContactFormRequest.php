<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *     schema="ContactFormRequest",
 *     type="object",
 *     required={"name", "phone", "email", "comment"},
 *     @OA\Property(property="name", type="string", example="John Doe", description="Full name"),
 *     @OA\Property(property="phone", type="string", example="+1234567890", description="Phone number"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="comment", type="string", example="Great work!", description="Message or comment")
 * )
 */
class ContactFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|max:255',
            'comment' => 'required|string|max:2000',
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name must not exceed 255 characters.',
            'phone.required' => 'The phone field is required.',
            'phone.string' => 'The phone must be a valid string.',
            'phone.max' => 'The phone must not exceed 30 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            'comment.required' => 'The comment field is required.',
            'comment.string' => 'The comment must be a valid string.',
            'comment.max' => 'The comment must not exceed 2000 characters.',
        ];
    }

    /**
     * Handle a failed validation attempt — return JSON 422.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
