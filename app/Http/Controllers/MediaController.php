<?php

namespace App\Http\Controllers;

use App\Enums\PageVisibility;
use App\Models\PageMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Serves customer photos from outside the web root after an access check.
 */
class MediaController extends Controller
{
    public function show(Request $request, PageMedia $media, string $variant)
    {
        [$allowed, $public] = $this->access($request, $media);
        abort_unless($allowed, 404);

        $path = $variant === 'thumb' && $media->thumb_path ? $media->thumb_path : $media->path;
        $disk = Storage::disk('uploads');
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Content-Type' => $media->mime ?: 'image/webp',
            'Cache-Control' => $public ? 'public, max-age=2592000, immutable' : 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
        ]);
    }

    /** @return array{0: bool, 1: bool} [allowed, cacheable publicly] */
    private function access(Request $request, PageMedia $media): array
    {
        if (auth('admin')->check()) {
            return [true, false];
        }

        if ($media->draft_token && ! $media->order_id) {
            $token = (string) $request->cookie('gb_draft');

            return [$token !== '' && hash_equals($media->draft_token, $token), false];
        }

        $page = $media->page;
        if (! $page || ! $page->isLive()) {
            return [false, false];
        }
        if ($page->visibility === PageVisibility::Private) {
            return [(bool) $request->session()->get('unlocked.'.$page->id), false];
        }

        return [true, true];
    }
}
