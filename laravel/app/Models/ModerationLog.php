<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['moderator_id', 'action', 'announ_id', 'target_user_id', 'reason'];

    public function moderator()
    {
        return $this->belongsTo(Owner::class, 'moderator_id');
    }
}
