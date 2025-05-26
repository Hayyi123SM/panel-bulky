<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WholesaleEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Form Wholesaler - Bulky.id',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.wholesale-email',
            with: $this->data ?? [],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
