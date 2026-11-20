<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case PaymentPending = 'payment_pending';
    case PaymentVerified = 'payment_verified';
    case InReview = 'in_review';
    case InProduction = 'in_production';
    case Ready = 'ready';
    case Delivered = 'delivered';
    case Published = 'published';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::PaymentPending => 'Payment Pending',
            self::PaymentVerified => 'Payment Verified',
            self::InReview => 'In Review',
            self::InProduction => 'In Production',
            self::Ready => 'Ready',
            self::Delivered => 'Delivered',
            self::Published => 'Published',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function labelBn(): string
    {
        return match ($this) {
            self::Draft => 'খসড়া',
            self::Submitted => 'অর্ডার জমা হয়েছে',
            self::PaymentPending => 'পেমেন্ট যাচাই বাকি',
            self::PaymentVerified => 'পেমেন্ট যাচাই হয়েছে',
            self::InReview => 'তথ্য দেখা হচ্ছে',
            self::InProduction => 'পেজ তৈরি হচ্ছে',
            self::Ready => 'পেজ প্রস্তুত',
            self::Delivered => 'পৌঁছে দেওয়া হয়েছে',
            self::Published => 'পেজ লাইভ',
            self::Expired => 'মেয়াদ শেষ',
            self::Cancelled => 'বাতিল',
            self::Refunded => 'টাকা ফেরত দেওয়া হয়েছে',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft, self::Submitted => 'slate',
            self::PaymentPending => 'amber',
            self::PaymentVerified, self::InReview => 'sky',
            self::InProduction => 'violet',
            self::Ready, self::Delivered => 'teal',
            self::Published => 'emerald',
            self::Expired => 'orange',
            self::Cancelled, self::Refunded => 'rose',
        };
    }

    /** Milestones shown to customers on the tracking page. */
    public static function trackingSteps(): array
    {
        return [
            ['key' => 'received', 'label' => 'অর্ডার পেয়েছি', 'statuses' => ['submitted', 'payment_pending']],
            ['key' => 'verified', 'label' => 'পেমেন্ট যাচাই হয়েছে', 'statuses' => ['payment_verified', 'in_review']],
            ['key' => 'production', 'label' => 'পেজ তৈরি হচ্ছে', 'statuses' => ['in_production']],
            ['key' => 'ready', 'label' => 'প্রস্তুত', 'statuses' => ['ready', 'delivered']],
            ['key' => 'published', 'label' => 'লাইভ!', 'statuses' => ['published', 'expired']],
        ];
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
