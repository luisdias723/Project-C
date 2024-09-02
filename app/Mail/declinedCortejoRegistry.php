<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class declinedCortejoRegistry extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $obs;
    public $link;

    /**
     * Create a new message instance.
     */
    public function __construct($order, $obs, $link)
    {
        $this->order = $order;
        $this->obs = $obs;
        $this->link = $link;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cortejo Etnográficos - Observações à Inscrição',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.declinedCortejoRegistry',
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

    public function build(): declinedCortejoRegistry
    {
        return $this->view('emails.declinedCortejoRegistry')
            ->with([
                'order' => $this->order,
                'obs' => $this->obs,
                'link' => $this->link
            ]);
    }
}
