<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TestimonialController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:manage-catalog')];
    }

    public function index()
    {
        return view('admin.testimonials.index', ['items' => Testimonial::orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.testimonials.form', ['item' => new Testimonial(['is_active' => true, 'rating' => 5])]);
    }

    public function store(Request $request, ImageService $images)
    {
        Testimonial::create($this->validated($request, $images));

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial added.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', ['item' => $testimonial]);
    }

    public function update(Request $request, Testimonial $testimonial, ImageService $images)
    {
        $testimonial->update($this->validated($request, $images));

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial saved.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return back()->with('success', 'Testimonial deleted.');
    }

    private function validated(Request $request, ImageService $images): array
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:80'], 'role' => ['nullable', 'string', 'max:80'],
            'quote' => ['required', 'string', 'max:600'], 'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'sort_order' => ['nullable', 'integer', 'min:0'], 'avatar' => ['nullable', 'file', 'max:4096', 'mimes:jpg,jpeg,png,webp'],
        ]);
        $d['is_active'] = $request->boolean('is_active');
        $d['sort_order'] = (int) ($d['sort_order'] ?? 0);
        if ($request->hasFile('avatar')) {
            $d['avatar_path'] = $images->storePublic($request->file('avatar'), 'testimonials', 200);
        }
        unset($d['avatar']);

        return $d;
    }
}
