<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Moves a catalogue item one place up or down. */
class ReorderController extends Controller
{
    private const MODELS = [
        'categories' => \App\Models\Category::class,
        'templates' => \App\Models\Template::class,
        'packages' => \App\Models\Package::class,
        'faqs' => \App\Models\Faq::class,
        'testimonials' => \App\Models\Testimonial::class,
    ];

    public function __invoke(Request $request, string $type)
    {
        Gate::authorize('manage-catalog');
        abort_unless(isset(self::MODELS[$type]), 404);
        $data = $request->validate(['id' => ['required', 'integer'], 'direction' => ['required', 'in:up,down']]);

        $model = self::MODELS[$type];
        $items = $model::orderBy('sort_order')->orderBy('id')->get(['id', 'sort_order'])->values();
        $i = $items->search(fn ($x) => $x->id === (int) $data['id']);
        abort_if($i === false, 404);
        $j = $data['direction'] === 'up' ? $i - 1 : $i + 1;
        if ($j >= 0 && $j < $items->count()) {
            $list = $items->all();
            [$list[$i], $list[$j]] = [$list[$j], $list[$i]];
            foreach ($list as $pos => $item) {
                $model::whereKey($item->id)->update(['sort_order' => $pos + 1]);
            }
        }

        return back();
    }
}
