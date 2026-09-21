<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(SettingFactory::class)]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['key', 'value', 'group_name'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = self::where('key', $key)->first();
        if (!$row)
            return $default;

        $decoded = json_decode($row->value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $row->value;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): self
    {
        $encoded = is_array($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE)
            : (string) $value;

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $encoded, 'group_name' => $group]
        );
    }
}