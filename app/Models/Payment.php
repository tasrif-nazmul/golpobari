<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'method', 'amount', 'expected_amount', 'receiver_number', 'sender_number',
        'transaction_id', 'status', 'verified_at', 'verified_by', 'rejection_reason',
        'refunded_at', 'refund_note',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'verified_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }

    public function amountMismatch(): bool
    {
        return abs((float) $this->amount - (float) $this->expected_amount) >= 0.01;
    }

    /** Other payments that used the same transaction ID (possible re-use). */
    public function duplicates()
    {
        return static::where('transaction_id', $this->transaction_id)->where('id', '!=', $this->id);
    }
}
