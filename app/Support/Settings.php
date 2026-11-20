<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Admin-editable key/value settings with defaults from config/golpobari.php.
 */
class Settings
{
    private const CACHE_KEY = 'golpobari.settings';

    private ?array $values = null;

    public function all(): array
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $defaults = [];
        foreach (config('golpobari.settings') as $group) {
            $defaults = array_merge($defaults, $group);
        }

        try {
            $stored = Cache::rememberForever(self::CACHE_KEY, fn () => Setting::query()->pluck('value', 'key')->all());
        } catch (Throwable) {
            $stored = []; // DB not ready (e.g. during install or on the 500 page)
        }

        return $this->values = array_merge($defaults, $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $v = $this->all()[$key] ?? null;

        return ($v === null || $v === '') ? $default : $v;
    }

    public function bool(string $key): bool
    {
        return (bool) (int) $this->get($key, '0');
    }

    public function set(array $pairs): void
    {
        $groups = [];
        foreach (config('golpobari.settings') as $group => $keys) {
            foreach (array_keys($keys) as $k) {
                $groups[$k] = $group;
            }
        }

        foreach ($pairs as $key => $value) {
            if (! isset($groups[$key])) {
                continue; // only known keys can be stored
            }
            Setting::updateOrCreate(['key' => $key], ['group' => $groups[$key], 'value' => $value]);
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->values = null;
    }

    public function fileUrl(string $key): ?string
    {
        $path = $this->get($key);

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /** Settings safe to use on any page, e.g. the bKash number. */
    public function bkashNumber(): string
    {
        return (string) $this->get('bkash_number', '');
    }
}
