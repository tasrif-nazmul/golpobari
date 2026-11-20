<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FaqController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:manage-catalog')];
    }

    public function index()
    {
        return view('admin.faqs.index', ['items' => Faq::orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.faqs.form', ['item' => new Faq(['is_active' => true, 'sort_order' => (int) Faq::max('sort_order') + 1])]);
    }

    public function store(Request $request)
    {
        Faq::create($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ added.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.form', ['item' => $faq]);
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ saved.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return back()->with('success', 'FAQ deleted.');
    }

    private function validated(Request $request): array
    {
        $d = $request->validate(['question' => ['required', 'string', 'max:255'], 'answer' => ['required', 'string', 'max:3000'], 'sort_order' => ['nullable', 'integer', 'min:0']]);
        $d['is_active'] = $request->boolean('is_active');
        $d['sort_order'] = (int) ($d['sort_order'] ?? 0);

        return $d;
    }
}
