<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Template;
use App\Services\PageRenderer;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function categories()
    {
        return view('site.categories', [
            'categories' => Category::active()->withCount(['templates as active_templates_count' => fn ($q) => $q->where('is_active', true)])->get(),
            'seo' => ['title' => 'ক্যাটাগরি · GolpoBari', 'description' => 'ভালোবাসা, জন্মদিন, বার্ষিকী, পোর্টফোলিও, ব্যবসা, অনুষ্ঠান আর স্মৃতি। আপনার গল্পের জন্য ক্যাটাগরি বেছে নিন।'],
        ]);
    }

    public function category(Category $category)
    {
        abort_unless($category->is_active, 404);

        return view('site.category', [
            'category' => $category,
            'templates' => $category->activeTemplates()->with('category')->get(),
            'seo' => ['title' => $category->displayName().' টেমপ্লেট · GolpoBari', 'description' => $category->description ?: $category->tagline],
        ]);
    }

    public function templates(Request $request)
    {
        $categories = Category::active()->get();
        $current = $categories->firstWhere('slug', $request->query('category'));

        return view('site.templates', [
            'categories' => $categories,
            'current' => $current,
            'templates' => Template::active()->with('category')->when($current, fn ($q) => $q->where('category_id', $current->id))->get(),
            'seo' => ['title' => 'টেমপ্লেট · GolpoBari', 'description' => 'GolpoBari-র সব টেমপ্লেট দেখুন: romantic, birthday, portfolio, business আর আরও অনেক। মোবাইল ও ডেস্কটপ প্রিভিউসহ।',
                'canonical' => route('templates.index', $current ? ['category' => $current->slug] : [])],
        ]);
    }

    public function template(Template $template)
    {
        abort_unless($template->is_active && $template->category?->is_active, 404);
        $template->load('category');

        return view('site.template', [
            'template' => $template,
            'related' => Template::active()->with('category')->where('id', '!=', $template->id)->where('category_id', $template->category_id)->limit(3)->get(),
            'seo' => ['title' => $template->name.' টেমপ্লেট · GolpoBari', 'description' => $template->description, 'image' => $template->previewUrl()],
        ]);
    }

    /** The template filled with sample content, shown inside the preview frame. */
    public function render(Template $template, PageRenderer $renderer)
    {
        abort_unless($template->is_active, 404);
        $template->load('fields');

        return response($renderer->forSample($template)->render())
            ->header('X-Robots-Tag', 'noindex')
            ->header('Cache-Control', 'public, max-age=600');
    }
}
