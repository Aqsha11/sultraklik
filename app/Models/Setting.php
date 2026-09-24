<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = ['group', 'key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        [$group, $k] = self::parseKey($key);

        $value = static::where('group', $group)->where('key', $k)->value('value');

        return $value ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        [$group, $k] = self::parseKey($key);
        static::updateOrCreate(
            ['group' => $group, 'key' => $k],
            ['value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES) : (string) $value],
        );
    }

    /** @return array{0: string, 1: string} */
    private static function parseKey(string $key): array
    {
        $parts = explode('.', $key, 2);

        if (count($parts) === 2) {
            return [$parts[0], $parts[1]];
        }

        return ['general', $parts[0]];
    }
}