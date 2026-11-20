<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['slug' => '7-days', 'name' => '7 Days', 'name_bn' => '৭ দিন', 'duration_value' => 7, 'duration_unit' => 'day', 'price' => 149,
                'description' => 'জন্মদিন বা একটা সারপ্রাইজের জন্য যথেষ্ট।',
                'features' => ['যেকোনো টেমপ্লেট', 'সর্বোচ্চ ১০টি ছবি', 'নিজের নামের লিংক', '২৪–৪৮ ঘণ্টায় ডেলিভারি'],
                'flags' => ['max_photos' => 10, 'show_branding' => true, 'premium_templates' => false]],
            ['slug' => '3-months', 'name' => '3 Months', 'name_bn' => '৩ মাস', 'duration_value' => 3, 'duration_unit' => 'month', 'price' => 399, 'is_featured' => true,
                'description' => 'সবচেয়ে জনপ্রিয়। গল্পটা থাকুক কিছুদিন।',
                'features' => ['যেকোনো টেমপ্লেট', 'সর্বোচ্চ ২০টি ছবি', 'নিজের নামের লিংক', 'একবার ফ্রি সংশোধন'],
                'flags' => ['max_photos' => 20, 'show_branding' => true, 'premium_templates' => true]],
            ['slug' => '6-months', 'name' => '6 Months', 'name_bn' => '৬ মাস', 'duration_value' => 6, 'duration_unit' => 'month', 'price' => 599,
                'description' => 'পোর্টফোলিও বা ছোট ব্যবসার জন্য ভালো।',
                'features' => ['যেকোনো টেমপ্লেট', 'সর্বোচ্চ ৩০টি ছবি', 'নিজের নামের লিংক', 'দুইবার ফ্রি সংশোধন'],
                'flags' => ['max_photos' => 30, 'show_branding' => true, 'premium_templates' => true]],
            ['slug' => '1-year', 'name' => '1 Year', 'name_bn' => '১ বছর', 'duration_value' => 1, 'duration_unit' => 'year', 'price' => 999, 'compare_price' => 1198,
                'description' => 'পুরো এক বছর, GolpoBari ব্র্যান্ডিং ছাড়া।',
                'features' => ['যেকোনো টেমপ্লেট', 'সর্বোচ্চ ৪০টি ছবি', 'নিজের নামের লিংক', 'GolpoBari লেখা থাকবে না', 'যেকোনো সময় সংশোধন'],
                'flags' => ['max_photos' => 40, 'show_branding' => false, 'premium_templates' => true]],
        ];

        foreach ($packages as $i => $p) {
            Package::firstOrCreate(['slug' => $p['slug']], $p + ['sort_order' => $i + 1, 'is_active' => true]);
        }

        Coupon::firstOrCreate(['code' => 'WELCOME20'], [
            'description' => 'Launch offer: 20% off',
            'type' => 'percent', 'value' => 20, 'max_discount' => 200,
            'usage_limit' => 100, 'per_phone_limit' => 1,
            'starts_at' => now()->startOfDay(), 'expires_at' => now()->addMonths(3)->endOfDay(),
            'is_active' => true,
        ]);
    }
}
