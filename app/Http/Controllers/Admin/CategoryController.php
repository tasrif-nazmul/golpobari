<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageService;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:manage-catalog')];
    }

    public function index()
    {
        return view('admin.categories.index', ['categories' => Category::withCount('templates')->orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category(['is_active' => true, 'sort_order' => (int) Category::max('sort_order') + 1])]);
    }

    public function store(Request $request, ImageService $images)
    {
        $category = Category::create($this->validated($request, $images));
        ActivityLogger::log('category.created', $category, $category->name);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category, ImageService $images)
    {
        $category->update($this->validated($request, $images, $category));
        ActivityLogger::log('category.updated', $category, $category->name);

        return redirect()->route('admin.categories.index')->with('success', 'Category saved.');
    }

    public function destroy(Category $category)
    {
        if ($category->templates()->exists()) {
            return back()->with('error', 'Move or delete this category\'s templates first, or just deactivate it.');
        }
        ActivityLogger::log('category.deleted', $category, $category->name);
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    private function validated(Request $request, ImageService $images, ?Category $category = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'name_bn' => ['nullable', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:80', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'emoji' => ['nullable', 'string', 'max:16'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'occasions' => ['nullable', 'string', 'max:1000'],
            'accent_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
            'icon' => ['nullable', 'file', 'max:4096', 'mimes:jpg,jpeg,png,webp'],
            'cover' => ['nullable', 'file', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
        ]);
        $data['occasions'] = array_values(array_filter(array_map('trim', explode(',', (string) ($data['occasions'] ?? '')))));
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        if ($request->hasFile('icon')) {
            $data['icon_path'] = $images->storePublic($request->file('icon'), 'categories', 256);
        }
        if ($request->hasFile('cover')) {
            $data['cover_path'] = $images->storePublic($request->file('cover'), 'categories', 1600);
        }
        if ($request->boolean('remove_icon')) {
            $data['icon_path'] = null;
        }
        unset($data['icon'], $data['cover']);

        return $data;
    }
}
