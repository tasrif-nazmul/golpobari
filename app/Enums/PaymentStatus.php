<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Refunded = 'refunded';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function labelBn(): string
    {
        return match ($this) {
            self::Pending => 'যাচাই বাকি',
            self::Verified => 'যাচাই হয়েছে',
            self::Rejected => 'বাতিল',
            self::Refunded => 'ফেরত দেওয়া হয়েছে',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Verified => 'emerald',
            self::Rejected => 'rose',
            self::Refunded => 'slate',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
