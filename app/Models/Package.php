<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name', 'name_bn', 'slug', 'duration_value', 'duration_unit', 'price', 'compare_price',
        'description', 'features', 'flags', 'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'features' => 'array',
            'flags' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('price');
    }

    public function addDurationTo(CarbonInterface $from): CarbonInterface
    {
        $from = $from->copy();

        return match ($this->duration_unit) {
            'day' => $from->addDays($this->duration_value),
            'year' => $from->addYearsNoOverflow($this->duration_value),
            default => $from->addMonthsNoOverflow($this->duration_value),
        };
    }

    public function durationLabel(): string
    {
        $units = ['day' => 'দিন', 'month' => 'মাস', 'year' => 'বছর'];

        return \App\Support\Bn::digits($this->duration_value).' '.($units[$this->duration_unit] ?? $this->duration_unit);
    }

    public function flag(string $key, mixed $default = null): mixed
    {
        return data_get($this->flags, $key, $default);
    }

    public function maxPhotos(): ?int
    {
        $v = $this->flag('max_photos');

        return $v ? (int) $v : null;
    }

    public function showsBranding(): bool
    {
        return (bool) $this->flag('show_branding', true);
    }
}
