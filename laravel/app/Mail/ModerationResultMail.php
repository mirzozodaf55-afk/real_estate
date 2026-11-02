<?php

namespace App\Mail;

use App\Models\Announ;
use App\Models\AnnounNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ModerationResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Announ $announ,
        public string $kind,
        public ?string $reason,
        public ?string $recipientName,
    ) {
    }

    public function envelope(): Envelope
    {
        $title = $this->announ->title ?: $this->announ->type_object;
        return new Envelope(subject: match ($this->kind) {
            AnnounNotification::KIND_APPROVED => 'Объявление опубликовано: ',
            AnnounNotification::KIND_NEEDS_CHANGES => 'Объявление нужно доработать: ',
            default => 'Объявление не прошло проверку: ',
        } . $title);
    }

    public function content(): Content
    {
        $frontend = rtrim(config('app.frontend_url'), '/');
        return new Content(view: 'emails.moderation-result', with: [
            'approved' => $this->approved(),
            'needsChanges' => $this->kind === AnnounNotification::KIND_NEEDS_CHANGES,
            'title' => $this->announ->title ?: $this->announ->type_object,
            'price' => number_format((int) $this->announ->price, 0, '', ' ') . ' сомони',
            'reason' => $this->reason,
            // не одобрено — сразу в редактирование этого объявления
            'openUrl' => $this->approved() ? $frontend . '/personal_area/my_announ' : $frontend . '/update-announ?id=' . $this->announ->id,
        ]);
    }

    private function approved(): bool
    {
        return $this->kind === AnnounNotification::KIND_APPROVED;
    }
}
