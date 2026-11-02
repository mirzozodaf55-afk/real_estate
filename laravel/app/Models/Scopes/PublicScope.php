<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Что видят посетители — во всех запросах сразу (как SoftDeletes):
 * одобрено модератором, не отмечено «Продано», владелец не заблокирован.
 * Код владельца и модератора снимает фильтр явно: Announ::anyStatus().
 */
class PublicScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // имена с таблицей — многие запросы делают join с pictures/likes
        $builder->where($model->qualifyColumn('moderation_status'), 'approved')
            ->whereNull($model->qualifyColumn('sold_at'))
            ->whereNotIn($model->qualifyColumn('owner_id'), function ($q) {
                $q->select('id')->from('owners')->whereNotNull('blocked_at');
            });
    }
}
