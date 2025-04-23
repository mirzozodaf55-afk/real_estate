<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'visitor_id',
        'ip-adress',
        'user-agent',
        'path',
        'referer',
        'fullUrl',
        'visited_at'
    ];
    }
