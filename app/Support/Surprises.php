<?php

namespace App\Support;

/**
 * The extra "surprise" sections that personal pages get on top of their own layout:
 * a quiz, a memory jar, open-when letters, love coupons and a hold-to-reveal finale.
 * Each one shows only when the customer filled it in. Rendered by partials/surprises.
 */
class Surprises
{
    /** layout => [tone, sections in page order] */
    public const LAYOUTS = [
        'romantic-story' => ['love', ['quiz', 'memory_jar', 'open_when', 'coupons', 'secret_message']],
        'love-letter' => ['love', ['memory_jar', 'open_when', 'coupons', 'secret_message']],
        'proposal' => ['love', ['quiz', 'memory_jar']],
        'our-journey' => ['love', ['quiz', 'memory_jar', 'open_when', 'coupons', 'secret_message']],
        'memory-timeline' => ['love', ['quiz', 'memory_jar', 'coupons', 'secret_message']],
        'gift-surprise' => ['love', ['quiz', 'memory_jar', 'coupons', 'secret_message']],
        'reasons-cards' => ['family', ['quiz', 'open_when', 'coupons', 'secret_message']],
        'birthday-celebration' => ['birthday', ['quiz', 'memory_jar', 'coupons', 'secret_message']],
        'travel-diary' => ['friends', ['quiz', 'memory_jar', 'secret_message']],
        'photo-memories' => ['friends', ['quiz', 'memory_jar', 'secret_message']],
        'festival-greeting' => ['festival', ['memory_jar', 'secret_message']],
        'tribute' => ['tribute', ['memory_jar']],
    ];

    public static function keys(?string $layout): array
    {
        return self::LAYOUTS[$layout][1] ?? [];
    }

    public static function tone(?string $layout): string
    {
        return self::LAYOUTS[$layout][0] ?? 'love';
    }

    /** Field definitions (same shape as TemplateRegistry::f) for a layout's surprises. */
    public static function fields(string $layout, callable $f): array
    {
        $jar = self::tone($layout) === 'festival' ? 'শুভকামনার বয়াম (একটা করে লাইন)' : 'স্মৃতির বয়াম (ছোট ছোট স্মৃতি বা কথা)';
        $defs = [
            'quiz' => $f('quiz', 'মজার কুইজ: আমাকে কতটা চেনো?', 'repeater', [
                'help' => 'প্রশ্ন, সঠিক উত্তর আর দুটো ভুল উত্তর লিখুন। পেজে উত্তরগুলো এলোমেলো হয়ে আসবে, শেষে স্কোর দেখাবে।',
                'options' => ['max' => 8, 'fields' => [
                    ['key' => 'q', 'label' => 'প্রশ্ন', 'type' => 'text'], ['key' => 'a', 'label' => 'সঠিক উত্তর', 'type' => 'text'],
                    ['key' => 'b', 'label' => 'ভুল উত্তর ১', 'type' => 'text'], ['key' => 'c', 'label' => 'ভুল উত্তর ২', 'type' => 'text'],
                ]],
            ]),
            'memory_jar' => $f('memory_jar', $jar, 'repeater', [
                'help' => 'বয়ামে ট্যাপ করলে একটা করে চিরকুট বেরিয়ে আসবে।',
                'options' => ['max' => 20, 'fields' => [['key' => 'text', 'label' => 'চিরকুট', 'type' => 'text']]],
            ]),
            'open_when' => $f('open_when', '“যখন… তখন খুলো” চিঠি', 'repeater', [
                'help' => 'যেমন: “মন খারাপ হলে”। খামে ট্যাপ করলে ভেতরের কথাটা দেখাবে।',
                'options' => ['max' => 8, 'fields' => [['key' => 'when', 'label' => 'কখন খুলবে', 'type' => 'text'], ['key' => 'message', 'label' => 'ভেতরের কথা', 'type' => 'textarea']]],
            ]),
            'coupons' => $f('coupons', 'উপহার কুপন', 'repeater', [
                'help' => 'যেমন: “এক প্লেট ফুচকা ফ্রি”। কুপনে ট্যাপ করলে “ব্যবহার হয়েছে” সিল পড়বে।',
                'options' => ['max' => 6, 'fields' => [['key' => 'title', 'label' => 'কুপন', 'type' => 'text'], ['key' => 'note', 'label' => 'ছোট নোট', 'type' => 'text']]],
            ]),
            'secret_message' => $f('secret_message', 'শেষ চমক (চেপে ধরে রাখলে খুলবে)', 'textarea', [
                'max_length' => 1000, 'help' => 'পেজের একদম শেষে লুকানো থাকবে। বোতামটা চেপে ধরে রাখলে খুলবে।',
            ]),
        ];

        return array_values(array_intersect_key($defs, array_flip(self::keys($layout))));
    }

