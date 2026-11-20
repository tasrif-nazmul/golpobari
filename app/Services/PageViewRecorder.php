<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageView;
use Illuminate\Http\Request;

/**
 * Privacy-light analytics: no raw IPs are stored, only a daily-rotating hash.
 */
class PageViewRecorder
{
    private const BOTS = '/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|preview|curl|wget|python|headless|lighthouse/i';

    public function record(Page $page, Request $request): void
    {
        $ua = (string) $request->userAgent();
        if ($ua === '' || preg_match(self::BOTS, $ua) || auth('admin')->check()) {
            return;
        }

        $hash = hash('sha256', $request->ip().'|'.$ua.'|'.now()->toDateString().'|'.config('app.key'));
        $sessionKey = 'pv.'.$page->id;
        if ($request->hasSession() && $request->session()->get($sessionKey) === now()->toDateString()) {
            return; // one view per visitor per day
        }

        $ref = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);
        if ($ref === $request->getHost()) {
            $ref = null;
        }

        PageView::create([
            'page_id' => $page->id,
            'visitor_hash' => $hash,
            'device' => preg_match('/tablet|ipad/i', $ua) ? 'tablet' : (preg_match('/mobi|android|iphone/i', $ua) ? 'mobile' : 'desktop'),
            'referrer_host' => $ref ? mb_substr($ref, 0, 190) : null,
            'viewed_at' => now(),
        ]);
        Page::whereKey($page->id)->update(['views_count' => \DB::raw('views_count + 1'), 'last_viewed_at' => now()]);

        if ($request->hasSession()) {
            $request->session()->put($sessionKey, now()->toDateString());
        }
    }
}
