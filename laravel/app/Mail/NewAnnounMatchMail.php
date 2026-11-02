<?php

namespace App\Mail;

use App\Models\Announ;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Письмо подписчику «Уведомить меня»: появилось объявление по его сохранённому поиску */
class NewAnnounMatchMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Announ $announ,
        public string $searchLabel,
        public ?string $recipientName,
        public ?string $imageUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        $title = $this->announ->title ?: $this->announ->type_object;
        return new Envelope(subject: 'Новое объявление по вашему поиску: ' . $title);
    }

    public function content(): Content
    {
        $frontend = rtrim(config('app.frontend_url'), '/');
        $a = $this->announ;

        $suffix = '';
        if ($a->deal_type === 'Аренда') {
            $suffix = $a->rental_type === 'Посуточно' ? ' / сутки' : ' / мес';
        }

        $facts = array_filter([
            $a->type_object,
            $a->quantity_room ? $a->quantity_room . '-комн.' : null,
            ($a->total_area ?: $a->house_area) ? ($a->total_area ?: $a->house_area) . ' м²' : null,
            $a->land_area ? $a->land_area . ' сот.' : null,
            ($a->floor && $a->floor_in_house) ? $a->floor . '/' . $a->floor_in_house . ' эт.' : null,
        ]);

        return new Content(view: 'emails.new-announ-match', with: [
            'title' => $a->title ?: $a->type_object,
            'price' => number_format((int) $a->price, 0, '', ' ') . ' сомони' . $suffix,
            'facts' => implode(' · ', $facts),
            'description' => $a->description ? mb_strimwidth($a->description, 0, 220, '…') : null,
            'openUrl' => $frontend . '/personal_area/notification',
            'manageUrl' => $frontend . '/personal_area/store_search',
        ]);
    }
}
