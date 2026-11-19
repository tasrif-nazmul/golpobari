<?php

namespace App\Support;

/**
 * Code-side catalogue of template layouts.
 *
 * A "layout" is a Blade view plus its default fields, theme and sample content.
 * Database templates point at a layout and may override name, theme and fields,
 * so admins can create many templates from the same layout without code changes.
 */
class TemplateRegistry
{
    public const FONTS = [
        'fraunces' => ['label' => 'Fraunces (serif)', 'css' => "'Fraunces', 'Noto Serif Bengali', serif", 'google' => 'Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700'],
        'playfair' => ['label' => 'Playfair Display', 'css' => "'Playfair Display', 'Noto Serif Bengali', serif", 'google' => 'Playfair+Display:ital,wght@0,500;0,700;1,500'],
        'dm-serif' => ['label' => 'DM Serif Display', 'css' => "'DM Serif Display', 'Tiro Bangla', serif", 'google' => 'DM+Serif+Display:ital@0;1'],
        'cormorant' => ['label' => 'Cormorant Garamond', 'css' => "'Cormorant Garamond', 'Noto Serif Bengali', serif", 'google' => 'Cormorant+Garamond:ital,wght@0,500;0,700;1,500'],
        'space-grotesk' => ['label' => 'Space Grotesk', 'css' => "'Space Grotesk', 'Hind Siliguri', sans-serif", 'google' => 'Space+Grotesk:wght@400;500;700'],
        'inter' => ['label' => 'Inter', 'css' => "'Inter', 'Hind Siliguri', sans-serif", 'google' => 'Inter:wght@400;500;600;700'],
        'manrope' => ['label' => 'Manrope', 'css' => "'Manrope', 'Hind Siliguri', sans-serif", 'google' => 'Manrope:wght@400;600;800'],
        'syne' => ['label' => 'Syne (display)', 'css' => "'Syne', 'Hind Siliguri', sans-serif", 'google' => 'Syne:wght@500;700;800'],
        'caveat' => ['label' => 'Caveat (handwriting)', 'css' => "'Caveat', 'Galada', cursive", 'google' => 'Caveat:wght@500;700'],
        'dancing' => ['label' => 'Dancing Script', 'css' => "'Dancing Script', 'Galada', cursive", 'google' => 'Dancing+Script:wght@500;700'],
        'hind-siliguri' => ['label' => 'Hind Siliguri (Bangla)', 'css' => "'Hind Siliguri', sans-serif", 'google' => 'Hind+Siliguri:wght@400;500;600;700'],
        'noto-serif-bengali' => ['label' => 'Noto Serif Bengali', 'css' => "'Noto Serif Bengali', serif", 'google' => 'Noto+Serif+Bengali:wght@400;600;700'],
        'tiro-bangla' => ['label' => 'Tiro Bangla', 'css' => "'Tiro Bangla', serif", 'google' => 'Tiro+Bangla:ital@0;1'],
        'galada' => ['label' => 'Galada (Bangla display)', 'css' => "'Galada', cursive", 'google' => 'Galada'],
        'baloo-da' => ['label' => 'Baloo Da 2 (Bangla rounded)', 'css' => "'Baloo Da 2', sans-serif", 'google' => 'Baloo+Da+2:wght@500;700'],
    ];

    /** Fonts always loaded alongside the template's choices so Bangla renders well. */
    private const BANGLA_FALLBACKS = ['Hind+Siliguri:wght@400;500;600;700', 'Noto+Serif+Bengali:wght@400;600;700'];

    public static function layouts(): array
    {
        return [
            'minimal-portfolio' => self::minimalPortfolio(),
            'modern-professional' => self::modernProfessional(),
            'creative-portfolio' => self::creativePortfolio(),
            'romantic-story' => self::romanticStory(),
            'memory-timeline' => self::memoryTimeline(),
            'love-letter' => self::loveLetter(),
            'birthday-celebration' => self::birthday(),
            'our-journey' => self::ourJourney(),
            'clean-business' => self::cleanBusiness(),
            'modern-service' => self::modernService(),
            'photo-memories' => self::photoMemories(),
            'event-invitation' => self::eventInvitation(),
        ];
    }

    public static function layout(?string $key): array
    {
        return self::layouts()[$key] ?? self::layouts()['minimal-portfolio'];
    }

    public static function options(): array
    {
        return collect(self::layouts())->mapWithKeys(fn ($l, $k) => [$k => $l['label']])->all();
    }

    public static function view(?string $key): string
    {
        $key = array_key_exists((string) $key, self::layouts()) ? $key : 'minimal-portfolio';

        return 'page-templates.'.$key;
    }

    public static function fontCss(?string $key, string $fallback = 'inter'): string
    {
        return (self::FONTS[$key] ?? self::FONTS[$fallback])['css'];
    }

    public static function googleFontsUrl(array $theme): string
    {
        $families = self::BANGLA_FALLBACKS;
        foreach (['font_heading', 'font_body', 'font_accent'] as $k) {
            if (! empty($theme[$k]) && isset(self::FONTS[$theme[$k]])) {
                $families[] = self::FONTS[$theme[$k]]['google'];
            }
        }

        return 'https://fonts.googleapis.com/css2?family='.implode('&family=', array_unique($families)).'&display=swap';
    }

    /* ------------------------------------------------------------------ */

    private static function f(string $key, string $label, string $type = 'text', array $extra = []): array
    {
        return array_merge([
            'key' => $key, 'label' => $label, 'type' => $type,
            'section' => in_array($type, ['image', 'gallery'], true) ? 'photos' : 'info',
            'placeholder' => null, 'help' => null, 'required' => false, 'max_length' => null, 'options' => [],
        ], $extra);
    }

