<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * ContactNotification — email sent for contact form submissions.
 *
 * Two modes:
 *  - "owner": notification to the site owner with full details
 *  - "user": confirmation copy to the user who submitted the form
 */
class ContactNotification extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;
    public string $recipientType;

    /**
     * @param  array<string, mixed>  $data  Validated form data
     * @param  string  $recipientType  "owner" or "user"
     */
    public function __construct(array $data, string $recipientType = 'owner')
    {
        $this->data = $data;
        $this->recipientType = $recipientType;

        // Set subject and envelope based on recipient type
        if ($recipientType === 'owner') {
            $this->subject('New Contact Form Submission');
        } else {
            $this->subject('Thank you for contacting us');
        }
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->view('emails.contact')
                    ->with([
                        'data' => $this->data,
                        'type' => $this->recipientType,
                    ]);
    }
}
