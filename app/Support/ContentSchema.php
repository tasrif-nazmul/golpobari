<?php

namespace App\Support;

use App\Models\TemplateField;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Turns a template's field definitions into validation rules and sanitises
 * submitted content. Media fields are handled separately (see ImageService).
 */
class ContentSchema
{
    /** @param Collection<int, TemplateField>|array $fields */
    public function __construct(private Collection|array $fields)
    {
        $this->fields = collect($fields)->map(fn ($f) => $f instanceof TemplateField ? $f->toSchema() : $f);
    }

    public function textFields(): Collection
    {
        return $this->fields->reject(fn ($f) => in_array($f['type'], TemplateField::MEDIA_TYPES, true));
    }

    public function mediaFields(): Collection
    {
        return $this->fields->filter(fn ($f) => in_array($f['type'], TemplateField::MEDIA_TYPES, true));
    }

    public function rules(string $prefix = 'content', bool $enforceRequired = true): array
    {
        $rules = [$prefix => ['nullable', 'array']];

        foreach ($this->textFields() as $f) {
            $key = $prefix.'.'.$f['key'];
            $req = ($enforceRequired && $f['required']) ? 'required' : 'nullable';

            if ($f['type'] === 'repeater') {
                $max = (int) data_get($f, 'options.max', 20);
                $rules[$key] = [$req, 'array', 'max:'.$max];
                if ($enforceRequired && $f['required']) {
                    $rules[$key][] = 'min:1';
                }
                foreach (data_get($f, 'options.fields', []) as $sub) {
                    $rules[$key.'.*.'.$sub['key']] = $this->scalarRules($sub, 'nullable');
                }

                continue;
            }

            $rules[$key] = $this->scalarRules($f, $req);
        }

        return $rules;
    }

    public function attributes(string $prefix = 'content'): array
    {
        $attrs = [];
        foreach ($this->fields as $f) {
            $attrs[$prefix.'.'.$f['key']] = $f['label'];
            foreach (data_get($f, 'options.fields', []) as $sub) {
                $attrs[$prefix.'.'.$f['key'].'.*.'.$sub['key']] = $f['label'].' → '.$sub['label'];
            }
        }

        return $attrs;
    }

    private function scalarRules(array $f, string $req): array
    {
        $len = $f['max_length'] ?? null;

        return match ($f['type']) {
            'textarea' => [$req, 'string', 'max:'.($len ?: 5000)],
            'date' => [$req, 'date_format:Y-m-d', 'after:1900-01-01', 'before:2100-01-01'],
            'email' => [$req, 'email:rfc', 'max:120'],
            'url' => [$req, 'url:http,https', 'max:300'],
            'phone' => [$req, 'string', 'max:25', 'regex:/^[0-9০-৯+\-\s()]{5,25}$/u'],
            'color' => [$req, 'regex:/^#[0-9a-fA-F]{6}$/'],
            'music' => ['nullable', 'string', Rule::in(array_merge(array_keys(TemplateRegistry::MUSIC), ['custom', 'none']))],
            'select' => array_values(array_filter([$req, 'string', 'max:80', ($c = data_get($f, 'options.choices', $f['choices'] ?? [])) ? Rule::in($c) : null])),
            default => [$req, 'string', 'max:'.($len ?: 255)],
        };
    }

    /** Keeps only declared keys, strips HTML and control characters, drops empty rows. */
    public function sanitize(?array $input): array
    {
        $input ??= [];
        $out = [];

        foreach ($this->textFields() as $f) {
            $value = $input[$f['key']] ?? null;

            if ($f['type'] === 'repeater') {
                $subKeys = collect(data_get($f, 'options.fields', []))->pluck('key')->all();
                $rows = [];
                foreach ((array) $value as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $clean = [];
                    foreach ($subKeys as $sk) {
                        $clean[$sk] = self::clean($row[$sk] ?? '');
                    }
                    if (implode('', $clean) !== '') {
                        $rows[] = $clean;
                    }
                }
                $out[$f['key']] = $rows;

                continue;
            }

            $out[$f['key']] = is_scalar($value) ? self::clean((string) $value) : '';
        }

        return $out;
    }

    public static function clean(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }
        $value = strip_tags((string) $value);
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/[^\P{C}\n\t]/u', '', $value) ?? '';

        return trim($value);
    }
}