    private static function socials(): array
    {
        return self::f('social_links', 'সোশ্যাল লিংক', 'repeater', ['options' => [
            'max' => 6,
            'fields' => [
                ['key' => 'platform', 'label' => 'প্ল্যাটফর্ম', 'type' => 'select', 'choices' => ['Facebook', 'Instagram', 'LinkedIn', 'GitHub', 'YouTube', 'TikTok', 'Behance', 'Dribbble', 'X / Twitter', 'Website']],
                ['key' => 'url', 'label' => 'লিংক', 'type' => 'url'],
            ],
        ]]);
    }

    private static function img(string $name): array
    {
        return ['url' => asset('images/samples/'.$name.'.svg'), 'thumb' => asset('images/samples/'.$name.'.svg'), 'caption' => null];
    }

    private static function minimalPortfolio(): array
    {
        return [
            'label' => 'Minimal Portfolio',
            'theme' => ['primary' => '#111111', 'accent' => '#C2410C', 'background' => '#FAFAF7', 'text' => '#1C1917', 'font_heading' => 'fraunces', 'font_body' => 'inter'],
            'fields' => [
                self::f('full_name', 'আপনার পুরো নাম', 'text', ['required' => true, 'max_length' => 80, 'placeholder' => 'e.g. Nodi Rahman']),
                self::f('headline', 'এক লাইনে পরিচয়', 'text', ['max_length' => 120, 'placeholder' => 'Product designer in Dhaka']),
                self::f('profile_photo', 'প্রোফাইল ছবি', 'image', ['help' => 'মুখ পরিষ্কার দেখা যায় এমন ছবি সবচেয়ে ভালো।']),
                self::f('about', 'আপনার সম্পর্কে', 'textarea', ['required' => true, 'max_length' => 2000]),
                self::f('skills', 'দক্ষতা', 'repeater', ['options' => ['max' => 20, 'fields' => [['key' => 'name', 'label' => 'দক্ষতা', 'type' => 'text']]]]),
                self::f('experience', 'অভিজ্ঞতা', 'repeater', ['options' => ['max' => 10, 'fields' => [
                    ['key' => 'role', 'label' => 'ভূমিকা', 'type' => 'text'], ['key' => 'org', 'label' => 'প্রতিষ্ঠান', 'type' => 'text'],
                    ['key' => 'period', 'label' => 'সময়কাল', 'type' => 'text'], ['key' => 'text', 'label' => 'কী করেছেন', 'type' => 'textarea'],
                ]]]),
                self::f('projects', 'প্রজেক্ট', 'repeater', ['options' => ['max' => 10, 'fields' => [
                    ['key' => 'title', 'label' => 'প্রজেক্টের নাম', 'type' => 'text'], ['key' => 'text', 'label' => 'ছোট বিবরণ', 'type' => 'textarea'], ['key' => 'link', 'label' => 'লিংক', 'type' => 'url'],
                ]]]),
                self::f('education', 'শিক্ষা', 'repeater', ['options' => ['max' => 6, 'fields' => [
                    ['key' => 'degree', 'label' => 'ডিগ্রি', 'type' => 'text'], ['key' => 'school', 'label' => 'শিক্ষাপ্রতিষ্ঠান', 'type' => 'text'], ['key' => 'period', 'label' => 'সাল', 'type' => 'text'],
                ]]]),
                self::f('email', 'ইমেইল', 'email'),
                self::f('phone', 'ফোন', 'phone'),
                self::f('location', 'লোকেশন', 'text', ['placeholder' => 'Dhaka, Bangladesh']),
                self::socials(),
            ],
            'sample' => [
                'full_name' => 'Nodi Rahman',
                'headline' => 'Product designer, Dhaka. I make complicated things feel simple.',
                'profile_photo' => self::img('portrait-a'),
                'about' => "আমি একজন product designer। গত ছয় বছর ধরে fintech আর edtech-এর জন্য এমন interface বানাই যা গ্রামের একজন দোকানদার থেকে শুরু করে শহরের একজন ছাত্র, সবাই সহজে ব্যবহার করতে পারে।\n\nকাজের বাইরে আমি ছবি আঁকি, পুরান ঢাকার অলিগলিতে হাঁটি আর ভালো চায়ের খোঁজে থাকি।",
                'skills' => [['name' => 'Product design'], ['name' => 'UX research'], ['name' => 'Figma'], ['name' => 'Design systems'], ['name' => 'Bangla typography']],
                'experience' => [
                    ['role' => 'Lead Product Designer', 'org' => 'Taka Labs', 'period' => '2023 – now', 'text' => 'Leading design for a mobile wallet used by 2M people.'],
                    ['role' => 'Product Designer', 'org' => 'Shikkhok', 'period' => '2020 – 2023', 'text' => 'Designed the learning app from zero to 300k students.'],
                ],
                'projects' => [
                    ['title' => 'Bazaar Khata', 'text' => 'A ledger app for small shopkeepers, designed for low literacy.', 'link' => ''],
                    ['title' => 'Bornomala UI kit', 'text' => 'Open-source Bangla-first component library.', 'link' => ''],
                ],
                'education' => [['degree' => 'BFA, Graphic Design', 'school' => 'University of Dhaka', 'period' => '2019']],
                'email' => 'hello@nodi.design', 'phone' => '', 'location' => 'Dhaka, Bangladesh',
                'social_links' => [['platform' => 'LinkedIn', 'url' => '#'], ['platform' => 'Behance', 'url' => '#']],
            ],
        ];
    }

