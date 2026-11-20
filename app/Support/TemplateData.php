<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

/**
 * What a page template sees: escaped text helpers, repeaters, media and theme.
 * Templates must output through {{ }} or these helpers; never {!! raw input !!}.
 */
class TemplateData
{
    public function __construct(
        public readonly array $content,
        public readonly array $media,      // field_key => [ ['url','thumb','caption','width','height'], ... ]
        public readonly Collection $fields, // field schemas
        public readonly array $theme,
        public readonly array $knownKeys = [],
        public readonly array $placeholders = [], // keys showing sample content in a draft preview
    ) {}

    /**
     * Marks an element as showing a field, so the order form can point at it in the live preview.
     * Usage: <h1 {{ $d->f('your_name') }}>. Several keys: $d->f('a b').
     */
    public function f(string $keys): HtmlString
    {
        $ph = collect(explode(' ', $keys))->contains(fn ($k) => in_array($k, $this->placeholders, true));

        return new HtmlString('data-f="'.e($keys).'"'.($ph ? ' data-ph' : ''));
    }

    public function text(string $key, string $default = ''): string
    {
        $v = $this->content[$key] ?? '';

        return is_string($v) && $v !== '' ? $v : $default;
    }

    public function has(string $key): bool
    {
        $v = $this->content[$key] ?? null;
        if (is_array($v)) {
            return count($v) > 0;
        }

        return filled($v) || ! empty($this->media[$key]);
    }

    /** Escaped text with paragraphs and line breaks preserved. */
    public function para(string $key, string $pClass = ''): HtmlString
    {
        $text = $this->text($key);
        if ($text === '') {
            return new HtmlString('');
        }
        $class = $pClass ? ' class="'.e($pClass).'"' : '';
        $html = collect(preg_split("/\n{2,}/", $text))
            ->map(fn ($p) => '<p'.$class.'>'.nl2br(e(trim($p)), false).'</p>')
            ->implode('');

        return new HtmlString($html);
    }

    public static function lines(?string $text): HtmlString
    {
        return new HtmlString(nl2br(e((string) $text), false));
    }

    public function items(string $key): array
    {
        $v = $this->content[$key] ?? [];

        return is_array($v) ? array_values(array_filter($v, 'is_array')) : [];
    }

    public function list(string $key, string $sep = ','): array
    {
        return array_values(array_filter(array_map('trim', explode($sep, $this->text($key)))));
    }

    public function image(string $key): ?array
    {
        return $this->media[$key][0] ?? null;
    }

    public function gallery(string $key): array
    {
        return $this->media[$key] ?? [];
    }

    public function date(string $key): ?Carbon
    {
        $v = $this->text($key);
        try {
            return $v ? Carbon::parse($v) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function link(?string $url): ?string
    {
        $url = trim((string) $url);

        return preg_match('#^https?://#i', $url) ? $url : null;
    }

    public function tel(?string $phone): string
    {
        return preg_replace('/[^0-9+]/', '', strtr((string) $phone, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']));
    }

    public function whatsapp(?string $phone): ?string
    {
        $n = ltrim($this->tel($phone), '+');
        if ($n === '') {
            return null;
        }
        if (str_starts_with($n, '01')) {
            $n = '88'.$n;
        }

        return 'https://wa.me/'.$n;
    }

    public function theme(string $key, string $default = ''): string
    {
        return (string) ($this->theme[$key] ?? $default);
    }

    /** Fields an admin added that this layout doesn't render itself. */
    public function extras(): array
    {
        $out = [];
        foreach ($this->fields as $f) {
            if ($f['type'] === 'music' || in_array($f['key'], $this->knownKeys, true) || in_array($f['key'], $this->placeholders, true) || ! $this->has($f['key'])) {
                continue;
            }
            $out[] = ['field' => $f, 'value' => $this->content[$f['key']] ?? null, 'media' => $this->media[$f['key']] ?? []];
        }

        return $out;
    }

    public function cssVars(): HtmlString
    {
        $hex = fn ($v, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $v) ? $v : $d;
        $vars = [
            '--t-primary' => $hex($this->theme['primary'] ?? null, '#111111'),
            '--t-accent' => $hex($this->theme['accent'] ?? null, '#E2553F'),
            '--t-bg' => $hex($this->theme['background'] ?? null, '#FFFFFF'),
            '--t-text' => $hex($this->theme['text'] ?? null, '#111111'),
            '--t-font-heading' => TemplateRegistry::fontCss($this->theme['font_heading'] ?? null, 'fraunces'),
            '--t-font-body' => TemplateRegistry::fontCss($this->theme['font_body'] ?? null, 'hind-siliguri'),
            '--t-font-accent' => TemplateRegistry::fontCss($this->theme['font_accent'] ?? ($this->theme['font_heading'] ?? null), 'caveat'),
        ];

        return new HtmlString(collect($vars)->map(fn ($v, $k) => $k.':'.$v)->implode(';'));
    }
}
