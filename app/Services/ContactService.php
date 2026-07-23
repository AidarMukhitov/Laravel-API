<?php

namespace App\Services;

use App\Mail\ContactNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * ContactService handles the full lifecycle of a contact form submission:
 * validation pass-through, AI sentiment analysis, email notifications,
 * and metrics tracking.
 */
class ContactService
{
    public function __construct(
        protected AIService $aiService,
        protected MetricsService $metricsService
    ) {}

    /**
     * Process a contact form submission.
     *
     * @param  array<string, mixed>  $data  Validated form data
     * @return array<string, mixed> Response payload including sentiment
     */
    public function submit(array $data): array
    {
        // Step 1: AI sentiment analysis (graceful fallback built-in)
        $sentiment = $this->aiService->analyzeSentiment(
            $data['comment'],
            $data['name']
        );

        // Step 2: Send email notifications
        $this->sendEmails($data);

        // Step 3: Update metrics
        $this->metricsService->increment();

        return [
            'message' => 'Your message has been sent successfully.',
            'sentiment' => $sentiment,
        ];
    }

    /**
     * Send email to site owner and a copy to the user.
     */
    protected function sendEmails(array $data): void
    {
        $ownerEmail = config('app.owner_email', env('MAIL_OWNER'));

        if (empty($ownerEmail)) {
            Log::warning('MAIL_OWNER is not configured. Owner email not sent.', [
                'user_email' => $data['email'],
            ]);
        } else {
            // Notify site owner
            Mail::to($ownerEmail)
                ->send(new ContactNotification($data, 'owner'));

            Log::info('Owner notification email queued', [
                'owner' => $ownerEmail,
            ]);
        }

        // Send confirmation copy to the user
        Mail::to($data['email'])
            ->send(new ContactNotification($data, 'user'));

        Log::info('User confirmation email queued', [
            'user_email' => $data['email'],
        ]);
    }
}
