<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnounNotification extends Model
{
    protected $fillable = ['user_id', 'announ_id', 'notify_me_id', 'kind', 'message', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    /** match — новое объявление по подписке; approved / rejected — решение модератора для владельца */
    public const KIND_MATCH = 'match';
    public const KIND_APPROVED = 'approved';
    public const KIND_REJECTED = 'rejected';
    public const KIND_NEEDS_CHANGES = 'needs_changes'; // вернули на доработку (message — комментарий модератора)
    /** для модераторов: ИИ считает, что фото не подходят к объявлению */
    public const KIND_PHOTO_ALERT = 'photo_alert';
    public const KIND_PROMOTED = 'promoted';           // админ включил ТОП/VIP (message: «VIP до 07.11.2026»)
    public const KIND_PROMO_REJECTED = 'promo_rejected'; // заявку на продвижение отклонили (message — причина)

    public function announ()
    {
        // владелец должен видеть уведомление и об отклонённом объявлении
        return $this->belongsTo(Announ::class, 'announ_id')->withoutGlobalScope(\App\Models\Scopes\PublicScope::class);
    }

    public function subscription()
    {
        return $this->belongsTo(NotifyMe::class, 'notify_me_id');
    }
}