    /** Sample content for the preview, in the layout's tone. */
    public static function sample(string $layout): array
    {
        $s = self::samples()[self::tone($layout)] ?? [];

        return array_intersect_key($s, array_flip(self::keys($layout)));
    }

    /** Headings for each section, by tone. */
    public static function copy(?string $layout): array
    {
        $tone = self::tone($layout);
        $base = [
            'quiz' => ['ছোট্ট একটা কুইজ', 'দেখি তো আমাকে কতটা চেনো!'],
            'memory_jar' => ['স্মৃতির বয়াম', 'বয়ামে ট্যাপ করো, একটা করে চিরকুট বেরোবে'],
            'open_when' => ['যখন… তখন খুলো', 'খামের উপর ট্যাপ করো'],
            'coupons' => ['তোমার জন্য কুপন', 'যখন খুশি ব্যবহার করো, মেয়াদ নেই'],
            'secret_message' => ['শেষ চমক', 'হার্টটা চেপে ধরে রাখো…', '❤️'],
        ];

        return match ($tone) {
            'birthday' => array_merge($base, ['quiz' => ['জন্মদিনের কুইজ', 'দেখি তো আমাদের কতটা চিনিস!'], 'coupons' => ['জন্মদিনের কুপন', 'সারা বছর ব্যবহার করা যাবে'], 'secret_message' => ['শেষ উপহার', 'বাক্সটা চেপে ধরে রাখ…', '🎁']]),
            'friends' => array_merge($base, ['quiz' => ['ট্রিপের কুইজ', 'কে কতটা মনে রেখেছে?'], 'secret_message' => ['শেষ চমক', 'চেপে ধরে রাখো…', '🎁']]),
            'festival' => array_merge($base, ['memory_jar' => ['শুভকামনার বয়াম', 'ট্যাপ করো, একটা করে শুভকামনা'], 'secret_message' => ['ছোট্ট একটা চমক', 'চেপে ধরে রাখো…', '🎁']]),
            'tribute' => array_merge($base, ['memory_jar' => ['স্মৃতির বয়াম', 'ট্যাপ করুন, একটা করে স্মৃতি']]),
            'family' => array_merge($base, ['quiz' => ['ছোট্ট একটা কুইজ', 'দেখি তো আমাকে কতটা চেনো, আম্মু!']]),
            default => $base,
        };
    }

