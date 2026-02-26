<?php

namespace Gometap\LaraiTracker\Models;

use Illuminate\Database\Eloquent\Model;

class LaraiSetting extends Model
{
    protected $table = 'larai_settings';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value)
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
