<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Одноразовый код: для входа или для подтверждения новой почты в профиле ($emailChange) */
class LoginCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code, public bool $emailChange = false)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: ($this->emailChange ? 'Код для смены почты: ' : 'Код для входа: ') . $this->code);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.login-code');
    }
}
