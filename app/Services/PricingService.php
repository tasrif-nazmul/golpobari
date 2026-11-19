<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Package;

/**
 * The single source of truth for prices. The browser only ever displays what this returns.
 */
class PricingService
{
    /**
     * @return array{original: float, discount: float, final: float, coupon: ?Coupon, error: ?string}
     */
    public function quote(Package $package, ?string $couponCode = null, ?string $phone = null, ?Coupon $lockedCoupon = null): array
    {
        $original = round((float) $package->price, 2);
        $result = ['original' => $original, 'discount' => 0.0, 'final' => $original, 'coupon' => null, 'error' => null];

        $code = strtoupper(trim((string) $couponCode));
        if ($code === '') {
            return $result;
        }

        $coupon = $lockedCoupon ?? Coupon::where('code', $code)->first();
        $error = $this->couponProblem($coupon, $original, $phone);
        if ($error) {
            $result['error'] = $error;

            return $result;
        }

        $discount = $coupon->type === 'percent'
            ? round($original * ((float) $coupon->value) / 100, 2)
            : round((float) $coupon->value, 2);

        if ($coupon->max_discount !== null) {
            $discount = min($discount, (float) $coupon->max_discount);
        }
        $discount = max(0, min($discount, $original));

        $result['discount'] = $discount;
        $result['final'] = round($original - $discount, 2);
        $result['coupon'] = $coupon;

        return $result;
    }

    public function couponProblem(?Coupon $coupon, float $amount, ?string $phone): ?string
    {
        if (! $coupon || ! $coupon->is_active) {
            return 'এই কুপন কোডটি সঠিক নয়।';
        }
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return 'এই কুপনটি এখনো চালু হয়নি।';
        }
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return 'এই কুপনের মেয়াদ শেষ হয়ে গেছে।';
        }
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return 'এই কুপনটি আর ব্যবহার করা যাবে না।';
        }
        if ($coupon->min_order_amount !== null && $amount < (float) $coupon->min_order_amount) {
            return 'এই কুপনের জন্য সর্বনিম্ন অর্ডার ৳'.number_format((float) $coupon->min_order_amount).'।';
        }
        if ($coupon->per_phone_limit !== null && $phone) {
            $used = CouponUsage::where('coupon_id', $coupon->id)->where('phone', $phone)->count();
            if ($used >= $coupon->per_phone_limit) {
                return 'এই নম্বর থেকে কুপনটি ইতিমধ্যে ব্যবহার করা হয়েছে।';
            }
        }

        return null;
    }
}