    private static function modernProfessional(): array
    {
        return [
            'label' => 'Modern Professional',
            'theme' => ['primary' => '#0F2A44', 'accent' => '#D4A24C', 'background' => '#F4F1EA', 'text' => '#13212F', 'font_heading' => 'manrope', 'font_body' => 'inter'],
            'fields' => [
                self::f('full_name', 'পুরো নাম', 'text', ['required' => true, 'max_length' => 80]),
                self::f('job_title', 'পদবি', 'text', ['required' => true, 'max_length' => 100, 'placeholder' => 'Senior Software Engineer']),
                self::f('profile_photo', 'প্রোফাইল ছবি', 'image'),
                self::f('summary', 'পেশাগত সারসংক্ষেপ', 'textarea', ['required' => true, 'max_length' => 1500]),
                self::f('stats', 'হাইলাইট (সংখ্যায়)', 'repeater', ['help' => 'যেমন: "৮+" বছরের অভিজ্ঞতা', 'options' => ['max' => 4, 'fields' => [
                    ['key' => 'value', 'label' => 'সংখ্যা', 'type' => 'text'], ['key' => 'label', 'label' => 'লেবেল', 'type' => 'text'],
                ]]]),
                self::f('experience', 'অভিজ্ঞতা', 'repeater', ['options' => ['max' => 10, 'fields' => [
                    ['key' => 'role', 'label' => 'ভূমিকা', 'type' => 'text'], ['key' => 'org', 'label' => 'প্রতিষ্ঠান', 'type' => 'text'],
                    ['key' => 'period', 'label' => 'সময়কাল', 'type' => 'text'], ['key' => 'text', 'label' => 'অর্জন', 'type' => 'textarea'],
                ]]]),
                self::f('skills', 'দক্ষতা', 'repeater', ['options' => ['max' => 24, 'fields' => [['key' => 'name', 'label' => 'দক্ষতা', 'type' => 'text']]]]),
                self::f('education', 'শিক্ষা', 'repeater', ['options' => ['max' => 6, 'fields' => [
                    ['key' => 'degree', 'label' => 'ডিগ্রি', 'type' => 'text'], ['key' => 'school', 'label' => 'শিক্ষাপ্রতিষ্ঠান', 'type' => 'text'], ['key' => 'period', 'label' => 'সাল', 'type' => 'text'],
                ]]]),
                self::f('email', 'ইমেইল', 'email'),
                self::f('phone', 'ফোন', 'phone'),
                self::f('location', 'লোকেশন', 'text'),
                self::f('cv_link', 'CV ডাউনলোড লিংক', 'url', ['help' => 'আপনার CV-এর Google Drive বা Dropbox লিংক (ঐচ্ছিক)']),
                self::socials(),
            ],
            'sample' => [
                'full_name' => 'Tanvir Ahmed', 'job_title' => 'Senior Software Engineer',
                'profile_photo' => self::img('portrait-b'),
                'summary' => 'Backend engineer with 8 years of building payment and logistics systems in Bangladesh. I care about reliable software, clear writing and mentoring junior engineers.',
                'stats' => [['value' => '8+', 'label' => 'Years experience'], ['value' => '40+', 'label' => 'Projects shipped'], ['value' => '12', 'label' => 'Engineers mentored']],
                'experience' => [
                    ['role' => 'Senior Software Engineer', 'org' => 'Pathao', 'period' => '2022 – Present', 'text' => 'Scaled the dispatch service to 1M daily trips.'],
                    ['role' => 'Software Engineer', 'org' => 'bKash', 'period' => '2018 – 2022', 'text' => 'Built merchant settlement and reconciliation services.'],
                ],
                'skills' => [['name' => 'PHP / Laravel'], ['name' => 'Go'], ['name' => 'MySQL'], ['name' => 'System design'], ['name' => 'AWS']],
                'education' => [['degree' => 'BSc in CSE', 'school' => 'BUET', 'period' => '2017']],
                'email' => 'tanvir@example.com', 'phone' => '+880 1700 000000', 'location' => 'Dhaka', 'cv_link' => '',
                'social_links' => [['platform' => 'LinkedIn', 'url' => '#'], ['platform' => 'GitHub', 'url' => '#']],
            ],
        ];
    }

    private static function creativePortfolio(): array
    {
        return [
            'label' => 'Creative Portfolio',
            'theme' => ['primary' => '#0B0B0B', 'accent' => '#D7F25E', 'background' => '#F2EFE6', 'text' => '#0B0B0B', 'font_heading' => 'syne', 'font_body' => 'space-grotesk'],
            'fields' => [
                self::f('full_name', 'নাম / স্টুডিওর নাম', 'text', ['required' => true, 'max_length' => 60]),
                self::f('role', 'আপনি কী করেন', 'text', ['required' => true, 'max_length' => 80, 'placeholder' => 'Illustrator & photographer']),
                self::f('intro', 'বড় করে প্রথম লাইন', 'textarea', ['max_length' => 240]),
                self::f('profile_photo', 'আপনার ছবি', 'image'),
                self::f('works', 'আপনার কাজের ছবি', 'gallery', ['required' => true, 'help' => 'চাইলে প্রতিটি ছবির নিচে ক্যাপশন দিন।', 'options' => ['max' => 24]]),
                self::f('services', 'সার্ভিস', 'repeater', ['options' => ['max' => 8, 'fields' => [
                    ['key' => 'title', 'label' => 'সার্ভিস', 'type' => 'text'], ['key' => 'text', 'label' => 'বিবরণ', 'type' => 'textarea'],
                ]]]),
                self::f('about', 'পরিচিতি', 'textarea', ['max_length' => 1500]),
                self::f('clients', 'ক্লায়েন্ট / কোলাবোরেশন', 'text', ['help' => 'কমা দিয়ে আলাদা করুন']),
                self::f('email', 'ইমেইল', 'email'),
                self::f('phone', 'ফোন / WhatsApp', 'phone'),
                self::socials(),
            ],
            'sample' => [
                'full_name' => 'Ritu Studio', 'role' => 'Illustrator & photographer',
                'intro' => 'I draw the city the way it feels, loud, warm and a little bit chaotic.',
                'profile_photo' => self::img('portrait-c'),
                'works' => [self::img('art-a'), self::img('art-b'), self::img('art-c'), self::img('landscape-a'), self::img('art-d'), self::img('landscape-b')],
                'services' => [
                    ['title' => 'Editorial illustration', 'text' => 'For magazines, books and brands.'],
                    ['title' => 'Event photography', 'text' => 'Weddings, holud and small gatherings.'],
                    ['title' => 'Packaging art', 'text' => 'Labels and boxes that people keep.'],
                ],
                'about' => 'Chattogram-born, Dhaka-based. 7 years of drawing, 4 years behind the camera.',
                'clients' => 'Prothom Alo, Aarong, Bengal Foundation, Jatra',
                'email' => 'hi@ritu.studio', 'phone' => '',
                'social_links' => [['platform' => 'Instagram', 'url' => '#'], ['platform' => 'Behance', 'url' => '#']],
            ],
        ];
    }

