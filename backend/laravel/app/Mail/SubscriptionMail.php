<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $title,
        protected string $body,
        protected ?string $pdfContent = null,
        protected ?string $pdfFilename = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription',
            with: [
                'title' => $this->title,
                'body' => $this->body,
            ],
        );
    }

    public function attachments(): array
    {
        if (!$this->pdfContent) {
            return [];
        }

        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->pdfFilename ?? 'subscription.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
