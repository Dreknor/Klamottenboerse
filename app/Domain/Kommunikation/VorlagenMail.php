<?php

namespace App\Domain\Kommunikation;

use App\Support\Einstellungen;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

/** Mail, deren Text aus einer Vorlage stammt (Markdown erlaubt). */
class VorlagenMail extends Mailable
{
    public function __construct(public string $betreff, public string $inhalt) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->betreff);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.vorlage',
            with: [
                'html' => Str::markdown($this->inhalt, ['html_input' => 'escape', 'allow_unsafe_links' => false]),
                'verein' => Einstellungen::get('vereinsname'),
            ],
        );
    }
}
