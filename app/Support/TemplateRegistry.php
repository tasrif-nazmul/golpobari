<?php

namespace App\Support;

use Illuminate\Support\Str;

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
        'baloo-da' => ['label' => 'Baloo Da 2 (Bangla rounded)', 'css' => "'Baloo Da 2', sans-serif", 'google' => 'Baloo+Da+2:wght@500;700;800'],
        'anek-bangla' => ['label' => 'Anek Bangla (Bangla display)', 'css' => "'Anek Bangla', 'Hind Siliguri', sans-serif", 'google' => 'Anek+Bangla:wght@300;500;700;800'],
        'atma' => ['label' => 'Atma (Bangla playful)', 'css' => "'Atma', 'Hind Siliguri', cursive", 'google' => 'Atma:wght@400;600;700'],
        'mina' => ['label' => 'Mina (Bangla)', 'css' => "'Mina', 'Hind Siliguri', sans-serif", 'google' => 'Mina:wght@400;700'],
        'great-vibes' => ['label' => 'Great Vibes (script)', 'css' => "'Great Vibes', 'Galada', cursive", 'google' => 'Great+Vibes'],
        'italiana' => ['label' => 'Italiana (elegant)', 'css' => "'Italiana', 'Noto Serif Bengali', serif", 'google' => 'Italiana'],
        'instrument-serif' => ['label' => 'Instrument Serif', 'css' => "'Instrument Serif', 'Noto Serif Bengali', serif", 'google' => 'Instrument+Serif:ital@0;1'],
        'bricolage' => ['label' => 'Bricolage Grotesque', 'css' => "'Bricolage Grotesque', 'Anek Bangla', sans-serif", 'google' => 'Bricolage+Grotesque:opsz,wght@12..96,400;12..96,700;12..96,800'],
    ];

    /** Fonts always loaded alongside the template's choices so Bangla renders well. */
    private const BANGLA_FALLBACKS = ['Hind+Siliguri:wght@400;500;600;700', 'Noto+Serif+Bengali:wght@400;600;700'];

    /** Built-in background music (original tracks made for GolpoBari, see tools/music). */
    public const MUSIC = [
        'prem' => ['title' => 'প্রেমের সুর', 'mood' => 'নরম, রোমান্টিক পিয়ানো', 'icon' => '💞'],
        'khushi' => ['title' => 'খুশির দিন', 'mood' => 'হাসিখুশি, জন্মদিনের মতো', 'icon' => '🎈'],
        'smriti' => ['title' => 'স্মৃতির পাতা', 'mood' => 'মিউজিক বক্স, একটু মন কেমন করা', 'icon' => '📖'],
        'utsob' => ['title' => 'উৎসবের রং', 'mood' => 'ঢোল আর বাঁশি, উৎসবের আমেজ', 'icon' => '🪘'],
        'nouka' => ['title' => 'নৌকার গান', 'mood' => 'গিটার আর বাঁশি, ভ্রমণের মতো হালকা', 'icon' => '⛵'],
        'prodip' => ['title' => 'শান্ত প্রদীপ', 'mood' => 'শান্ত তানপুরা আর বাঁশি', 'icon' => '🪔'],
    ];

    /** Default song per layout (used for samples and pre-selected in the order form). */
    private const LAYOUT_MUSIC = [
        'romantic-story' => 'prem', 'love-letter' => 'prem', 'our-journey' => 'prem', 'proposal' => 'prem', 'reasons-cards' => 'prem',
        'birthday-celebration' => 'khushi', 'gift-surprise' => 'khushi',
        'memory-timeline' => 'smriti', 'photo-memories' => 'smriti',
        'event-invitation' => 'utsob', 'festival-greeting' => 'utsob',
        'travel-diary' => 'nouka', 'tribute' => 'prodip',
    ];

    public static function musicUrl(?string $key): ?string
    {
        return $key && isset(self::MUSIC[$key]) ? asset('audio/'.$key.'.mp3') : null;
    }

    public static function defaultMusic(?string $layout): string
    {
        return self::LAYOUT_MUSIC[$layout] ?? '';
    }

    /** The background-music field every layout gets. */
    public static function musicField(?string $layout = null): array
    {
        return self::f('music', 'পেজের গান', 'music', [
            'help' => 'পেজ খুললে হালকা করে বাজবে। আমাদের গান থেকে বাছুন বা নিজের পছন্দের গান দিন।',
            'placeholder' => self::defaultMusic($layout),
        ]);
    }

    public static function layouts(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $cache = self::baseLayouts();
        foreach ($cache as $key => &$layout) {
            $layout['fields'][] = self::musicField($key);
            $layout['sample']['music'] = self::defaultMusic($key);
        }

        return $cache;
    }

    private static function baseLayouts(): array
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
            'proposal' => self::proposal(),
            'gift-surprise' => self::giftSurprise(),
            'reasons-cards' => self::reasonsCards(),
            'travel-diary' => self::travelDiary(),
            'tribute' => self::tribute(),
            'festival-greeting' => self::festivalGreeting(),
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

    /**
     * The order form's map of a layout: which part of the page each field fills.
     * Sections follow the page from top to bottom; fields not listed land in "আরও কিছু".
     *
     * @param  array<int, array>  $fields  the template's field schemas (may include admin-added fields)
     */
    public static function guide(?string $layout, array $fields, ?array $sample = null): array
    {
        $def = self::layout($layout);
        $sections = self::guides()[$layout] ?? [];
        $sample ??= $def['sample'] ?? [];
        $keys = collect($fields)->pluck('key')->all();
        $placed = [];
        $out = [];
        foreach ($sections as $sec) {
            $f = array_values(array_filter($sec['fields'], fn ($k) => in_array($k, $keys, true)));
            if (! $f) {
                continue;
            }
            $placed = array_merge($placed, $f);
            $out[] = ['title' => $sec['title'], 'where' => $sec['where'], 'icon' => $sec['icon'], 'fields' => $f];
        }
        if (in_array('music', $keys, true) && ! in_array('music', $placed, true)) {
            $placed[] = 'music';
            $music = ['title' => 'পেজের গান', 'where' => 'পেজ খুললেই পেছনে হালকা করে বাজবে, কোণে থাকবে চালু/বন্ধের বোতাম', 'icon' => '🎵', 'fields' => ['music']];
        }
        $rest = array_values(array_diff($keys, $placed));
        if ($rest) {
            $out[] = ['title' => 'আরও কিছু', 'where' => 'পেজের নিচের দিকে আলাদা অংশে দেখাবে', 'icon' => '➕', 'fields' => $rest];
        }
        if (! empty($music)) {
            $out[] = $music;
        }
        $total = count($out);
        foreach ($out as $i => &$sec) {
            $sec['n'] = $i + 1;
            $sec['total'] = $total;
        }

        $examples = [];
        foreach ($keys as $k) {
            $v = $sample[$k] ?? null;
            if (is_array($v) && isset($v['url'])) {
                $examples[$k] = ['image' => $v['thumb'] ?? $v['url']];
            } elseif (is_array($v) && isset($v[0]['url'])) {
                $examples[$k] = ['image' => $v[0]['thumb'] ?? $v[0]['url'], 'count' => count($v)];
            } elseif (is_array($v) && isset($v[0]) && is_array($v[0])) {
                $examples[$k] = ['row' => $v[0], 'count' => count($v)];
            } elseif (is_string($v) && $v !== '') {
                $examples[$k] = ['text' => Str::limit(preg_replace('/\s+/u', ' ', $v), 90)];
            }
        }

        return ['sections' => $out, 'examples' => $examples];
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

    /** Sample photos a single template shows instead of its layout's (slug => field => photo name). */
    public const SAMPLE_PHOTOS = [
        'gaye-holud' => ['hero_photo' => 'holud', 'gallery' => ['flowers', 'friends']],
    ];

    /**
     * A sample photo: the free Unsplash photo in public/images/samples/<name>.webp (sources and
     * licence in tools/photos/sources.json), or the drawn .svg when there is no photo.
     */
    public static function img(string $name): array
    {
        $file = 'images/samples/'.$name.(file_exists(public_path('images/samples/'.$name.'.webp')) ? '.webp' : '.svg');

        return ['url' => asset($file), 'thumb' => asset($file), 'caption' => null];
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
                'hours' => 'Every day: 9am – 11pm',
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
                'gallery' => array_map(function ($n, $c) {
                    $i = self::img($n);
                    $i['caption'] = $c;

                    return $i;
                },
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
                'hero_photo' => self::img('couple-a'),
                'message' => 'আমাদের জীবনের নতুন অধ্যায়ের শুরুতে আপনার উপস্থিতি ও দোয়া কামনা করছি।',
                'schedule' => [['time' => '৭:০০', 'title' => 'অতিথি আগমন'], ['time' => '৮:০০', 'title' => 'বিবাহ অনুষ্ঠান'], ['time' => '৯:০০', 'title' => 'নৈশভোজ']],
                'dress_code' => 'Traditional', 'gallery' => [self::img('couple-c'), self::img('flowers')], 'rsvp_phone' => '01700000000',
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Order-form guide: page sections from top to bottom */
    /* ------------------------------------------------------------------ */

    private static function sec(string $icon, string $title, string $where, array $fields): array
    {
        return compact('icon', 'title', 'where', 'fields');
    }

    public static function guides(): array
    {
        return [
            'romantic-story' => [
                self::sec('💌', 'প্রথম পাতা', 'পেজ খুললেই একদম উপরে: দুজনের নাম, বড় ছবি আর কত দিন একসাথে', ['your_name', 'their_name', 'relationship', 'hero_photo', 'headline', 'start_date']),
                self::sec('📖', 'আপনাদের গল্প', 'ছবির ঠিক নিচে, বড় অক্ষরে শুরু হওয়া গল্পের অংশ', ['story']),
                self::sec('🌸', 'প্রিয় মুহূর্তগুলো', 'গল্পের পরে ছোট ছোট কার্ডে, প্রতিটা স্মৃতি আলাদা কার্ডে', ['favorite_memories']),
                self::sec('📸', 'ছবির দেয়াল', 'পোলারয়েড ছবির মতো বাঁকা করে সাজানো গ্যালারি', ['gallery']),
                self::sec('✨', 'প্রিয় লাইন', 'বড় বাঁকা অক্ষরে, একটার পর একটা উক্তি', ['quotes']),
                self::sec('❤️', 'শেষ চিঠি', 'একদম শেষে গাঢ় রঙের কার্ডে, তার নাম ধরে লেখা আপনার বার্তা', ['special_message']),
            ],
            'memory-timeline' => [
                self::sec('🏠', 'প্রথম পাতা', 'একদম উপরে: শিরোনাম, দুজনের নাম, কভার ছবি আর ছোট্ট পরিচিতি', ['title', 'your_name', 'their_name', 'cover_photo', 'intro']),
                self::sec('🧵', 'সুতোয় ঝোলানো মুহূর্তগুলো', 'মাঝখানে একটা সুতো ধরে, তারিখ অনুযায়ী একটার পর একটা কার্ড। প্রতিটা কার্ডের পাশে গ্যালারির একটা করে ছবি বসবে', ['timeline', 'gallery']),
                self::sec('💛', 'শেষ কথা', 'একদম শেষে, হাতের লেখার মতো অক্ষরে', ['closing_message']),
            ],
            'love-letter' => [
                self::sec('✉️', 'খামের উপর', 'পেজ খুললেই একটা খাম, তার উপরে "প্রতি" আর "প্রেরক"', ['to_name', 'from_name', 'letter_date']),
                self::sec('📝', 'চিঠির ভেতর', 'খাম খুললে লাইন টানা কাগজে আপনার চিঠি, টাইপ হতে হতে দেখা যাবে', ['opening', 'letter', 'signature']),
                self::sec('📷', 'চিঠির সাথে ছবি', 'চিঠির কোণায় টেপ দিয়ে লাগানো একটা ছবি', ['photo']),
                self::sec('✒️', 'পুনশ্চ', 'চিঠির একদম নিচে ছোট্ট করে', ['ps']),
            ],
            'birthday-celebration' => [
                self::sec('🎈', 'প্রথম পাতা', 'বেলুন উড়ছে, বড় করে নাম, বয়স আর কাউন্টডাউন', ['birthday_name', 'age', 'birthday_date', 'hero_photo']),
                self::sec('🎂', 'কেক আর বার্তা', 'মোমবাতিওয়ালা কেক। মোমবাতি নেভালে আপনার বার্তাটা বেরিয়ে আসবে', ['message', 'from_name']),
                self::sec('🌟', 'একসাথের স্মৃতি', 'রঙিন কার্ডে ছোট ছোট স্মৃতি', ['memories']),
                self::sec('📸', 'ছবির গ্যালারি', 'বিভিন্ন সাইজের ছবির কোলাজ', ['gallery']),
                self::sec('💬', 'সবার শুভেচ্ছা', 'স্টিকি নোটের দেয়াল, প্রতিটা শুভেচ্ছা একেকটা নোট', ['wishes']),
            ],
            'our-journey' => [
                self::sec('💍', 'প্রথম পাতা', 'আলপনার মাঝে খিলান আকারের ছবি, দুজনের নাম আর বছর-মাস-দিনের হিসাব', ['partner_one', 'partner_two', 'anniversary_date', 'hero_photo', 'intro']),
                self::sec('📜', 'অধ্যায়গুলো', 'বইয়ের পাতার মতো অধ্যায় ১, ২, ৩... একটার পর একটা', ['milestones']),
                self::sec('🖼️', 'ছবিগুলো', 'সোনালি ফ্রেমের গ্যালারি', ['gallery']),
                self::sec('💛', 'বার্ষিকীর বার্তা', 'একদম শেষে, নকশী কাঁথার মতো সেলাই করা ফ্রেমের ভেতরে', ['message']),
            ],
            'photo-memories' => [
                self::sec('🎞️', 'প্রথম পাতা', 'সিনেমার পোস্টারের মতো কভার: অ্যালবামের নাম, কখন-কোথায়', ['title', 'subtitle', 'date_text', 'cover_photo', 'intro']),
                self::sec('📸', 'ছবিগুলো', 'প্রিন্ট করা ছবির মতো, প্রতিটার নিচে আপনার ক্যাপশন', ['gallery']),
                self::sec('🎬', 'এই গল্পের মানুষেরা', 'সিনেমার শেষের ক্রেডিটের মতো নামগুলো', ['people']),
                self::sec('🌅', 'শেষ কথা', 'একদম শেষে "সমাপ্ত" লেখার আগে', ['closing_note']),
            ],
            'event-invitation' => [
                self::sec('💌', 'দাওয়াতপত্রের মুখ', 'নকশা করা কার্ডের উপরে: অনুষ্ঠানের নাম, আয়োজক আর নিমন্ত্রণ বার্তা', ['event_title', 'hosts', 'hero_photo', 'message']),
                self::sec('📅', 'কবে, কোথায়', 'বড় করে তারিখ, কাউন্টডাউন, সময়, ভেন্যু আর ম্যাপ বাটন', ['event_date', 'event_time', 'venue', 'map_link']),
                self::sec('🕰️', 'অনুষ্ঠানসূচি', 'সময় ধরে ধরে কী কী হবে, সাথে ড্রেস কোড', ['schedule', 'dress_code']),
                self::sec('📸', 'ছবি', 'ছবির গ্যালারি', ['gallery']),
                self::sec('📞', 'যোগাযোগ', 'একদম শেষে কল আর WhatsApp বাটন', ['rsvp_phone']),
            ],
            'minimal-portfolio' => [
                self::sec('👋', 'প্রথম পাতা', 'একদম উপরে: নাম, এক লাইনের পরিচয়, ছবি আর লোকেশন', ['full_name', 'headline', 'profile_photo', 'location']),
                self::sec('🙂', 'আমার সম্পর্কে', 'নামের নিচে বড় করে আপনার কথা, সাথে দক্ষতাগুলো', ['about', 'skills']),
                self::sec('💼', 'কাজ আর অভিজ্ঞতা', 'অভিজ্ঞতা, প্রজেক্ট আর শিক্ষা আলাদা তালিকায়', ['experience', 'projects', 'education']),
                self::sec('📬', 'যোগাযোগ', 'একদম শেষে বড় করে ইমেইল, ফোন আর সোশ্যাল লিংক', ['email', 'phone', 'social_links']),
            ],
            'modern-professional' => [
                self::sec('👔', 'প্রথম পাতা', 'নাম, পদবি, ছবি আর সারসংক্ষেপ', ['full_name', 'job_title', 'profile_photo', 'summary', 'location']),
                self::sec('📊', 'সংখ্যায় হাইলাইট', 'বড় বড় সংখ্যার কার্ড (যেমন ৮+ বছর)', ['stats']),
                self::sec('💼', 'অভিজ্ঞতা ও দক্ষতা', 'টাইমলাইনে চাকরিগুলো, পাশে দক্ষতা আর শিক্ষা', ['experience', 'skills', 'education']),
                self::sec('📬', 'যোগাযোগ', 'শেষে CV ডাউনলোড, ইমেইল, ফোন আর লিংক', ['cv_link', 'email', 'phone', 'social_links']),
            ],
            'creative-portfolio' => [
                self::sec('⚡', 'প্রথম পাতা', 'বিশাল অক্ষরে নাম, আপনি কী করেন আর প্রথম লাইন', ['full_name', 'role', 'intro', 'profile_photo']),
                self::sec('🖼️', 'কাজের ছবি', 'বড় বড় ছবির গ্রিড, ক্যাপশনসহ', ['works']),
                self::sec('🛠️', 'সার্ভিস ও পরিচিতি', 'সার্ভিসের তালিকা, আপনার কথা আর ক্লায়েন্টদের নামের চলন্ত লাইন', ['services', 'about', 'clients']),
                self::sec('📬', 'যোগাযোগ', 'শেষে উজ্জ্বল রঙের যোগাযোগের অংশ', ['email', 'phone', 'social_links']),
            ],
            'clean-business' => [
                self::sec('🏪', 'প্রথম পাতা', 'লোগো, ব্যবসার নাম, ট্যাগলাইন আর বড় কভার ছবি', ['business_name', 'tagline', 'logo', 'cover_photo']),
                self::sec('ℹ️', 'আমাদের কথা', 'কভারের নিচে ব্যবসা সম্পর্কে', ['about']),
                self::sec('🧾', 'পণ্য / সার্ভিস', 'মেনু বা দামের তালিকার মতো কার্ড', ['services', 'gallery']),
                self::sec('📍', 'ঠিকানা ও যোগাযোগ', 'খোলার সময়, ঠিকানা, ম্যাপ, কল আর WhatsApp বাটন', ['hours', 'address', 'map_link', 'phone', 'whatsapp', 'email', 'social_links']),
            ],
            'modern-service' => [
                self::sec('🚀', 'প্রথম পাতা', 'ডার্ক ব্যাকগ্রাউন্ডে বড় হেডলাইন, নাম আর ছবি', ['brand_name', 'headline', 'intro', 'photo']),
                self::sec('🧩', 'সার্ভিস', 'নম্বর দেওয়া সার্ভিস কার্ড, সাথে শুরুর দাম', ['services']),
                self::sec('🔁', 'কাজের ধাপ ও রিভিউ', 'ধাপে ধাপে কীভাবে কাজ করেন আর ক্লায়েন্টদের কথা', ['process', 'testimonials']),
                self::sec('📬', 'যোগাযোগ', 'শেষে বড় বাটন আর যোগাযোগের লিংক', ['cta_text', 'email', 'phone', 'whatsapp', 'social_links']),
            ],
            'proposal' => [
                self::sec('💍', 'প্রথম পর্দা', 'পেজ খুললেই পর্দা: "{তার নাম}, তোমার জন্য একটা প্রশ্ন আছে"', ['their_name', 'your_name']),
                self::sec('💭', 'মনের কথাগুলো', 'একটা একটা করে লাইন আসবে, "পরের কথা" চাপলে পরেরটা। সাথে দুজনের ছবি', ['lines', 'hero_photo']),
                self::sec('❓', 'আসল প্রশ্ন', 'আংটির নিচে বড় করে প্রশ্ন, আর হ্যাঁ / না বাটন (না বাটন পালিয়ে যায়!)', ['question']),
                self::sec('🎉', 'হ্যাঁ বললে', 'হ্যাঁ চাপলে আতশবাজি, আর এই লেখা ও ছবিগুলো বেরিয়ে আসবে', ['yes_message', 'gallery', 'whatsapp']),
            ],
            'gift-surprise' => [
                self::sec('🎁', 'গিফট বক্স', 'পেজ খুললেই একটা নড়তে থাকা গিফট বক্স, উপরে ছোট্ট একটা লাইন', ['to_name', 'teaser']),
                self::sec('✨', 'স্ক্র্যাচ কার্ড', 'বক্স খুললে একটা কার্ড, আঙুল দিয়ে ঘষলে আসল সারপ্রাইজটা দেখা যাবে', ['surprise', 'surprise_photo']),
                self::sec('💌', '"যখন... তখন খুলো" চিঠি', 'ছোট ছোট খাম, প্রতিটার উপরে কখন খুলতে হবে লেখা', ['open_when']),
                self::sec('❤️', 'শেষ কথা', 'একদম শেষে আপনার নামসহ', ['closing_message', 'from_name']),
            ],
            'reasons-cards' => [
                self::sec('🃏', 'প্রথম পাতা', 'বড় করে শিরোনাম আর মোট কয়টা কারণ, সাথে ছবি', ['title', 'to_name', 'photo', 'intro']),
                self::sec('🔢', 'কারণের কার্ডগুলো', 'নম্বর লেখা কার্ড, ট্যাপ করলে উল্টে গিয়ে কারণটা দেখায়', ['reasons']),
                self::sec('💛', 'শেষ কথা', 'কার্ডগুলোর পরে, আপনার নামসহ', ['closing_message', 'from_name']),
            ],
            'travel-diary' => [
                self::sec('🎫', 'বোর্ডিং পাস', 'একদম উপরে টিকিটের মতো: কোথা থেকে কোথায়, কবে, কারা', ['trip_title', 'from_place', 'to_place', 'travel_date', 'travelers', 'cover_photo']),
                self::sec('🗺️', 'ভ্রমণের গল্প', 'পরিচিতি, তারপর দিন ধরে ধরে প্রতিটা জায়গা, সাথে সিল-ছাপ্পর', ['intro', 'stops']),
                self::sec('📊', 'সংখ্যায় ট্রিপ', 'মজার সংখ্যার কার্ড (যেমন ১২ কাপ চা)', ['stats']),
                self::sec('📸', 'স্ক্র্যাপবুক', 'টেপ দিয়ে লাগানো ছবির মতো গ্যালারি আর শেষ কথা', ['gallery', 'closing_note']),
            ],
            'tribute' => [
                self::sec('🕯️', 'প্রথম পাতা', 'জ্বলন্ত মোমবাতি, নাম, সম্পর্ক আর জন্ম-মৃত্যুর সাল, মাঝখানে ছবি', ['full_name', 'relation', 'born', 'passed', 'portrait']),
                self::sec('📖', 'জীবনের গল্প', 'শান্ত অক্ষরে তাঁর জীবনের কথা', ['life_story']),
                self::sec('🌿', 'যা শিখিয়ে গেছেন', 'পাতার চিহ্ন দেওয়া তালিকা', ['lessons']),
                self::sec('🖼️', 'স্মৃতির ছবি', 'সাদা-কালো থেকে রঙিন হয়ে ওঠা ছবি', ['gallery']),
                self::sec('🪔', 'দোয়া ও প্রদীপ', 'শেষে আপনার প্রার্থনা, পরিবারের নাম আর "প্রদীপ জ্বালান" বাটন', ['prayer', 'from_family']),
            ],
            'festival-greeting' => [
                self::sec('🌙', 'প্রথম পাতা', 'উৎসব অনুযায়ী সাজ (চাঁদ-লণ্ঠন, আলপনা বা প্রদীপ), বড় করে শুভেচ্ছা', ['festival', 'greeting_title', 'to_name']),
                self::sec('💌', 'শুভেচ্ছা বার্তা', 'নকশা করা কার্ডের ভেতরে আপনার বার্তা আর ছবি', ['message', 'photo']),
                self::sec('👨‍👩‍👧', 'কাদের পক্ষ থেকে', 'বার্তার নিচে নাম', ['from_name', 'family']),
                self::sec('🧧', 'সালামি কর্নার', 'মজার "সালামি পাঠাও" অংশ, bKash নম্বর কপি করার বাটনসহ (ঐচ্ছিক)', ['salami_number']),
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* New layouts */
    /* ------------------------------------------------------------------ */

    private static function proposal(): array
    {
        return [
            'label' => 'Proposal',
            'theme' => ['primary' => '#3B0A2A', 'accent' => '#FF6FA4', 'background' => '#1B0614', 'text' => '#FCE9F1', 'font_heading' => 'great-vibes', 'font_body' => 'hind-siliguri', 'font_accent' => 'anek-bangla'],
            'fields' => [
                self::f('their_name', 'যাকে প্রস্তাব দিচ্ছেন, তার নাম', 'text', ['required' => true, 'max_length' => 40, 'placeholder' => 'যেমন: নীলা']),
                self::f('your_name', 'আপনার নাম', 'text', ['required' => true, 'max_length' => 40]),
                self::f('lines', 'মনের কথাগুলো (একটা একটা করে আসবে)', 'repeater', ['required' => true, 'help' => '৩ থেকে ৬টা ছোট লাইন সবচেয়ে ভালো লাগে।', 'options' => ['max' => 10, 'fields' => [['key' => 'text', 'label' => 'লাইন', 'type' => 'textarea']]]]),
                self::f('hero_photo', 'দুজনের একটা ছবি', 'image'),
                self::f('question', 'আসল প্রশ্নটা', 'text', ['max_length' => 80, 'placeholder' => 'তুমি কি আমার হবে?']),
                self::f('yes_message', '"হ্যাঁ" বললে যা দেখাবে', 'textarea', ['required' => true, 'max_length' => 1500]),
                self::f('gallery', 'হ্যাঁ বলার পর দেখানোর ছবি', 'gallery', ['options' => ['max' => 12]]),
                self::f('whatsapp', 'আপনার WhatsApp নম্বর', 'phone', ['help' => 'দিলে "হ্যাঁ" বলার পর সে আপনাকে সরাসরি WhatsApp-এ জানাতে পারবে।']),
            ],
            'sample' => [
                'their_name' => 'নীলা', 'your_name' => 'আরিফ',
                'lines' => [
                    ['text' => 'মনে আছে, টিএসসির সেই বিকেলে তুমি বলেছিলে চা ছাড়া তোমার দিন শুরু হয় না?'],
                    ['text' => 'সেদিন থেকে আমার প্রতিটা সকাল শুরু হয় তোমার কথা ভেবে।'],
                    ['text' => 'তোমার হাসি, তোমার রাগ, তোমার বকবক, সব আমার প্রিয়।'],
                    ['text' => 'তাই আজ একটা কথা জিজ্ঞেস করতে চাই…'],
                ],
                'hero_photo' => self::img('couple-a'),
                'question' => 'তুমি কি সারাজীবন আমার পাশে থাকবে?',
                'yes_message' => "জানতাম তুমি হ্যাঁ বলবে! 💍\n\nআজ থেকে তোমার চায়ের দায়িত্ব আমার। ভালোবাসি, নীলা।",
                'gallery' => [self::img('couple-b'), self::img('flowers'), self::img('couple-c')],
                'whatsapp' => '',
            ],
        ];
    }

    private static function giftSurprise(): array
    {
        return [
            'label' => 'Surprise Gift Box',
            'theme' => ['primary' => '#E63946', 'accent' => '#FFD166', 'background' => '#FFF4E6', 'text' => '#2B1B17', 'font_heading' => 'atma', 'font_body' => 'hind-siliguri', 'font_accent' => 'caveat'],
            'fields' => [
                self::f('to_name', 'সারপ্রাইজটা কার জন্য', 'text', ['required' => true, 'max_length' => 40]),
                self::f('teaser', 'বক্সের উপরের লাইন', 'text', ['max_length' => 100, 'placeholder' => 'তোমার জন্য একটা জিনিস লুকিয়ে রেখেছি…']),
                self::f('surprise', 'আসল সারপ্রাইজ (স্ক্র্যাচ করলে দেখা যাবে)', 'textarea', ['required' => true, 'max_length' => 600, 'placeholder' => 'যেমন: শুক্রবার আমরা কক্সবাজার যাচ্ছি! টিকিট কাটা শেষ 🎟️']),
                self::f('surprise_photo', 'সারপ্রাইজের সাথে একটা ছবি', 'image'),
                self::f('open_when', '"যখন... তখন খুলো" চিঠিগুলো', 'repeater', ['help' => 'যেমন: "যখন মন খারাপ লাগবে" → ভেতরে তাকে হাসানোর একটা কথা।', 'options' => ['max' => 8, 'fields' => [
                    ['key' => 'when', 'label' => 'কখন খুলবে', 'type' => 'text'], ['key' => 'message', 'label' => 'ভেতরের কথা', 'type' => 'textarea'],
                ]]]),
                self::f('closing_message', 'শেষ কথা', 'textarea', ['max_length' => 1200]),
                self::f('from_name', 'আপনার নাম', 'text', ['max_length' => 40]),
            ],
            'sample' => [
                'to_name' => 'তিথি', 'teaser' => 'তোমার জন্য একটা জিনিস লুকিয়ে রেখেছি…',
                'surprise' => 'এই শুক্রবার আমরা সাজেক যাচ্ছি! বাসের টিকিট কাটা শেষ, ব্যাগ গোছাও 🎒',
                'surprise_photo' => self::img('landscape-a'),
                'open_when' => [
                    ['when' => 'যখন মন খারাপ লাগবে', 'message' => 'মনে রেখো, তুমি একা না। একটা কল দাও, আমি ১০ মিনিটে ফুচকা নিয়ে হাজির।'],
                    ['when' => 'যখন ঘুম আসবে না', 'message' => 'জানালা দিয়ে চাঁদটা দেখো। আমিও হয়তো ঠিক তখনই দেখছি।'],
                    ['when' => 'যখন নিজেকে ছোট মনে হবে', 'message' => 'তুমি আমার দেখা সবচেয়ে সাহসী মানুষ। সত্যি।'],
                ],
                'closing_message' => 'তুমি হাসলে আমার দিন ভালো যায়। তাই এই ছোট্ট আয়োজন।',
                'from_name' => 'রাহাত',
            ],
        ];
    }

    private static function reasonsCards(): array
    {
        return [
            'label' => 'Reasons Cards',
            'theme' => ['primary' => '#1D3557', 'accent' => '#F4A261', 'background' => '#F1FAEE', 'text' => '#1D3557', 'font_heading' => 'bricolage', 'font_body' => 'hind-siliguri', 'font_accent' => 'caveat'],
            'fields' => [
                self::f('title', 'শিরোনাম', 'text', ['max_length' => 80, 'placeholder' => 'তোমাকে ভালোবাসার কারণগুলো']),
                self::f('to_name', 'কার জন্য', 'text', ['required' => true, 'max_length' => 40]),
                self::f('photo', 'তার একটা ছবি', 'image'),
                self::f('intro', 'ছোট্ট ভূমিকা', 'textarea', ['max_length' => 600]),
                self::f('reasons', 'কারণগুলো', 'repeater', ['required' => true, 'help' => 'প্রতিটা কারণ একেকটা কার্ড হবে। যত খুশি দিন (সর্বোচ্চ ৬০)।', 'options' => ['max' => 60, 'fields' => [['key' => 'text', 'label' => 'কারণ', 'type' => 'textarea']]]]),
                self::f('closing_message', 'শেষ কথা', 'textarea', ['max_length' => 1000]),
                self::f('from_name', 'আপনার নাম', 'text', ['max_length' => 40]),
            ],
            'sample' => [
                'title' => 'আম্মু, তোমাকে ভালোবাসার কারণগুলো', 'to_name' => 'আম্মু', 'photo' => self::img('mother'),
                'intro' => 'সব কারণ লিখতে গেলে খাতা ফুরিয়ে যাবে। তাই অল্প কয়েকটা এখানে রাখলাম।',
                'reasons' => [
                    ['text' => 'জ্বর হলে সারারাত জেগে মাথায় পানি দাও।'], ['text' => 'আমার প্রিয় খিচুড়িটা শুধু তুমিই এভাবে রাঁধতে পারো।'],
                    ['text' => 'পরীক্ষার আগের রাতে বলো, "যা হবে ভালোই হবে"।'], ['text' => 'আমি দেরি করে ফিরলে বারান্দায় দাঁড়িয়ে থাকো।'],
                    ['text' => 'রাগ করলেও ভাত বেড়ে রাখো।'], ['text' => 'তোমার হাসিটা পুরো বাড়ি আলো করে দেয়।'],
                    ['text' => 'আমার সব বোকামি গোপন রাখো।'], ['text' => 'তুমি আমার প্রথম বন্ধু।'],
                ],
                'closing_message' => 'তুমি না থাকলে আমি "আমি" হতাম না। ভালোবাসি আম্মু।',
                'from_name' => 'তোমার পাগলি মেয়ে',
            ],
        ];
    }

    private static function travelDiary(): array
    {
        return [
            'label' => 'Travel Diary',
            'theme' => ['primary' => '#0B4F6C', 'accent' => '#F6AE2D', 'background' => '#F3EDE2', 'text' => '#20323A', 'font_heading' => 'bricolage', 'font_body' => 'hind-siliguri', 'font_accent' => 'caveat'],
            'fields' => [
                self::f('trip_title', 'ট্রিপের নাম', 'text', ['required' => true, 'max_length' => 80, 'placeholder' => 'চার বন্ধুর বান্দরবান']),
                self::f('from_place', 'কোথা থেকে', 'text', ['max_length' => 30, 'placeholder' => 'ঢাকা']),
                self::f('to_place', 'কোথায়', 'text', ['required' => true, 'max_length' => 30, 'placeholder' => 'বান্দরবান']),
                self::f('travel_date', 'কবে', 'text', ['max_length' => 40, 'placeholder' => 'ডিসেম্বর ২০২৫']),
                self::f('travelers', 'কারা গিয়েছিলেন', 'text', ['max_length' => 160, 'help' => 'নামগুলো কমা দিয়ে আলাদা করুন']),
                self::f('cover_photo', 'কভার ছবি', 'image', ['required' => true]),
                self::f('intro', 'ট্রিপের শুরুর কথা', 'textarea', ['max_length' => 1500]),
                self::f('stops', 'দিন ধরে ধরে গল্প', 'repeater', ['options' => ['max' => 12, 'fields' => [
                    ['key' => 'day', 'label' => 'দিন', 'type' => 'text'], ['key' => 'place', 'label' => 'জায়গা', 'type' => 'text'], ['key' => 'story', 'label' => 'কী হয়েছিল', 'type' => 'textarea'],
                ]]]),
                self::f('stats', 'মজার সংখ্যা', 'repeater', ['help' => 'যেমন: "৩৭" কাপ চা, "৪" বার পথ হারানো', 'options' => ['max' => 4, 'fields' => [
                    ['key' => 'value', 'label' => 'সংখ্যা', 'type' => 'text'], ['key' => 'label', 'label' => 'কীসের', 'type' => 'text'],
                ]]]),
                self::f('gallery', 'ট্রিপের ছবি', 'gallery', ['options' => ['max' => 30]]),
                self::f('closing_note', 'শেষ কথা', 'textarea', ['max_length' => 800]),
            ],
            'sample' => [
                'trip_title' => 'চার বন্ধুর বান্দরবান', 'from_place' => 'ঢাকা', 'to_place' => 'বান্দরবান', 'travel_date' => 'ডিসেম্বর ২০২৫',
                'travelers' => 'সাকিব, তানহা, রাফি, মিতু', 'cover_photo' => self::img('landscape-b'),
                'intro' => 'রাত ১১টার বাস, এক ব্যাগ চিপস আর অনেক প্ল্যান। প্ল্যানের অর্ধেকও মানা হয়নি, কিন্তু ট্রিপটা হয়েছিল জীবনের সেরা।',
                'stops' => [
                    ['day' => 'দিন ১', 'place' => 'নীলগিরি', 'story' => 'মেঘের উপরে দাঁড়িয়ে সূর্যোদয়। কেউ কোনো কথা বলেনি।'],
                    ['day' => 'দিন ২', 'place' => 'নাফাখুম', 'story' => 'নৌকা, হাঁটা, আবার নৌকা। পা ব্যথা, মন ভালো।'],
                    ['day' => 'দিন ৩', 'place' => 'বগা লেক', 'story' => 'রাতে আগুনের পাশে গান, আকাশ ভর্তি তারা।'],
                ],
                'stats' => [['value' => '৩৭', 'label' => 'কাপ চা'], ['value' => '৪', 'label' => 'বার পথ হারানো'], ['value' => '১২০০', 'label' => 'ছবি তোলা']],
                'gallery' => array_map(function ($n, $c) {
                    $i = self::img($n);
                    $i['caption'] = $c;

                    return $i;
                },
                    ['landscape-a', 'friends', 'landscape-c', 'art-a'], ['মেঘের সমুদ্র', 'চার পাগল', 'ঝর্নার পথে', 'রাতের আগুন']),
                'closing_note' => 'পরের ট্রিপ? সেন্ট মার্টিন। ডেট ঠিক হয়নি, কিন্তু যাবই।',
            ],
        ];
    }

    private static function tribute(): array
    {
        return [
            'label' => 'In Loving Memory',
            'theme' => ['primary' => '#2F3A32', 'accent' => '#C9A66B', 'background' => '#F4F0E8', 'text' => '#2B2B26', 'font_heading' => 'cormorant', 'font_body' => 'noto-serif-bengali', 'font_accent' => 'tiro-bangla'],
            'fields' => [
                self::f('full_name', 'তাঁর নাম', 'text', ['required' => true, 'max_length' => 80]),
                self::f('relation', 'আপনাদের কাছে তিনি', 'text', ['max_length' => 60, 'placeholder' => 'আমাদের প্রিয় আব্বু']),
                self::f('born', 'জন্ম (সাল বা তারিখ)', 'text', ['max_length' => 30, 'placeholder' => '১৯৫২']),
                self::f('passed', 'মৃত্যু (সাল বা তারিখ)', 'text', ['max_length' => 30, 'placeholder' => '২০২৪']),
                self::f('portrait', 'তাঁর ছবি', 'image', ['required' => true]),
                self::f('life_story', 'তাঁর জীবনের গল্প', 'textarea', ['required' => true, 'max_length' => 5000]),
                self::f('lessons', 'যা শিখিয়ে গেছেন', 'repeater', ['options' => ['max' => 10, 'fields' => [['key' => 'text', 'label' => 'শিক্ষা', 'type' => 'text']]]]),
                self::f('gallery', 'স্মৃতির ছবি', 'gallery', ['options' => ['max' => 30]]),
                self::f('prayer', 'দোয়া / প্রার্থনা', 'textarea', ['max_length' => 800]),
                self::f('from_family', 'কাদের পক্ষ থেকে', 'text', ['max_length' => 120, 'placeholder' => 'রহমান পরিবার']),
            ],
            'sample' => [
                'full_name' => 'আব্দুর রহমান', 'relation' => 'আমাদের প্রিয় আব্বু', 'born' => '১৯৫২', 'passed' => '২০২৪',
                'portrait' => self::img('elder'),
                'life_story' => "কুমিল্লার এক ছোট্ট গ্রামে জন্ম। স্কুলশিক্ষক হিসেবে চল্লিশ বছর কাটিয়েছেন, হাজারো ছাত্রছাত্রীর প্রিয় \"রহমান স্যার\"।\n\nভোরবেলা হাঁটা, রেডিওতে খবর আর বিকেলে বাগানের যত্ন, এই ছিল তাঁর দিন। কখনো উঁচু গলায় কথা বলতে শুনিনি।",
                'lessons' => [['text' => 'সৎ থাকো, বাকিটা আল্লাহ দেখবেন।'], ['text' => 'বই কখনো ধার দিয়ে ফেরত চাইতে লজ্জা পেয়ো না।'], ['text' => 'মানুষকে সম্মান দাও, সে যেই হোক।']],
                'gallery' => [self::img('landscape-c'), self::img('landscape-a'), self::img('flowers')],
                'prayer' => 'আল্লাহ তাঁকে জান্নাতুল ফেরদৌস দান করুন।',
                'from_family' => 'রহমান পরিবার',
            ],
        ];
    }

    private static function festivalGreeting(): array
    {
        return [
            'label' => 'Festival Greeting',
            'theme' => ['primary' => '#0F3D3E', 'accent' => '#E8B931', 'background' => '#06201F', 'text' => '#F6F1E1', 'font_heading' => 'galada', 'font_body' => 'hind-siliguri', 'font_accent' => 'anek-bangla'],
            'fields' => [
                self::f('festival', 'কোন উৎসব', 'select', ['required' => true, 'options' => ['choices' => ['ঈদ মোবারক', 'শুভ নববর্ষ', 'শারদীয় শুভেচ্ছা', 'শুভ দীপাবলি', 'বড়দিনের শুভেচ্ছা', 'হ্যাপি নিউ ইয়ার']]]),
                self::f('greeting_title', 'বড় করে শুভেচ্ছার লাইন', 'text', ['max_length' => 80, 'placeholder' => 'ঈদের খুশি ছড়িয়ে পড়ুক সবখানে']),
                self::f('to_name', 'কার জন্য (ঐচ্ছিক)', 'text', ['max_length' => 60, 'placeholder' => 'প্রিয় নানুভাই']),
                self::f('message', 'শুভেচ্ছা বার্তা', 'textarea', ['required' => true, 'max_length' => 1500]),
                self::f('photo', 'একটা ছবি', 'image'),
                self::f('from_name', 'কার পক্ষ থেকে', 'text', ['required' => true, 'max_length' => 60]),
                self::f('family', 'পরিবারের অন্যরা', 'text', ['max_length' => 160, 'help' => 'নামগুলো কমা দিয়ে আলাদা করুন']),
                self::f('salami_number', 'সালামির bKash নম্বর 😄', 'phone', ['help' => 'মজার জন্য। দিলে পেজে "সালামি পাঠাও" অংশ দেখাবে।']),
            ],
            'sample' => [
                'festival' => 'ঈদ মোবারক', 'greeting_title' => 'ঈদের খুশি ছড়িয়ে পড়ুক সবখানে', 'to_name' => 'প্রিয় বন্ধুরা',
                'message' => "রমজানের শেষে আবার এলো খুশির ঈদ। সেমাই, নতুন জামা আর সবার হাসিমুখ, এই দিনটা হোক আপনার পরিবারের জন্য অনেক আনন্দের।\n\nঈদ মোবারক!",
                'photo' => self::img('friends'), 'from_name' => 'সাকিব ও পরিবার', 'family' => 'আম্মু, আব্বু, তানহা, ছোট্ট রাইয়ান',
                'salami_number' => '01700000000',
            ],
        ];
    }
}
