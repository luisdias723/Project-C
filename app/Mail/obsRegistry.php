<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class obsRegistry extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $subject;
    public $obs;

    /**
     * Create a new message instance.
     */
    public function __construct($order, $subject, $obs)
    {
        $this->order = $order;
        $this->subject = $subject;
        $this->obs = $obs;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject . ' - Desfile da Mordomia',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.obsRegistry',
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

    public function build(): obsRegistry
    {
        return $this->view('emails.obsRegistry')
            ->with([
                'code' => $this->order,
                'obs' => $this->obs,
            ]);
    }
}
