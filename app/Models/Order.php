<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_name', 'customer_phone', 'customer_email', 'category_id',
        'template_id', 'package_id', 'coupon_id', 'snapshot', 'occasion', 'coupon_code',
        'original_price', 'discount_amount', 'manual_discount', 'final_price', 'status',
        'payment_status', 'content', 'desired_slug', 'customer_note', 'admin_notes',
        'draft_token', 'ip_address', 'submitted_at', 'delivered_at',
    ];

    protected $hidden = ['draft_token', 'ip_address'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'snapshot' => 'array',
            'content' => 'array',
            'original_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'manual_discount' => 'decimal:2',
            'final_price' => 'decimal:2',
            'submitted_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PageMedia::class)->orderBy('field_key')->orderBy('sort_order');
    }

    public function page(): HasOne
    {
        return $this->hasOne(Page::class);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        return $q->where(function ($w) use ($term) {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
            $w->where('order_number', 'like', $like)
                ->orWhere('customer_name', 'like', $like)
                ->orWhere('customer_phone', 'like', $like)
                ->orWhere('customer_email', 'like', $like)
                ->orWhereHas('payments', fn ($p) => $p->where('transaction_id', 'like', $like))
                ->orWhereHas('page', fn ($p) => $p->where('slug', 'like', $like));
        });
    }

    public function snap(string $key, mixed $default = null): mixed
    {
        return data_get($this->snapshot, $key, $default);
    }

    public function templateName(): string
    {
        return $this->template?->name ?? $this->snap('template', '—');
    }

    public function packageName(): string
    {
        return $this->package?->name ?? $this->snap('package', '—');
    }

    public function categoryName(): string
    {
        return $this->category?->name ?? $this->snap('category', '—');
    }

    public function totalDiscount(): float
    {
        return (float) $this->discount_amount + (float) $this->manual_discount;
    }
}
