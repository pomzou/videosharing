<?php

namespace App\Mail;

use App\Models\VideoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UrlExpirationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $videoFile;
    public $hoursRemaining;
    public $isExpired;

    /**
     * Create a new message instance.
     */
    public function __construct(VideoFile $videoFile, int $hoursRemaining = 0, bool $isExpired = false)
    {
        $this->videoFile = $videoFile;
        $this->hoursRemaining = $hoursRemaining;
        $this->isExpired = $isExpired;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isExpired
            ? 'Video Share Link Has Expired'
            : 'Video Share Link Expiring Soon';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.url-expiration',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