    private static function romanticStory(): array
    {
        return [
            'label' => 'Romantic Story',
            'theme' => ['primary' => '#7A1F3D', 'accent' => '#F29CB2', 'background' => '#FFF5F3', 'text' => '#3B1220', 'font_heading' => 'dancing', 'font_body' => 'hind-siliguri', 'font_accent' => 'cormorant'],
            'fields' => [
                self::f('your_name', 'আপনার নাম', 'text', ['required' => true, 'max_length' => 40]),
                self::f('their_name', 'তার নাম', 'text', ['required' => true, 'max_length' => 40]),
                self::f('relationship', 'সম্পর্ক', 'select', ['options' => ['choices' => ['Girlfriend', 'Boyfriend', 'Wife', 'Husband', 'Fiancé(e)', 'Crush', 'Best friend', 'Long distance love']]]),
                self::f('start_date', 'যেদিন শুরু হয়েছিল', 'date', ['help' => 'কত দিন ধরে একসাথে আছেন, পেজে সেটা দেখাবে।']),
                self::f('hero_photo', 'দুজনের প্রিয় একটি ছবি', 'image', ['required' => true]),
                self::f('headline', 'পেজের শিরোনাম', 'text', ['max_length' => 80, 'placeholder' => 'তুমি আমার প্রিয় গল্প']),
                self::f('story', 'আপনাদের গল্প', 'textarea', ['required' => true, 'max_length' => 4000, 'help' => 'কীভাবে দেখা হয়েছিল? মানুষটা কেন এত বিশেষ?']),
                self::f('favorite_memories', 'প্রিয় স্মৃতিগুলো', 'repeater', ['options' => ['max' => 8, 'fields' => [
                    ['key' => 'title', 'label' => 'স্মৃতি', 'type' => 'text'], ['key' => 'text', 'label' => 'কী হয়েছিল', 'type' => 'textarea'],
                ]]]),
                self::f('gallery', 'আরও ছবি', 'gallery', ['options' => ['max' => 20]]),
                self::f('quotes', 'প্রিয় কিছু লাইন', 'repeater', ['options' => ['max' => 6, 'fields' => [['key' => 'text', 'label' => 'লাইন', 'type' => 'textarea']]]]),
                self::f('special_message', 'তার জন্য একটি বিশেষ বার্তা', 'textarea', ['required' => true, 'max_length' => 2000]),
            ],
            'sample' => [
                'your_name' => 'Mitu', 'their_name' => 'Rafi', 'relationship' => 'Boyfriend', 'start_date' => '2022-02-14',
                'hero_photo' => self::img('couple-a'),
                'headline' => 'তুমি আমার প্রিয় গল্প',
                'story' => "লাইব্রেরির সেই বৃষ্টির বিকেলটা মনে আছে? তুমি আমার ছাতাটা নিয়ে চলে গিয়েছিলে, আর ফেরত দিতে এসেছিলে দুই কাপ চা নিয়ে।\n\nসেদিন থেকে প্রতিটা বৃষ্টি আমার কাছে একটু বেশি সুন্দর।",
                'favorite_memories' => [
                    ['title' => 'প্রথম রিকশা ভ্রমণ', 'text' => 'ধানমন্ডি লেকের পাশ দিয়ে, হাতে বাদাম, মুখে অনেক কথা।'],
                    ['title' => 'কক্সবাজারের সূর্যাস্ত', 'text' => 'তুমি বলেছিলে সমুদ্রও নাকি আমার মত অস্থির।'],
                    ['title' => 'মাঝরাতের ফুচকা', 'text' => 'বেইলি রোডে, শেষ দোকানটা বন্ধ হওয়ার ঠিক আগে।'],
                ],
                'gallery' => [self::img('couple-b'), self::img('landscape-c'), self::img('flowers'), self::img('couple-c')],
                'quotes' => [['text' => 'তোমাকে দেখলে মনে হয় সময় একটু থেমে যাক।'], ['text' => 'Home is wherever you are.']],
                'special_message' => "রাফি, তুমি আমার সবচেয়ে প্রিয় মানুষ। আমার পাশে থাকার জন্য, আমার পাগলামিগুলো সহ্য করার জন্য ধন্যবাদ।\n\nভালোবাসি, আজ, কাল, সবসময়।",
            ],
        ];
    }