    private static function samples(): array
    {
        $q = fn ($q, $a, $b, $c) => compact('q', 'a', 'b', 'c');
        $t = fn (...$lines) => array_map(fn ($x) => ['text' => $x], $lines);

        return [
            'love' => [
                'quiz' => [
                    $q('আমার সবচেয়ে প্রিয় খাবার কোনটা?', 'কাচ্চি বিরিয়ানি', 'পান্তা-ইলিশ', 'ফুচকা'),
                    $q('আমি সবচেয়ে বেশি ভয় পাই কিসে?', 'তেলাপোকা', 'অন্ধকার', 'পরীক্ষা'),
                    $q('ছুটির দিনে আমার প্রিয় কাজ?', 'দুপুর পর্যন্ত ঘুম', 'রান্না করা', 'জিমে যাওয়া'),
                    $q('আমি তোমাকে কতটা ভালোবাসি?', 'মাপা যায় না', 'অনেক', 'একটু একটু'),
                ],
                'memory_jar' => $t(
                    'তোমার হাসির শব্দটা আমার সবচেয়ে প্রিয় গান।',
                    'বৃষ্টিতে রিকশার হুড ফেলে ভেজার দিনটা মনে আছে?',
                    'তুমি রাগ করলে নাকটা লাল হয়ে যায়, জানো?',
                    'তোমার পাঠানো প্রথম মেসেজটা এখনো সেভ করে রেখেছি।',
                    'আমার সব ভালো দিনের পেছনে কোথাও না কোথাও তুমি আছো।',
                    'একসাথে বুড়ো হব, চুক্তি পাকা।',
                ),
                'open_when' => [
                    ['when' => 'মন খারাপ হলে', 'message' => 'মনে রেখো, তুমি একা না। এখনই আমাকে একটা ফোন দাও, আর এক কাপ চা বানাও।'],
                    ['when' => 'আমার উপর রাগ হলে', 'message' => 'সরি! (যেটার জন্যই রাগ করো না কেন)। আজকের আইসক্রিম আমার পক্ষ থেকে।'],
                    ['when' => 'ঘুম না এলে', 'message' => 'চোখ বন্ধ করো আর ভাবো আমরা সমুদ্রের পাড়ে বসে আছি। ঢেউ গুনতে থাকো…'],
                    ['when' => 'আমাকে মিস করলে', 'message' => 'আমিও তোমাকে মিস করছি, ঠিক এই মুহূর্তে। গ্যালারিতে আমাদের প্রথম ছবিটা দেখো।'],
                ],
                'coupons' => [
                    ['title' => 'একটা লম্বা রিকশা ভ্রমণ', 'note' => 'বাদাম আর গল্প সহ'],
                    ['title' => 'ঝগড়ায় একবার তুমি জিতবে', 'note' => 'কোনো তর্ক ছাড়া'],
                    ['title' => 'তোমার পছন্দের সিনেমা', 'note' => 'আমি ঘুমাব না, প্রমিস'],
                    ['title' => 'মাঝরাতের ফুচকা ডেলিভারি', 'note' => 'যেকোনো রাতে, এক ফোনে'],
                ],
                'secret_message' => "তুমি আমার জীবনের সবচেয়ে সুন্দর সারপ্রাইজ।\nআজ, কাল আর সবসময়, শুধু তুমি। ❤",
            ],
            'family' => [
                'quiz' => [
                    $q('তোমার রান্নার মধ্যে আমার সবচেয়ে প্রিয় কোনটা?', 'খিচুড়ি আর ডিম ভাজা', 'পায়েস', 'ভাপা পিঠা'),
                    $q('ছোটবেলায় বকা খাওয়ার ভয়ে আমি কোথায় লুকাতাম?', 'খাটের নিচে', 'আলমারির পেছনে', 'ছাদে'),
                    $q('তোমার কোন কথাটা আমি দিনে সবচেয়ে বেশি শুনি?', '“খেয়েছিস?”', '“ঘুমাতে যা”', '“পড়তে বস”'),
                ],
                'open_when' => [
                    ['when' => 'আমাকে মিস করলে', 'message' => 'আমার ছোটবেলার অ্যালবামটা খুলো, আর আমাকে একটা ভিডিও কল দাও।'],
                    ['when' => 'খুব ক্লান্ত লাগলে', 'message' => 'আজ আর কোনো কাজ না। চা আমি বানাব, তুমি শুধু বসে থাকবে।'],
                    ['when' => 'আমার উপর রাগ হলে', 'message' => 'জানি দুষ্টুমি করি, কিন্তু তোমাকেই সবচেয়ে বেশি ভালোবাসি। মাফ করে দাও?'],
                ],
                'coupons' => [
                    ['title' => 'একদিনের সব রান্না আমার', 'note' => 'রান্নাঘর তোমার জন্য বন্ধ'],
                    ['title' => 'তোমার পছন্দের শাড়ি কেনা', 'note' => 'দোকান তুমি বাছবে'],
                    ['title' => 'এক বিকেল শুধু গল্প', 'note' => 'ফোন দূরে রেখে'],
                ],
                'secret_message' => "আম্মু, পৃথিবীর সব ভালো জিনিসের নাম যদি এক শব্দে বলতে হয়, আমি বলব “তুমি”।\nভালোবাসি। ❤",
            ],
            'birthday' => [
                'quiz' => [
                    $q('আমাদের গ্যাংয়ে সবচেয়ে বেশি লেট করে কে?', 'তুই, অবশ্যই', 'আমি', 'রিকশাওয়ালা মামা'),
                    $q('তোর প্রিয় আড্ডার জায়গা কোনটা?', 'টং দোকানের চা', 'ছাদ', 'লাইব্রেরি'),
                    $q('মনের দিক থেকে তোর বয়স আসলে কত?', '১৬, চিরকাল', '৬০', '৫'),
                ],
                'memory_jar' => $t(
                    'পরীক্ষার আগের রাতের গ্রুপ স্টাডি (মানে আড্ডা)।',
                    'রাস্তা হারিয়ে দুই ঘণ্টা হাঁটার সেই বিকেল।',
                    'তোর হাসি শুনে পাশের টেবিলের লোকও হেসে ফেলে।',
                    'জন্মদিনে মুখে কেক মাখানোর ঐতিহ্য চলবেই।',
                    'তুই না থাকলে গ্রুপ চ্যাট মরুভূমি।',
                ),
                'coupons' => [
                    ['title' => 'এক প্লেট ফুচকা ফ্রি', 'note' => 'ঝাল যত খুশি'],
                    ['title' => 'তোর পার্সোনাল ফটোগ্রাফার', 'note' => 'পুরো একদিন, ১০০টা ছবি'],
                    ['title' => 'একটা গোপন কথা রাখার গ্যারান্টি', 'note' => 'কাউকে বলব না'],
                ],
                'secret_message' => "সন্ধ্যা ৭টায় ছাদে চলে আয়।\nসবাই অপেক্ষা করছে, কেকটাও! 🎂🎉",
            ],
            'friends' => [
                'quiz' => [
                    $q('ট্রিপে সবচেয়ে বেশি কে ঘুমিয়েছে?', 'যে বলেছিল “আর পাঁচ মিনিট”', 'ড্রাইভার মামা', 'কেউ না'),
                    $q('প্রথম রাতে আমরা কী খেয়েছিলাম?', 'বাঁশের ভেতরে রান্না মুরগি', 'খিচুড়ি', 'শুধু ম্যাগি'),
                    $q('পরের ট্রিপ কোথায়?', 'যেখানে বাজেটে কুলায়', 'সেন্টমার্টিন', 'সিলেট'),
                ],
                'memory_jar' => $t(
                    'ভোর পাঁচটায় মেঘের উপর দাঁড়িয়ে চা।',
                    'জিপের ছাদে বসে গলা ছেড়ে গান।',
                    'ক্যামেরার চার্জ শেষ, তবু সবচেয়ে সুন্দর মুহূর্তটা মনে আছে।',
                    'রাতে আকাশে এত তারা আগে কখনো দেখিনি।',
                    'ফেরার বাসে সবাই চুপ, কেউ ফিরতে চায়নি।',
                ),
                'secret_message' => "পরের ট্রিপের টিকিট আমার পক্ষ থেকে! 🎒\nতারিখটা শুধু তোমরা ঠিক করো।",
            ],
            'festival' => [
                'memory_jar' => $t(
                    'নতুন দিনে নতুন আনন্দ আসুক।',
                    'সব মন খারাপ দূরে চলে যাক।',
                    'সবার ঘরে থাকুক শান্তি আর হাসি।',
                    'প্রিয় মানুষগুলো পাশে থাকুক সবসময়।',
                    'এ বছর তোমার সব স্বপ্ন সত্যি হোক।',
                ),
                'secret_message' => "তোমার জন্য একটা ছোট্ট উপহার পাঠিয়েছি।\nআজ দরজার দিকে একটু খেয়াল রেখো! 🎁",
            ],
            'tribute' => [
                'memory_jar' => $t(
                    'শুক্রবার সকালে আব্বুর হাতের গরম পরোটা।',
                    'নামাজ শেষে মাথায় হাত বুলিয়ে দোয়া করা।',
                    'পুরনো ছাত্ররা আজও রাস্তায় দেখলে সালাম দেয়।',
                    'রেডিওতে খবর, হাতে চায়ের কাপ, বারান্দায় ইজিচেয়ার।',
                    'পরীক্ষার রেজাল্টের দিন সবার আগে মিষ্টি আনতেন।',
                ),
            ],
        ];
    }
}
