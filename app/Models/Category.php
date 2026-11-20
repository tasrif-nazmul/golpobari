<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $fillable = [
        'name', 'name_bn', 'slug', 'emoji', 'tagline', 'description', 'occasions',
        'accent_color', 'icon_path', 'cover_path', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['occasions' => 'array', 'is_active' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    public function activeTemplates(): HasMany
    {
        return $this->templates()->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function displayName(): string
    {
        return $this->name_bn ?: $this->name;
    }

    public function iconUrl(): ?string
    {
        return $this->icon_path ? Storage::disk('public')->url($this->icon_path) : null;
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }
}