    private static function memoryTimeline(): array
    {
        return [
            'label' => 'Memory Timeline',
            'theme' => ['primary' => '#1E3A5F', 'accent' => '#E07A5F', 'background' => '#F7F1E5', 'text' => '#1E2A38', 'font_heading' => 'dm-serif', 'font_body' => 'hind-siliguri', 'font_accent' => 'caveat'],
            'fields' => [
                self::f('your_name', 'আপনার নাম', 'text', ['required' => true, 'max_length' => 40]),
                self::f('their_name', 'তার নাম', 'text', ['required' => true, 'max_length' => 40]),
                self::f('title', 'পেজের শিরোনাম', 'text', ['max_length' => 80, 'placeholder' => 'Our little story']),
                self::f('intro', 'পরিচিতি', 'textarea', ['max_length' => 1000]),
                self::f('cover_photo', 'কভার ছবি', 'image'),
                self::f('timeline', 'টাইমলাইনের মুহূর্তগুলো', 'repeater', ['required' => true, 'help' => 'সময়ের ক্রমে লিখুন: প্রথম দেখা, প্রথম ঘোরা, এভাবে।', 'options' => ['max' => 20, 'fields' => [
                    ['key' => 'date', 'label' => 'কখন', 'type' => 'text'], ['key' => 'title', 'label' => 'শিরোনাম', 'type' => 'text'], ['key' => 'text', 'label' => 'কী হয়েছিল', 'type' => 'textarea'],
                ]]]),
                self::f('gallery', 'ছবি (প্রতি মুহূর্তে একটি হলে দারুণ)', 'gallery', ['options' => ['max' => 20]]),
                self::f('closing_message', 'শেষ বার্তা', 'textarea', ['max_length' => 1500]),
            ],
            'sample' => [
                'your_name' => 'Sadia', 'their_name' => 'Arif', 'title' => 'আমাদের ছোট্ট গল্প',
                'intro' => 'কিছু তারিখ ক্যালেন্ডারে থাকে, আর কিছু থাকে মনে। এখানে আমাদের মনের তারিখগুলো।',
                'cover_photo' => self::img('couple-b'),
                'timeline' => [
                    ['date' => 'মার্চ ২০১৯', 'title' => 'প্রথম দেখা', 'text' => 'বন্ধুর জন্মদিনের পার্টিতে, তুমি কেক কাটতে গিয়ে ছুরি ভুলে গিয়েছিলে।'],
                    ['date' => 'ডিসেম্বর ২০১৯', 'title' => 'প্রথম ট্যুর', 'text' => 'সিলেটের চা বাগান, আর হারিয়ে যাওয়া রাস্তা।'],
                    ['date' => 'জুন ২০২১', 'title' => 'দূরে থাকার বছর', 'text' => 'ভিডিও কলে রাতের পর রাত।'],
                    ['date' => 'জানুয়ারি ২০২৪', 'title' => 'আবার একসাথে', 'text' => 'এয়ারপোর্টের সেই আলিঙ্গন।'],
                ],
                'gallery' => [self::img('couple-a'), self::img('landscape-a'), self::img('couple-c'), self::img('flowers')],
                'closing_message' => 'সামনের পাতাগুলোও একসাথে লিখব।',
            ],
        ];
    }

    private static function loveLetter(): array
    {
        return [
            'label' => 'Love Letter',
            'theme' => ['primary' => '#8B2E2E', 'accent' => '#C9A46A', 'background' => '#EFE4D2', 'text' => '#3A2A1E', 'font_heading' => 'caveat', 'font_body' => 'tiro-bangla', 'font_accent' => 'cormorant'],
            'fields' => [
                self::f('to_name', 'চিঠিটা কার জন্য', 'text', ['required' => true, 'max_length' => 40]),
                self::f('from_name', 'প্রেরক', 'text', ['required' => true, 'max_length' => 40]),
                self::f('letter_date', 'চিঠির তারিখ', 'date'),
                self::f('opening', 'শুরুর লাইন', 'text', ['max_length' => 120, 'placeholder' => 'প্রিয়তমা,']),
                self::f('letter', 'আপনার চিঠি', 'textarea', ['required' => true, 'max_length' => 6000, 'help' => 'মন খুলে লিখুন, যত বড় ইচ্ছা।']),
                self::f('signature', 'শেষে আপনার নাম', 'text', ['max_length' => 80, 'placeholder' => 'শুধু তোমার,']),
                self::f('photo', 'চিঠির ভেতরে রাখার একটি ছবি', 'image'),
                self::f('ps', 'পুনশ্চ (P.S.)', 'text', ['max_length' => 200]),
            ],
            'sample' => [
                'to_name' => 'Nusrat', 'from_name' => 'Imran', 'letter_date' => '2026-02-14', 'opening' => 'প্রিয় নুসরাত,',
                'letter' => "অনেকদিন ভেবেছি তোমাকে একটা চিঠি লিখব। মেসেজে তো রোজই কথা হয়, কিন্তু কিছু কথা কাগজের মতো ধীরে বলতে হয়।\n\nতুমি আমার জীবনে আসার পর থেকে সাধারণ দিনগুলোও কেমন যেন উৎসবের মতো লাগে। সকালের চা, অফিস ফেরত জ্যাম, এমনকি লোডশেডিংয়ের রাতও।\n\nআমি খুব গুছিয়ে কথা বলতে পারি না। শুধু এটুকু জানি, যতদিন বাঁচব তোমার হাসিটা দেখতে চাই।",
                'signature' => 'শুধু তোমার,', 'photo' => self::img('couple-c'), 'ps' => 'আজ রাতে তোমার প্রিয় বিরিয়ানিটা আমি আনছি।',
            ],
        ];
    }

