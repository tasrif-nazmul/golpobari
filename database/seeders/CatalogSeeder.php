<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Template;
use App\Support\TemplateRegistry;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'someone-special', 'name' => 'For Someone Special', 'name_bn' => 'প্রিয় মানুষের জন্য', 'emoji' => '❤️', 'accent_color' => '#E2553F',
                'tagline' => 'যে কথাগুলো মুখে বলা হয় না', 'description' => 'Girlfriend, boyfriend, best friend কিংবা long distance love, প্রিয় মানুষকে একটা পুরো ওয়েবসাইট উপহার দিন।',
                'occasions' => ['Girlfriend', 'Boyfriend', 'Best Friend', 'Proposal', 'Anniversary', 'Long Distance', 'Our Story', 'Love Letter', 'Special Message']],
            ['slug' => 'birthday', 'name' => 'Birthday', 'name_bn' => 'জন্মদিন', 'emoji' => '🎂', 'accent_color' => '#F2A93B',
                'tagline' => 'কেকের চেয়েও মিষ্টি একটা সারপ্রাইজ', 'description' => 'ছবি, স্মৃতি আর বন্ধুদের শুভেচ্ছা দিয়ে সাজানো জন্মদিনের পেজ।',
                'occasions' => ['Birthday Celebration', 'Birthday Story', 'Birthday Memories', 'Surprise Page']],
            ['slug' => 'anniversary', 'name' => 'Anniversary', 'name_bn' => 'বিবাহবার্ষিকী', 'emoji' => '💍', 'accent_color' => '#B8860B',
                'tagline' => 'একসাথে পথচলার গল্প', 'description' => 'আপনাদের যাত্রার প্রতিটা অধ্যায়, একটা সুন্দর টাইমলাইনে।',
                'occasions' => ['Our Journey', 'Timeline', 'Memories', 'Love Story']],
            ['slug' => 'personal', 'name' => 'Personal', 'name_bn' => 'নিজের পরিচয়', 'emoji' => '👤', 'accent_color' => '#2F5D50',
                'tagline' => 'আপনার নামে, আপনার ঠিকানা', 'description' => 'Portfolio, CV, student profile কিংবা personal brand। নিজের একটা পেশাদার ওয়েবসাইট।',
                'occasions' => ['Personal Profile', 'Portfolio', 'CV / Resume', 'Student', 'Freelancer', 'Developer', 'Photographer', 'Artist', 'Designer', 'Personal Brand']],
            ['slug' => 'business', 'name' => 'Business', 'name_bn' => 'ব্যবসা', 'emoji' => '💼', 'accent_color' => '#1F6F8B',
                'tagline' => 'ছোট ব্যবসার বড় পরিচয়', 'description' => 'দোকান, ক্যাফে, সার্ভিস বা পণ্য। Facebook পেজের পাশাপাশি নিজের একটা ওয়েবসাইট।',
                'occasions' => ['Small Business', 'Service', 'Restaurant / Cafe', 'Product Showcase', 'Freelancer', 'Agency', 'Personal Business']],
            ['slug' => 'events', 'name' => 'Events', 'name_bn' => 'অনুষ্ঠান', 'emoji' => '🎉', 'accent_color' => '#8E3B76',
                'tagline' => 'দাওয়াতটা হোক মনে রাখার মতো', 'description' => 'বিয়ে, রিইউনিয়ন, জন্মদিনের পার্টি। সব তথ্য এক লিংকে।',
                'occasions' => ['Birthday Event', 'Wedding', 'Invitation', 'Reunion', 'Special Event']],
            ['slug' => 'festivals', 'name' => 'Festivals', 'name_bn' => 'উৎসব', 'emoji' => '🌙', 'accent_color' => '#0F766E',
                'tagline' => 'শুভেচ্ছাটা হোক একটু আলাদা', 'description' => 'ঈদ, পহেলা বৈশাখ, পূজা কিংবা নতুন বছর। প্রিয়জনদের জন্য একটা শুভেচ্ছার ওয়েবসাইট, সাথে মজার সালামি কর্নার।',
                'occasions' => ['Eid', 'Pohela Boishakh', 'Puja', 'New Year', 'Christmas']],
            ['slug' => 'memories', 'name' => 'Memories', 'name_bn' => 'স্মৃতি', 'emoji' => '📖', 'accent_color' => '#5B6C8F',
                'tagline' => 'ছবিগুলো ফোনের গ্যালারিতে হারিয়ে না যাক', 'description' => 'ভ্রমণ, পরিবার, বন্ধুত্ব কিংবা প্রিয় কাউকে স্মরণ। স্মৃতির একটা অ্যালবাম।',
                'occasions' => ['Travel Memories', 'Family Memories', 'Friendship Memories', 'Life Story', 'Photo Memories', 'Tribute / Memorial']],
        ];

        foreach ($categories as $i => $c) {
            Category::firstOrCreate(['slug' => $c['slug']], $c + ['sort_order' => $i + 1, 'is_active' => true]);
        }

        $cat = fn ($slug) => Category::where('slug', $slug)->value('id');

        $templates = [
            ['personal', 'minimal-portfolio', 'Minimal Portfolio', 'minimal-portfolio', 'শান্ত, পরিষ্কার, কাজের কথা', 'সাদা জায়গা আর সুন্দর টাইপোগ্রাফি। designer, writer বা যেকোনো পেশাজীবীর জন্য।', true],
            ['personal', 'modern-professional', 'Modern Professional', 'modern-professional', 'CV যখন ওয়েবসাইট', 'অভিজ্ঞতা, দক্ষতা আর অর্জন সাজানো পেশাদার প্রোফাইল। চাকরির আবেদনে লিংকটা দিন।', false],
            ['personal', 'creative-portfolio', 'Creative Portfolio', 'creative-portfolio', 'কাজ দেখাক কথা', 'বড় ছবি, সাহসী রং। শিল্পী, ফটোগ্রাফার ও ডিজাইনারদের জন্য।', true],
            ['someone-special', 'romantic-story', 'Romantic Story', 'romantic-story', 'আপনাদের গল্প, ভালোবাসার ভাষায়', 'একসাথে কত দিন, প্রিয় স্মৃতি, ছবি আর মনের কথা। প্রিয় মানুষের জন্য সবচেয়ে জনপ্রিয় টেমপ্লেট।', true],
            ['someone-special', 'memory-timeline', 'Memory Timeline', 'memory-timeline', 'প্রথম দেখা থেকে আজ পর্যন্ত', 'তারিখ ধরে ধরে সাজানো আপনাদের গল্প, প্রতিটা মুহূর্তের সাথে ছবি।', false],
            ['someone-special', 'love-letter', 'Love Letter', 'love-letter', 'একটা চিঠি, যেটা খুলতে হয়', 'খাম খুললেই বেরিয়ে আসে আপনার হাতে লেখা মতো একটা চিঠি।', true],
            ['birthday', 'birthday-celebration', 'Birthday Celebration', 'birthday-celebration', 'জন্মদিনে একটা আস্ত ওয়েবসাইট', 'কনফেটি, কাউন্টডাউন, ছবি আর বন্ধুদের শুভেচ্ছা। সারপ্রাইজটা হোক মনে রাখার মতো।', true],
            ['anniversary', 'our-journey', 'Our Journey', 'our-journey', 'অধ্যায় ধরে ধরে পথচলা', 'গাঢ় সবুজ আর সোনালি রঙে আপনাদের দাম্পত্যের গল্প, বছর গোনার ঘড়িসহ।', true],
            ['business', 'clean-business', 'Clean Business', 'clean-business', 'দোকান থেকে ক্যাফে', 'সার্ভিস, দাম, ছবি, ঠিকানা, ম্যাপ আর WhatsApp বাটন। ছোট ব্যবসার জন্য যা যা দরকার।', false],
            ['business', 'modern-service', 'Modern Service', 'modern-service', 'ফ্রিল্যান্সার ও এজেন্সির জন্য', 'ডার্ক থিম, বোল্ড লেখা। সার্ভিস, কাজের ধাপ আর ক্লায়েন্ট রিভিউ।', false],
            ['memories', 'photo-memories', 'Photo Memories', 'photo-memories', 'স্মৃতির অ্যালবাম', 'ফিল্ম প্রিন্টের মত ছবি, প্রতিটার নিচে ছোট্ট নোট। ভ্রমণ বা বন্ধুদের জন্য।', true],
            ['events', 'event-invitation', 'Event Invitation', 'event-invitation', 'দাওয়াতপত্র, অনলাইনে', 'তারিখ, সময়, ভেন্যু, ম্যাপ আর অনুষ্ঠানসূচি। বিয়ে বা যেকোনো আয়োজনের জন্য।', false],
            ['memories', 'tribute', 'In Loving Memory', 'tribute', 'প্রিয় মানুষকে স্মরণ', 'জ্বলন্ত মোমবাতি, শান্ত রং, জীবনের গল্প আর "প্রদীপ জ্বালান" বাটন। প্রিয় কাউকে সম্মান জানানোর পেজ।', false],
            ['someone-special', 'will-you-be-mine', 'Will You Be Mine?', 'proposal', 'প্রস্তাবটা হোক অবিস্মরণীয়', 'মনের কথা একটা একটা করে আসে, তারপর আংটি আর প্রশ্ন। "না" বাটনটা ধরাই যায় না!', true],
            ['someone-special', 'surprise-gift-box', 'Surprise Gift Box', 'gift-surprise', 'বক্স খোলো, ঘষে দেখো', 'নড়তে থাকা গিফট বক্স, স্ক্র্যাচ কার্ডে লুকানো সারপ্রাইজ আর "যখন মন খারাপ, তখন খুলো" চিঠি।', true],
            ['someone-special', 'reasons-i-love-you', 'Reasons I Love You', 'reasons-cards', 'ভালোবাসার কারণগুলো, কার্ডে কার্ডে', 'নম্বর লেখা কার্ড, ট্যাপ করলে উল্টে যায়। প্রেমিক-প্রেমিকা, মা-বাবা বা বন্ধুর জন্য।', false],
            ['memories', 'travel-diary', 'Travel Diary', 'travel-diary', 'বোর্ডিং পাস থেকে স্ক্র্যাপবুক', 'টিকিটের মতো কভার, দিন ধরে ধরে গল্প, মজার সংখ্যা আর ট্রিপের ছবি।', true],
            ['festivals', 'eid-mubarak', 'Eid Mubarak', 'festival-greeting', 'চাঁদ, লণ্ঠন আর সালামি', 'দোলানো লণ্ঠন আর বাঁকা চাঁদের নিচে আপনার ঈদের শুভেচ্ছা। সাথে মজার সালামি কর্নার।', true],
            ['festivals', 'shubho-noboborsho', 'Shubho Noboborsho', 'festival-greeting', 'এসো হে বৈশাখ', 'লাল-হলুদ রং আর ঘুরতে থাকা আলপনায় নতুন বছরের শুভেচ্ছা।', false,
                ['primary' => '#4D000A', 'accent' => '#FFD23F', 'background' => '#7A0010', 'text' => '#FFF4E0', 'font_heading' => 'galada', 'font_accent' => 'anek-bangla'],
                ['sample' => ['festival' => 'শুভ নববর্ষ', 'greeting_title' => 'এসো হে বৈশাখ, এসো এসো', 'to_name' => 'প্রিয় সবাইকে',
                    'message' => "পুরনো বছরের সব ক্লান্তি মুছে যাক। পান্তা-ইলিশ, লাল-সাদা শাড়ি-পাঞ্জাবি আর মঙ্গল শোভাযাত্রার রঙে রাঙুক নতুন বছর।\n\nশুভ নববর্ষ!",
                    'from_name' => 'মিতু ও পরিবার', 'family' => 'আব্বু, আম্মু, রাফি', 'salami_number' => '']]],
            ['events', 'gaye-holud', 'Gaye Holud', 'event-invitation', 'হলুদ ছোঁয়ার দাওয়াত', 'হলুদ-সবুজ রঙে, আলপনা আর ফুলে সাজানো গায়ে হলুদের অনলাইন দাওয়াতপত্র।', true,
                ['primary' => '#2E6B2F', 'accent' => '#F4B400', 'background' => '#FFF6D6', 'text' => '#2A2A12', 'font_heading' => 'galada', 'font_accent' => 'galada'],
                ['sample' => ['event_title' => 'রিয়ার গায়ে হলুদ', 'hosts' => 'আহমেদ পরিবার', 'event_time' => 'বিকেল ৪টা',
                    'message' => 'হলুদ, মেহেদি আর গানে গানে কাটবে সন্ধ্যা। আপনাদের উপস্থিতিতেই পূর্ণ হবে আমাদের আনন্দ।',
                    'schedule' => [['time' => '৪:০০', 'title' => 'হলুদ ছোঁয়া'], ['time' => '৬:০০', 'title' => 'মেহেদি ও গান'], ['time' => '৮:০০', 'title' => 'রাতের খাবার']],
                    'dress_code' => 'হলুদ / সবুজ']]],
        ];

        foreach ($templates as $i => $t) {
            [$catSlug, $slug, $name, $layout, $tagline, $desc, $featured] = $t;
            $template = Template::firstOrCreate(['slug' => $slug], [
                'category_id' => $cat($catSlug),
                'name' => $name,
                'layout' => $layout,
                'tagline' => $tagline,
                'description' => $desc,
                'preview_path' => file_exists(public_path("images/previews/{$slug}.webp")) ? "images/previews/{$slug}.webp" : null,
                'theme' => $t[7] ?? null,
                'design' => $t[8] ?? null,
                'max_photos' => 20,
                'is_featured' => $featured,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);

            if ($template->fields()->count() === 0) {
                foreach (TemplateRegistry::layout($layout)['fields'] as $j => $f) {
                    $template->fields()->create([
                        'key' => $f['key'], 'label' => $f['label'], 'type' => $f['type'], 'section' => $f['section'],
                        'placeholder' => $f['placeholder'], 'help' => $f['help'], 'options' => $f['options'] ?: null,
                        'is_required' => $f['required'], 'max_length' => $f['max_length'], 'sort_order' => $j + 1,
                    ]);
                }
            }
        }
    }
}
