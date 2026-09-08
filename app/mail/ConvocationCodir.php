<?php

namespace App\Mail;

use App\Models\Codir;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConvocationCodir extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Codir $codir,
        public string $destinataire_nom,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[DCUS] Convocation CODIR — '.$this->codir->objet
                   .' — '.$this->codir->date->locale('fr')->translatedFormat('d F Y'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.codir-convocation');
    }
}