    private static function birthday(): array
    {
        return [
            'label' => 'Birthday Celebration',
            'theme' => ['primary' => '#2B2D8F', 'accent' => '#FFB703', 'background' => '#FFF8E7', 'text' => '#1D1F4E', 'font_heading' => 'baloo-da', 'font_body' => 'hind-siliguri'],
            'fields' => [
                self::f('birthday_name', "Birthday person's name", 'text', ['required' => true, 'max_length' => 40]),
                self::f('birthday_date', 'জন্মদিনের তারিখ', 'date', ['required' => true]),
                self::f('age', 'কত বছরে পা দিচ্ছে', 'text', ['max_length' => 4, 'help' => 'ঐচ্ছিক']),
                self::f('hero_photo', 'প্রধান ছবি', 'image', ['required' => true]),
                self::f('message', 'জন্মদিনের বার্তা', 'textarea', ['required' => true, 'max_length' => 3000]),
                self::f('memories', 'একসাথের স্মৃতি', 'repeater', ['options' => ['max' => 8, 'fields' => [
                    ['key' => 'title', 'label' => 'স্মৃতি', 'type' => 'text'], ['key' => 'text', 'label' => 'বিস্তারিত', 'type' => 'textarea'],
                ]]]),
                self::f('gallery', 'ছবির গ্যালারি', 'gallery', ['options' => ['max' => 20]]),
                self::f('wishes', 'বন্ধু ও পরিবারের শুভেচ্ছা', 'repeater', ['options' => ['max' => 20, 'fields' => [
                    ['key' => 'from', 'label' => 'প্রেরক', 'type' => 'text'], ['key' => 'message', 'label' => 'শুভেচ্ছা', 'type' => 'textarea'],
                ]]]),
                self::f('from_name', 'প্রেরক (আপনার নাম)', 'text', ['max_length' => 60]),
            ],
            'sample' => [
                'birthday_name' => 'Mitu', 'birthday_date' => now()->addDays(3)->toDateString(), 'age' => '24',
                'hero_photo' => self::img('portrait-a'),
                'message' => "শুভ জন্মদিন মিতু! 🎂\n\nতুই না থাকলে আমাদের আড্ডাগুলো এত রঙিন হতো না। নতুন বছরটা হোক অনেক হাসি, অনেক ঘোরাঘুরি আর অনেক চকলেটে ভরা।",
                'memories' => [
                    ['title' => 'ভার্সিটির প্রথম দিন', 'text' => 'ক্লাস খুঁজে না পেয়ে দুজনে ক্যান্টিনে বসে ছিলাম।'],
                    ['title' => 'সাজেক ট্রিপ', 'text' => 'মেঘের মধ্যে দাঁড়িয়ে তোর সেই চিৎকার!'],
                ],
                'gallery' => [self::img('cake'), self::img('friends'), self::img('landscape-b'), self::img('portrait-c')],
                'wishes' => [
                    ['from' => 'আম্মু', 'message' => 'অনেক বড় হ মা, অনেক ভালো থাকিস।'],
                    ['from' => 'Tanha', 'message' => 'Happy birthday, partner in crime! 💛'],
                    ['from' => 'Rafi', 'message' => 'Treat kobe dibi?'],
                ],
                'from_name' => 'তোর বন্ধুরা',
            ],
        ];
    }

    private static function ourJourney(): array
    {
        return [
            'label' => 'Our Journey',
            'theme' => ['primary' => '#123B34', 'accent' => '#C8A45D', 'background' => '#0E2622', 'text' => '#F3EBDD', 'font_heading' => 'cormorant', 'font_body' => 'noto-serif-bengali'],
            'fields' => [
                self::f('partner_one', 'প্রথম নাম', 'text', ['required' => true, 'max_length' => 40]),
                self::f('partner_two', 'দ্বিতীয় নাম', 'text', ['required' => true, 'max_length' => 40]),
                self::f('anniversary_date', 'বার্ষিকী / বিয়ের তারিখ', 'date', ['required' => true]),
                self::f('hero_photo', 'কভার ছবি', 'image', ['required' => true]),
                self::f('intro', 'পরিচিতি', 'textarea', ['max_length' => 1500]),
                self::f('milestones', 'আপনাদের পথচলার অধ্যায়', 'repeater', ['options' => ['max' => 15, 'fields' => [
                    ['key' => 'date', 'label' => 'কখন', 'type' => 'text'], ['key' => 'title', 'label' => 'অধ্যায়ের নাম', 'type' => 'text'], ['key' => 'text', 'label' => 'গল্প', 'type' => 'textarea'],
                ]]]),
                self::f('gallery', 'ছবিগুলো', 'gallery', ['options' => ['max' => 24]]),
                self::f('message', 'বার্ষিকীর বার্তা', 'textarea', ['required' => true, 'max_length' => 2500]),
            ],
            'sample' => [
                'partner_one' => 'Anika', 'partner_two' => 'Shuvo', 'anniversary_date' => '2021-11-19',
                'hero_photo' => self::img('couple-a'),
                'intro' => 'পাঁচটা বছর, অগণিত চা, কয়েকটা ঝগড়া আর একটা ঘর।',
                'milestones' => [
                    ['date' => '২০১৮', 'title' => 'অধ্যায় ১: পরিচয়', 'text' => 'একই অফিসের ভিন্ন দুই ফ্লোর, একটাই লিফট।'],
                    ['date' => '২০২০', 'title' => 'অধ্যায় ২: প্রস্তাব', 'text' => 'ছাদে, লাল বাতি আর কাঁপা হাতে আংটি।'],
                    ['date' => '২০২১', 'title' => 'অধ্যায় ৩: বিয়ে', 'text' => 'দুই পরিবারের হৈচৈ, আর আমরা দুজন।'],
                    ['date' => '২০২৪', 'title' => 'অধ্যায় ৪: আমাদের ঘর', 'text' => 'ছোট্ট বারান্দায় অনেক গাছ।'],
                ],
                'gallery' => [self::img('couple-b'), self::img('flowers'), self::img('couple-c'), self::img('landscape-c')],
                'message' => 'শুভ বিবাহবার্ষিকী। তোমার সাথে বুড়ো হওয়াটাই আমার সবচেয়ে বড় স্বপ্ন।',
            ],
        ];
    }

