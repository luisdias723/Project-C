<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class acceptedRegistry extends Mailable
{
    use Queueable, SerializesModels;

    public $reg;
    public $order;
    public $pdfPath;
    public $qrcode;
    public $obs;

    /**
     * Create a new message instance.
     */
    public function __construct($order, $pdfPath, $reg, $qrcode, $obs)
    {
        $this->order = $order;
        $this->pdfPath = public_path($pdfPath);
        $this->qrcode = public_path($qrcode);
        $this->reg = $reg;
        $this->obs = $obs;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->order . ' Desfile da Mordomia - Inscrição',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.acceptedRegistry',
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

    public function build(): acceptedRegistry
    {
        return $this->view('emails.acceptedRegistry')
            ->with([
                'orderName' => $this->order,
                'reg' => $this->reg,
                'qrcode' => $this->qrcode,
                'obs' => $this->obs
            ])
            ->attach($this->pdfPath, [
                'as' => 'Inscricao.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
