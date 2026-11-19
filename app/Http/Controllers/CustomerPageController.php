<?php

namespace App\Http\Controllers;

use App\Enums\PageStatus;
use App\Enums\PageVisibility;
use App\Models\Page;
use App\Services\PageRenderer;
use App\Services\PageViewRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class CustomerPageController extends Controller
{
    public function show(Request $request, string $slug, PageRenderer $renderer, PageViewRecorder $views)
    {
        $page = Page::with(['template.fields', 'media'])->where('slug', $slug)->first();

        if (! $page || in_array($page->status, [PageStatus::Draft, PageStatus::Pending, PageStatus::Archived], true)) {
            abort(404);
        }

        if ($page->status === PageStatus::Suspended) {
            return response()->view('site.page-unavailable', ['page' => $page, 'seo' => ['title' => 'পেজটি এখন বন্ধ · GolpoBari', 'noindex' => true]], 410);
        }

        // Expiry is checked live, so pages close on time even if the scheduler isn't running.
        if ($page->status === PageStatus::Expired || $page->hasExpired()) {
            return response()->view('site.page-expired', ['page' => $page, 'seo' => ['title' => $page->title.' · GolpoBari', 'noindex' => true]], 410);
        }

        if ($page->visibility === PageVisibility::Private && ! $request->session()->get('unlocked.'.$page->id)) {
            return response()->view('site.page-locked', ['page' => $page, 'seo' => ['title' => 'Private page · GolpoBari', 'noindex' => true]]);
        }

        $views->record($page, $request);

        return response($renderer->forPage($page)->render())
            ->header('Cache-Control', 'private, no-cache')
            ->header('X-Robots-Tag', $page->visibility === PageVisibility::Public ? 'index, follow' : 'noindex, nofollow');
    }

    public function unlock(Request $request, string $slug)
    {
        $page = Page::where('slug', $slug)->where('visibility', PageVisibility::Private)->firstOrFail();
        $request->validate(['access_code' => ['required', 'string', 'max:64']]);

        if (! $page->access_code || ! Hash::check($request->input('access_code'), $page->access_code)) {
            return back()->withErrors(['access_code' => 'কোডটি মিলছে না। আবার চেষ্টা করুন।']);
        }

        $request->session()->regenerate();
        $request->session()->put('unlocked.'.$page->id, true);

        return redirect('/'.$page->slug);
    }
}
