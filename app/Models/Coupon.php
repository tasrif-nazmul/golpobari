<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value', 'max_discount', 'min_order_amount',
        'usage_limit', 'per_phone_limit', 'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper(trim((string) $value));
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Disabled';
        }
        if ($this->isExpired()) {
            return 'Expired';
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'Scheduled';
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 'Used up';
        }

        return 'Active';
    }

    public function discountLabel(): string
    {
        return $this->type === 'percent'
            ? rtrim(rtrim((string) $this->value, '0'), '.').'%'
            : '৳'.number_format((float) $this->value);
    }
}
