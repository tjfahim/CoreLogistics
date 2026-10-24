<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Key/value site settings (site title, logo, favicon, company details ...).
 *   Setting::get('site_title', 'Default')   Setting::set('site_title', 'My Site')
 *   Setting::assetUrl('logo', 'admin/images/logo.png')  -> uploaded file URL or the default asset
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'site_settings_all';

    /** Setting keys that hold an uploaded file path. */
    public const FILE_KEYS = ['logo', 'logo_mini', 'favicon'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** All settings as [key => value] (cached; empty array if the table does not exist yet). */
    public static function all_(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                if (!Schema::hasTable('settings')) {
                    return [];
                }
                return static::query()->pluck('value', 'key')->all();
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function get(string $key, $default = null)
    {
        $v = static::all_()[$key] ?? null;

        return ($v === null || $v === '') ? $default : $v;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /** URL of an uploaded file setting, or asset($fallback) when none was uploaded. */
    public static function assetUrl(string $key, string $fallback): string
    {
        $path = static::get($key);

        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path) . '?v=' . Storage::disk('public')->lastModified($path);
        }

        return asset($fallback);
    }
}
