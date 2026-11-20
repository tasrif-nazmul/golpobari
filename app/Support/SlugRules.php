<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class SlugRules
{
    public const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public static function normalize(?string $slug): string
    {
        return Str::slug(Str::ascii((string) $slug));
    }

    public static function reserved(): array
    {
        $extra = array_filter(array_map(
            fn ($s) => strtolower(trim($s)),
            explode(',', (string) app(Settings::class)->get('extra_reserved_slugs', ''))
        ));

        // Every first path segment the app itself routes is also off-limits.
        $routed = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($r) => strtolower(explode('/', ltrim($r->uri(), '/'))[0] ?? ''))
            ->filter(fn ($s) => $s !== '' && ! str_contains($s, '{'))
            ->all();

        return array_values(array_unique(array_merge(
            config('golpobari.reserved_slugs'),
            [config('golpobari.admin_path')],
            $extra,
            $routed
        )));
    }

    public static function isReserved(string $slug): bool
    {
        return in_array(strtolower($slug), self::reserved(), true);
    }

    /** Returns an error message, or null when the slug can be used. */
    public static function problem(string $slug, ?int $ignorePageId = null): ?string
    {
        if ($slug === '' || strlen($slug) < 2 || strlen($slug) > 60) {
            return 'Slug must be 2–60 characters.';
        }
        if (! preg_match(self::PATTERN, $slug)) {
            return 'Use only lowercase English letters, numbers and hyphens.';
        }
        if (self::isReserved($slug)) {
            return 'This address is reserved by the system.';
        }
        $taken = Page::where('slug', $slug)->when($ignorePageId, fn ($q) => $q->where('id', '!=', $ignorePageId))->exists();

        return $taken ? 'This address is already taken.' : null;
    }

    public static function suggest(string $base, ?int $ignorePageId = null): string
    {
        $base = self::normalize($base) ?: 'golpo';
        $base = Str::limit($base, 50, '');
        $slug = $base;
        $i = 2;
        while (self::problem($slug, $ignorePageId) !== null) {
            $slug = $base.'-'.$i++;
            if ($i > 200) {
                $slug = $base.'-'.Str::lower(Str::random(5));
                break;
            }
        }

        return $slug;
    }
}
