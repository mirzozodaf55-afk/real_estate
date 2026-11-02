<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionRequest extends Model
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    protected $fillable = ['announ_id', 'owner_id', 'plan', 'days', 'price', 'payment_note'];
    protected $casts = ['decided_at' => 'datetime'];

    public function announ()
    {
        return $this->belongsTo(Announ::class)->withoutGlobalScopes();
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    /** Тарифы и реквизиты — из настроек сайта (меняет админ), со стартовыми значениями */
    public static function plans(): array
    {
        return [
            'top' => [
                'label' => 'ТОП',
                'price' => (int) SiteSetting::get('promo_top_price', 30),
                'days' => (int) SiteSetting::get('promo_top_days', 7),
                'text' => 'Выше обычных объявлений, значок «ТОП»',
            ],
            'vip' => [
                'label' => 'VIP',
                'price' => (int) SiteSetting::get('promo_vip_price', 60),
                'days' => (int) SiteSetting::get('promo_vip_days', 7),
                'text' => 'Самый верх списка, золотая рамка и значок «VIP»',
            ],
        ];
    }

    public static function requisites(): string
    {
        return (string) SiteSetting::get('promo_requisites', '');
    }
}
