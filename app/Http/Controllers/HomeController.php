<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Package;
use App\Models\Template;
use App\Models\Testimonial;
use App\Support\Settings;
use App\Support\TemplateRegistry;

class HomeController extends Controller
{
    public function __invoke(Settings $settings)
    {
        $categories = Category::active()->withCount(['templates as active_templates_count' => fn ($q) => $q->where('is_active', true)])->get();

        $templates = Template::active()->with('category')->where('is_featured', true)->limit(9)->get();
        if ($templates->count() < 3) {
            $templates = Template::active()->with('category')->limit(6)->get();
        }

        // Lead with the templates that open like a letter: they show what GolpoBari is about.
        $first = ['romantic-story', 'event-invitation', 'birthday-celebration', 'memory-timeline', 'our-journey', 'love-letter', 'eid-mubarak', 'will-you-be-mine', 'travel-diary'];
        $templates = $templates->sortBy(fn ($t) => ($i = array_search($t->slug, $first, true)) === false ? 99 : $i)->values();

        $hero = Template::active()->with('category')->whereIn('slug', ['romantic-story', 'event-invitation', 'memory-timeline', 'birthday-celebration', 'eid-mubarak'])->get()
            ->merge($templates)->unique('id')->filter(fn ($t) => $t->phoneShots())->take(5)
            ->map(fn ($t) => $t->phoneShots() + ['name' => $t->name, 'payload' => $t->previewPayload()])->values();

        return view('site.home', [
            'heroItems' => $hero,
            'tracks' => collect(TemplateRegistry::MUSIC)->map(fn ($t, $k) => $t + ['key' => $k, 'url' => TemplateRegistry::musicUrl($k)])->values(),
            'categories' => $categories,
            'templates' => $templates,
            'packages' => Package::active()->get(),
            'testimonials' => Testimonial::active()->limit(6)->get(),
            'faqs' => Faq::active()->limit(6)->get(),
            'seo' => [
                'title' => $settings->get('seo_title'),
                'description' => $settings->get('seo_description'),
                'canonical' => url('/'),
                'jsonld' => [
                    '@context' => 'https://schema.org',
                    '@graph' => [
                        ['@type' => 'Organization', 'name' => $settings->get('site_name'), 'url' => url('/'), 'logo' => $settings->fileUrl('logo_path') ?: asset('images/apple-touch-icon.png'),
                            'sameAs' => array_values(array_filter([$settings->get('facebook_url'), $settings->get('instagram_url'), $settings->get('youtube_url')]))],
                        ['@type' => 'WebSite', 'name' => $settings->get('site_name'), 'alternateName' => $settings->get('site_name_bn'), 'url' => url('/'), 'inLanguage' => 'bn-BD'],
                    ],
                ],
            ],
        ]);
    }
}
