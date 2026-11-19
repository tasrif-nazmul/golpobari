<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Package;
use App\Models\Template;
use App\Models\Testimonial;
use App\Support\Settings;

class HomeController extends Controller
{
    public function __invoke(Settings $settings)
    {
        $categories = Category::active()->withCount(['templates as active_templates_count' => fn ($q) => $q->where('is_active', true)])->get();

        $templates = Template::active()->with('category')->where('is_featured', true)->limit(6)->get();
        if ($templates->count() < 3) {
            $templates = Template::active()->with('category')->limit(6)->get();
        }

        return view('site.home', [
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
