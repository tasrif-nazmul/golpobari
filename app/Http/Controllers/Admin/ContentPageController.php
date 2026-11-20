<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ContentPageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:manage-catalog')];
    }

    public function index()
    {
        return view('admin.content-pages.index', ['items' => ContentPage::orderBy('slug')->get()]);
    }

    public function edit(ContentPage $contentPage)
    {
        return view('admin.content-pages.form', ['item' => $contentPage]);
    }

    public function update(Request $request, ContentPage $contentPage)
    {
        $contentPage->update($request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:50000'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ]));
        ActivityLogger::log('content_page.updated', $contentPage, $contentPage->slug);

        return back()->with('success', 'Page saved.');
    }
}