    private static function cleanBusiness(): array
    {
        return [
            'label' => 'Clean Business',
            'theme' => ['primary' => '#0E7C66', 'accent' => '#F4A259', 'background' => '#FFFFFF', 'text' => '#17312B', 'font_heading' => 'manrope', 'font_body' => 'hind-siliguri'],
            'fields' => [
                self::f('business_name', 'ব্যবসার নাম', 'text', ['required' => true, 'max_length' => 80]),
                self::f('tagline', 'ট্যাগলাইন', 'text', ['max_length' => 140]),
                self::f('logo', 'লোগো', 'image'),
                self::f('cover_photo', 'কভার ছবি', 'image'),
                self::f('about', 'ব্যবসা সম্পর্কে', 'textarea', ['required' => true, 'max_length' => 2500]),
                self::f('services', 'পণ্য / সার্ভিস', 'repeater', ['options' => ['max' => 20, 'fields' => [
                    ['key' => 'name', 'label' => 'নাম', 'type' => 'text'], ['key' => 'description', 'label' => 'বিবরণ', 'type' => 'textarea'], ['key' => 'price', 'label' => 'দাম (ঐচ্ছিক)', 'type' => 'text'],
                ]]]),
                self::f('gallery', 'পণ্য বা দোকানের ছবি', 'gallery', ['options' => ['max' => 20]]),
                self::f('phone', 'ফোন', 'phone', ['required' => true]),
                self::f('whatsapp', 'WhatsApp নম্বর', 'phone'),
                self::f('email', 'ইমেইল', 'email'),
                self::f('address', 'ঠিকানা', 'textarea', ['max_length' => 300]),
                self::f('map_link', 'Google Maps লিংক', 'url'),
                self::f('hours', 'খোলার সময়', 'textarea', ['max_length' => 300, 'placeholder' => "Sat–Thu: 10am – 9pm\nFriday: closed"]),
                self::socials(),
            ],
            'sample' => [
                'business_name' => 'Rafi’s Café', 'tagline' => 'Dhanmondi-র সবচেয়ে আরামের কোণ। ভালো কফি, ঘরের মতো খাবার।',
                'logo' => self::img('logo-cafe'), 'cover_photo' => self::img('cafe'),
                'about' => 'ছোট্ট একটা ক্যাফে, যেখানে কফি বানানো হয় ধীরে, আর গল্প চলে অনেকক্ষণ। ২০২২ সাল থেকে আমরা ধানমন্ডি ২৭-এ।',
                'services' => [
                    ['name' => 'Signature Cold Brew', 'description' => '18-hour steeped, served with gur syrup.', 'price' => '৳220'],
                    ['name' => 'Beef Tehari Bowl', 'description' => 'Old Dhaka style, Friday special.', 'price' => '৳280'],
                    ['name' => 'Private Events', 'description' => 'Birthday & small gatherings up to 25 people.', 'price' => ''],
                ],
                'gallery' => [self::img('cafe'), self::img('art-b'), self::img('cake')],
                'phone' => '01700-000000', 'whatsapp' => '01700-000000', 'email' => 'hello@rafiscafe.com',
                'address' => 'House 12, Road 27, Dhanmondi, Dhaka', 'map_link' => '',
                'hours' => "Every day: 9am – 11pm",
                'social_links' => [['platform' => 'Facebook', 'url' => '#'], ['platform' => 'Instagram', 'url' => '#']],
            ],
        ];
    }

    private static function modernService(): array
    {
        return [
            'label' => 'Modern Service',
            'theme' => ['primary' => '#FF5A36', 'accent' => '#7C5CFF', 'background' => '#0D0D12', 'text' => '#F5F5F7', 'font_heading' => 'space-grotesk', 'font_body' => 'inter'],
            'fields' => [
                self::f('brand_name', 'নাম / ব্র্যান্ড', 'text', ['required' => true, 'max_length' => 60]),
                self::f('headline', 'হেডলাইন', 'text', ['required' => true, 'max_length' => 120, 'placeholder' => 'I help small brands look big online']),
                self::f('intro', 'ছোট্ট পরিচিতি', 'textarea', ['max_length' => 800]),
                self::f('photo', 'ছবি / কভার', 'image'),
                self::f('services', 'সার্ভিস', 'repeater', ['required' => true, 'options' => ['max' => 9, 'fields' => [
                    ['key' => 'title', 'label' => 'সার্ভিস', 'type' => 'text'], ['key' => 'text', 'label' => 'বিবরণ', 'type' => 'textarea'], ['key' => 'price', 'label' => 'শুরুর দাম', 'type' => 'text'],
                ]]]),
                self::f('process', 'কীভাবে কাজ করেন (ধাপ)', 'repeater', ['options' => ['max' => 5, 'fields' => [
                    ['key' => 'title', 'label' => 'ধাপ', 'type' => 'text'], ['key' => 'text', 'label' => 'বিবরণ', 'type' => 'textarea'],
                ]]]),
                self::f('testimonials', 'ক্লায়েন্টদের মতামত', 'repeater', ['options' => ['max' => 6, 'fields' => [
                    ['key' => 'name', 'label' => 'ক্লায়েন্ট', 'type' => 'text'], ['key' => 'text', 'label' => 'মতামত', 'type' => 'textarea'],
                ]]]),
                self::f('cta_text', 'বাটনের লেখা', 'text', ['max_length' => 60, 'placeholder' => "Let's work together"]),
                self::f('email', 'ইমেইল', 'email'),
                self::f('phone', 'ফোন', 'phone'),
                self::f('whatsapp', 'WhatsApp', 'phone'),
                self::socials(),
            ],
            'sample' => [
                'brand_name' => 'Sakib Motion', 'headline' => 'Motion design that makes people stop scrolling.',
                'intro' => 'Freelance motion designer working with brands in Bangladesh, India and the UK.',
                'photo' => self::img('portrait-b'),
                'services' => [
                    ['title' => 'Social ads', 'text' => '6–15s reels and stories that convert.', 'price' => 'from ৳5,000'],
                    ['title' => 'Explainer videos', 'text' => 'Animated product explainers up to 90s.', 'price' => 'from ৳18,000'],
                    ['title' => 'Logo animation', 'text' => 'Bring your brand mark to life.', 'price' => 'from ৳3,500'],
                ],
                'process' => [
                    ['title' => 'Brief', 'text' => 'A 20-minute call to understand your goal.'],
                    ['title' => 'Storyboard', 'text' => 'Frames and script for your approval.'],
                    ['title' => 'Animate & deliver', 'text' => 'Two rounds of revisions included.'],
                ],
                'testimonials' => [['name' => 'Fahim, Shajgoj', 'text' => 'Fast, creative and easy to work with.'], ['name' => 'Lamia, Kaporer Golpo', 'text' => 'Our reel views tripled.']],
                'cta_text' => 'চলুন কাজ শুরু করি', 'email' => 'sakib@example.com', 'phone' => '', 'whatsapp' => '01800000000',
                'social_links' => [['platform' => 'Instagram', 'url' => '#'], ['platform' => 'YouTube', 'url' => '#']],
            ],
        ];
    }

