<?php

namespace App\Models;

use App\Support\TemplateRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Template extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'layout', 'tagline', 'description', 'preview_path',
        'theme', 'design', 'max_photos', 'is_premium', 'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'theme' => 'array',
            'design' => 'array',
            'is_premium' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(TemplateField::class)->orderBy('sort_order')->orderBy('id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->whereHas('category', fn ($c) => $c->where('is_active', true))
            ->orderBy('sort_order')->orderBy('id');
    }

    public function previewUrl(): ?string
    {
        if (! $this->preview_path) {
            return null;
        }

        return str_starts_with($this->preview_path, 'images/')
            ? asset($this->preview_path)
            : Storage::disk('public')->url($this->preview_path);
    }

    /** Theme = layout defaults, overridden by this template's saved theme. */
    public function resolvedTheme(array $overrides = []): array
    {
        $defaults = TemplateRegistry::layout($this->layout)['theme'] ?? [];

        return array_merge($defaults, array_filter($this->theme ?? [], fn ($v) => $v !== null && $v !== ''), array_filter($overrides, fn ($v) => $v !== null && $v !== ''));
    }

    public function viewName(): string
    {
        return TemplateRegistry::view($this->layout);
    }

    public function sampleContent(): array
    {
        return TemplateRegistry::layout($this->layout)['sample'] ?? [];
    }
}
