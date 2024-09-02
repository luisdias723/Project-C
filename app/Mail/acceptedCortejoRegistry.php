<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class acceptedCortejoRegistry extends Mailable
{
    use Queueable, SerializesModels;

    public $reg;
    public $order;
    public $pdfPath;
    public $qrcode;
    public $obs;
    public $participants;

    /**
     * Create a new message instance.
     */
    public function __construct($order, $pdfPath, $reg, $participants, $qrcode, $obs)
    {


    
        $this->order = $order;
        $this->pdfPath = public_path($pdfPath);
        $this->qrcode = public_path($qrcode);
        $this->participants = $participants;
        $this->reg = $reg;
        $this->obs = $obs;

      
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->order . ' Cortejo Etnográfico - Inscrição',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.acceptedCortejoRegistry',
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

    public function build(): acceptedCortejoRegistry
    {
        return $this->view('emails.acceptedCortejoRegistry')
            ->with([
                'orderName' => $this->order,
                'reg' => $this->reg,
                'qrcode' => $this->qrcode,
                'obs' => $this->obs,
                'participants' => $this->participants
            ])
            ->attach($this->pdfPath, [
                'as' => 'Inscricao.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
