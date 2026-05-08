<?php
namespace App\Mail;
use App\Models\Reunion;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
class ConvocationReunion extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(
        public Reunion $reunion,
        public User $destinataire
    ) {}
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[DCUS] Convocation — ' . $this->reunion->titre
                   . ' du ' . $this->reunion->date->format('d/m/Y'),
        );
    }
    public function content(): Content
    {
        return new Content(
            view: 'emails.convocation',
        );
    }

}
