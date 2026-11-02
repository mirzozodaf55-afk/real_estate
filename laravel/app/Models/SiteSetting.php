<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return static::find($key)?->value ?? $default;
    }

    public static function put(string $key, $value): void
    {
        $value === null
            ? static::where('key', $key)->delete()
            : static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }
}
