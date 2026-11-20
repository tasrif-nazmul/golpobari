<?php

return [

    // Comma-separated proxy IPs/CIDRs that sit in front of the app (Cloudflare, a load balancer). Empty = none.
    'trusted_proxies' => env('TRUSTED_PROXIES', ''),

    'admin_path' => trim(env('ADMIN_PATH', 'admin'), '/') ?: 'admin',

    'admin_idle_timeout' => (int) env('ADMIN_IDLE_TIMEOUT', 120),

    /*
     | Slugs a customer page can never use. Admins can add more from Settings.
     | Every first URL segment the app routes is also blocked automatically.
     */
    'reserved_slugs' => [
        'admin', 'login', 'logout', 'register', 'dashboard', 'api', 'assets', 'uploads',
        'storage', 'pricing', 'templates', 'template', 'categories', 'category', 'faq', 'contact',
        'about', 'privacy', 'terms', 'refund-policy', 'track-order', 'order', 'orders',
        'media', 'build', 'sitemap', 'sitemap.xml', 'robots.txt', 'favicon.ico', 'up',
        'golpobari', 'support', 'help', 'blog', 'www', 'mail', 'account', 'p', 'page', 'pages',
    ],

    'images' => [
        'max_dimension' => 1920,
        'thumb_dimension' => 480,
        'quality' => 82,
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    // Draft uploads that never became an order are removed after this many hours.
    'draft_upload_ttl_hours' => 48,

    'expiring_soon_days' => 7,

    /*
     | Defaults for admin-editable settings. Values saved in the settings table win.
     */
    'settings' => [
        'general' => [
            'site_name' => 'GolpoBari',
            'site_name_bn' => 'গল্পবাড়ি',
            'tagline' => 'আপনার গল্পের নিজের একটা ঠিকানা।',
            'tagline_en' => 'Your story deserves its own place.',
            'logo_path' => null,
            'favicon_path' => null,
            'contact_phone' => '',
            'contact_email' => '',
            'whatsapp_number' => '',
            'facebook_url' => '',
            'messenger_url' => '',
            'instagram_url' => '',
            'youtube_url' => '',
            'tiktok_url' => '',
            'office_hours' => 'প্রতিদিন সকাল ১০টা – রাত ১০টা',
            'branding_text' => 'Created with GolpoBari ❤️',
            'branding_enabled' => '1',
        ],
        'payment' => [
            'bkash_number' => '',
            'bkash_account_type' => 'Personal',
            'payment_instructions' => "bKash অ্যাপ থেকে Send Money করুন।\nReference-এ আপনার নাম লিখতে পারেন।",
        ],
        'seo' => [
            'seo_title' => 'GolpoBari — আপনার গল্পের নিজের একটা ঠিকানা',
            'seo_description' => 'জন্মদিন, ভালোবাসা, বার্ষিকী, পোর্টফোলিও কিংবা ব্যবসার জন্য নিজের একটা সুন্দর ওয়েবসাইট। কোনো coding ছাড়াই, golpobari.com/আপনার-নাম ঠিকানায়।',
            'og_image_path' => null,
            'google_site_verification' => '',
        ],
        'upload' => [
            'max_file_mb' => '8',
            'max_files_per_order' => '30',
            'allowed_types' => 'jpg,jpeg,png,webp',
        ],
        'order' => [
            'delivery_time' => '২৪–৪৮ ঘণ্টা',
            'order_instructions' => 'Order ID-টি সংরক্ষণ করুন। পেমেন্ট যাচাইয়ের পর আমরা আপনার পেজ তৈরি শুরু করব এবং প্রস্তুত হলে আপনার ফোনে যোগাযোগ করব।',
            'orders_enabled' => '1',
            'extra_reserved_slugs' => '',
        ],
        'home' => [
            'hero_title' => 'আপনার গল্পের নিজের একটা ঠিকানা।',
            'hero_subtitle' => 'প্রিয় মানুষ, জন্মদিন, ভালোবাসার গল্প, নিজের পরিচয় কিংবা ছোট্ট ব্যবসা। সব কিছুর জন্য একটা সুন্দর ওয়েবসাইট, নিজের নামের লিংকে। Coding লাগবে না, বাকিটা আমরা করে দিই।',
            'hero_cta_primary' => 'আপনার গল্প শুরু করুন',
            'hero_cta_secondary' => 'টেমপ্লেট দেখুন',
            'about_title' => 'গল্পবাড়ি কী?',
            'about_text' => 'আপনার জীবনের বিশেষ মানুষ, স্মৃতি, গল্প, ব্যবসা কিংবা নিজের পরিচয়ের জন্য তৈরি করুন একটি সুন্দর ওয়েবসাইট, কোনো coding ছাড়াই। আপনি শুধু গল্পটা বলুন আর ছবিগুলো দিন। ডিজাইন, লেখা সাজানো আর অনলাইনে তোলার কাজটা আমাদের।',
            'final_cta_title' => 'একটা গল্পেরও তো নিজের একটা ঠিকানা থাকা উচিত।',
            'final_cta_button' => 'আজই শুরু করুন',
            'show_templates' => '1',
            'show_pricing' => '1',
            'show_testimonials' => '1',
            'show_faq' => '1',
            'slug_examples' => 'korim,our-story,happy-birthday-mitu,ammu,rafi-cafe,nodi-portfolio',
        ],
    ],
];
