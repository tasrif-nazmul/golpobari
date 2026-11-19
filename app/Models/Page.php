<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Enums\PageVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'order_id', 'template_id', 'slug', 'title', 'status', 'visibility', 'access_code',
        'content', 'theme', 'seo', 'show_branding', 'published_at', 'expires_at',
    ];

    protected $hidden = ['access_code'];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'visibility' => PageVisibility::class,
            'content' => 'array',
            'theme' => 'array',
            'seo' => 'array',
            'show_branding' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'expiry_reminded_at' => 'datetime',
            'last_viewed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(PageMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(PageView::class);
    }

    public function url(): string
    {
        return url('/'.$this->slug);
    }

    /** Active and not past its expiry date (checked live, so expiry works even without cron). */
    public function isLive(): bool
    {
        return $this->status === PageStatus::Active && ! $this->hasExpired();
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function seo(string $key, ?string $default = null): ?string
    {
        $v = data_get($this->seo, $key);

        return filled($v) ? $v : $default;
    }

    public function scopeExpiringSoon(Builder $q, int $days): Builder
    {
        return $q->where('status', PageStatus::Active)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }
}