    private static function photoMemories(): array
    {
        return [
            'label' => 'Photo Memories',
            'theme' => ['primary' => '#F5EFE6', 'accent' => '#E9B44C', 'background' => '#151515', 'text' => '#F5EFE6', 'font_heading' => 'playfair', 'font_body' => 'hind-siliguri', 'font_accent' => 'caveat'],
            'fields' => [
                self::f('title', 'অ্যালবামের নাম', 'text', ['required' => true, 'max_length' => 80, 'placeholder' => 'Sajek, 2025']),
                self::f('subtitle', 'সাবটাইটেল', 'text', ['max_length' => 140]),
                self::f('date_text', 'কখন / কোথায়', 'text', ['max_length' => 80, 'placeholder' => 'March 2025 · Rangamati']),
                self::f('cover_photo', 'কভার ছবি', 'image', ['required' => true]),
                self::f('intro', 'পরিচিতি', 'textarea', ['max_length' => 1500]),
                self::f('gallery', 'ছবিগুলো', 'gallery', ['required' => true, 'help' => 'ক্যাপশনগুলো প্রতিটি ছবির নিচে হাতের লেখার মতো দেখাবে।', 'options' => ['max' => 40]]),
                self::f('people', 'এই গল্পের মানুষগুলো', 'text', ['help' => 'নামগুলো কমা দিয়ে আলাদা করুন']),
                self::f('closing_note', 'শেষ কথা', 'textarea', ['max_length' => 1000]),
            ],
            'sample' => [
                'title' => 'মেঘের দেশে তিন দিন', 'subtitle' => 'Five friends, one jeep and too much tea.', 'date_text' => 'মার্চ ২০২৫ · সাজেক',
                'cover_photo' => self::img('landscape-a'),
                'intro' => 'অনেকদিনের প্ল্যান, অবশেষে বেরিয়ে পড়া। এই অ্যালবামটা সেই তিন দিনের জন্য।',
                'gallery' => array_map(function ($n, $c) { $i = self::img($n); $i['caption'] = $c; return $i; },
                    ['landscape-b', 'friends', 'landscape-c', 'art-a', 'landscape-a', 'flowers'],
                    ['ভোর ৫টা, মেঘ তখনো ঘুমায়', 'চান্দের গাড়ির ছাদে', 'কংলাক পাহাড়ের পথে', 'রাতের আগুন', 'শেষ বিকেল', 'পাহাড়ি ফুল']),
                'people' => 'Mitu, Rafi, Tanha, Sakib, Nodi',
                'closing_note' => 'আবার যাব, এবার বর্ষায়।',
            ],
        ];
    }

    private static function eventInvitation(): array
    {
        return [
            'label' => 'Event Invitation',
            'theme' => ['primary' => '#6B2D5C', 'accent' => '#E8B04B', 'background' => '#FBF3E8', 'text' => '#2E1A2A', 'font_heading' => 'playfair', 'font_body' => 'hind-siliguri', 'font_accent' => 'galada'],
            'fields' => [
                self::f('event_title', 'অনুষ্ঠানের নাম', 'text', ['required' => true, 'max_length' => 80, 'placeholder' => 'Anika & Shuvo’s Wedding']),
                self::f('hosts', 'আয়োজক', 'text', ['max_length' => 120]),
                self::f('event_date', 'তারিখ', 'date', ['required' => true]),
                self::f('event_time', 'সময়', 'text', ['max_length' => 40, 'placeholder' => 'সন্ধ্যা ৭টা']),
                self::f('venue', 'অনুষ্ঠানের স্থান', 'textarea', ['required' => true, 'max_length' => 300]),
                self::f('map_link', 'Google Maps লিংক', 'url'),
                self::f('hero_photo', 'প্রধান ছবি', 'image'),
                self::f('message', 'নিমন্ত্রণ বার্তা', 'textarea', ['required' => true, 'max_length' => 2000]),
                self::f('schedule', 'অনুষ্ঠানসূচি', 'repeater', ['options' => ['max' => 12, 'fields' => [
                    ['key' => 'time', 'label' => 'সময়', 'type' => 'text'], ['key' => 'title', 'label' => 'কী হবে', 'type' => 'text'],
                ]]]),
                self::f('dress_code', 'ড্রেস কোড', 'text', ['max_length' => 80]),
                self::f('gallery', 'ছবিগুলো', 'gallery', ['options' => ['max' => 16]]),
                self::f('rsvp_phone', 'যোগাযোগের ফোন (RSVP)', 'phone'),
            ],
            'sample' => [
                'event_title' => 'অনিকা ও শুভর বিয়ে', 'hosts' => 'রহমান ও চৌধুরী পরিবার',
                'event_date' => now()->addDays(20)->toDateString(), 'event_time' => 'সন্ধ্যা ৭টা',
                'venue' => "Bashundhara Convention Hall 2\nBashundhara R/A, Dhaka", 'map_link' => '',
                'hero_photo' => self::img('flowers'),
                'message' => 'আমাদের জীবনের নতুন অধ্যায়ের শুরুতে আপনার উপস্থিতি ও দোয়া কামনা করছি।',
                'schedule' => [['time' => '৭:০০', 'title' => 'অতিথি আগমন'], ['time' => '৮:০০', 'title' => 'বিবাহ অনুষ্ঠান'], ['time' => '৯:০০', 'title' => 'নৈশভোজ']],
                'dress_code' => 'Traditional', 'gallery' => [self::img('couple-a'), self::img('couple-c')], 'rsvp_phone' => '01700000000',
            ],
        ];
    }
}
