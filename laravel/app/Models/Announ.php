<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\PublicScope;

class Announ extends Model
{   
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'deal_type',
        'type_real_estate',
        'coord_x',
        'coord_y',
        'address',
        'floor',
        'floor_in_house',
        'year_of_construction',
        'quantity_room',
        'total_area',
        'living_area',
        'kitchen_area',
        'link_video',
        'furniture',
        'building_area',
        'state',
        'CeilingHeight',
        'allowKids',
        'allowAnimal',
        'layout',
        'numberSeats',
        'parking',
        'elevator',
        'Balcony',
        'number_of_rent',
        'land_area',
        'Rent',
        'Land_category',
        'house_area',
        'quantity_bedrooms',
        'condition_of_the_home',
        'electricity',
        'rental_period',
        'repair',
        'title',
        'description',
        'price',
        'rental_type',
        'house_type',
        'type_object',
        'owner_id',
        'type_home'
];
    // moderation_* нет в $fillable: статус меняет только модерация (или сброс на проверку при редактировании)

    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const NEEDS_CHANGES = 'needs_changes'; // модератор вернул на доработку с комментарием (moderation_reason)
    public const STATUSES = [self::PENDING, self::APPROVED, self::REJECTED, self::NEEDS_CHANGES];

    // координаты в БД — DECIMAL (строкой из MySQL), а карты на фронте ждут числа
    protected $casts = ['moderated_at' => 'datetime', 'sold_at' => 'datetime', 'photo_check' => 'array', 'photo_check_at' => 'datetime', 'coord_x' => 'float', 'coord_y' => 'float', 'promo_until' => 'datetime', 'ai_review' => 'array', 'ai_review_at' => 'datetime'];

    // promo / promo_until нет в $fillable: продвижение включает только админ (Admin\PromotionController)
    public const PROMO_PLANS = ['vip', 'top']; // по старшинству: VIP выше ТОП
    protected $appends = ['promo_active'];

    /** Действующее продвижение: 'vip' | 'top' | null (срок кончился — просто перестаёт действовать, без крона) */
    public function getPromoActiveAttribute(): ?string
    {
        return $this->promo && $this->promo_until && $this->promo_until->isFuture() ? $this->promo : null;
    }

    /** Сначала VIP, потом ТОП, потом остальные — для сортировки «по умолчанию» */
    public function scopePromotedFirst($query)
    {
        return $query->orderByRaw("CASE WHEN promo_until > ? AND promo = 'vip' THEN 0 WHEN promo_until > ? AND promo = 'top' THEN 1 ELSE 2 END", [now(), now()]);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new PublicScope);
    }

    /** Запрос без фильтра PublicScope (одобрено, не продано, владелец не заблокирован) — для владельца и модераторов */
    public static function anyStatus()
    {
        return static::withoutGlobalScope(PublicScope::class);
    }

public function LikedUsers(){
    return $this->belongsToMany(Owner::class,'likes','announ_id','user_id');
}

public function pictures(){
    return $this->hasMany(Pictures::class, 'announ_id')->whereNull('deleted_at')->orderBy('position');
}

public function owner(){
    return $this->belongsTo(Owner::class, 'owner_id');
}

public function likes(){
    return $this->hasMany(Likes::class, 'announ_id');
}
}