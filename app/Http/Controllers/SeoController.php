<?php

namespace App\Http\Controllers;

use App\Enums\PageStatus;
use App\Enums\PageVisibility;
use App\Models\Category;
use App\Models\Page;
use App\Models\Template;

class SeoController extends Controller
{
    public function sitemap()
    {
        $urls = collect([
            ['loc' => url('/'), 'priority' => '1.0'],
            ['loc' => route('templates.index'), 'priority' => '0.9'],
            ['loc' => route('categories.index'), 'priority' => '0.8'],
            ['loc' => route('pricing'), 'priority' => '0.8'],
            ['loc' => route('faq'), 'priority' => '0.6'],
            ['loc' => route('contact'), 'priority' => '0.5'],
            ['loc' => route('about'), 'priority' => '0.5'],
            ['loc' => route('privacy'), 'priority' => '0.3'],
            ['loc' => route('terms'), 'priority' => '0.3'],
            ['loc' => route('refund'), 'priority' => '0.3'],
        ]);

        Category::active()->get()->each(fn ($c) => $urls->push(['loc' => route('categories.show', $c), 'lastmod' => $c->updated_at, 'priority' => '0.7']));
        Template::active()->get()->each(fn ($t) => $urls->push(['loc' => route('templates.show', $t), 'lastmod' => $t->updated_at, 'priority' => '0.7']));
        Page::where('status', PageStatus::Active)->where('visibility', PageVisibility::Public)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->select(['slug', 'updated_at'])->orderBy('id')->limit(40000)->get()
            ->each(fn ($p) => $urls->push(['loc' => url('/'.$p->slug), 'lastmod' => $p->updated_at, 'priority' => '0.5']));

        return response()->view('seo.sitemap', ['urls' => $urls], 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots()
    {
        $admin = config('golpobari.admin_path');
        $body = implode("\n", [
            'User-agent: *',
            'Disallow: /'.$admin,
            'Disallow: /order',
            'Disallow: /track-order',
            'Disallow: /templates/*/render',
            'Allow: /',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($body)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
