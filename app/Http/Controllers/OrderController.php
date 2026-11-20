<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Package;
use App\Models\PageMedia;
use App\Models\Template;
use App\Services\ImageService;
use App\Services\OrderService;
use App\Services\PageRenderer;
use App\Services\PricingService;
use App\Support\Bn;
use App\Support\ContentSchema;
use App\Support\Phone;
use App\Support\Settings;
use App\Support\SlugRules;
use App\Support\TemplateRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use RuntimeException;

class OrderController extends Controller
{
    private const DRAFT_COOKIE = 'gb_draft';

    public function create(Request $request, Settings $settings)
    {
        $this->draftToken($request);

        $categories = Category::active()->with(['templates' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->with('fields')])->get()
            ->filter(fn ($c) => $c->templates->isNotEmpty())->values();

        $catalog = $categories->map(fn (Category $c) => [
            'id' => $c->id,
            'slug' => $c->slug,
            'name' => $c->displayName(),
            'emoji' => $c->emoji,
            'tagline' => $c->tagline,
            'accent' => $c->accent_color ?: '#d9482b',
            'occasions' => $c->occasions ?? [],
            'templates' => $c->templates->map(fn (Template $t) => [
                'slug' => $t->slug,
                'name' => $t->name,
                'tagline' => $t->tagline,
                'preview' => $t->previewUrl(),
                'render' => route('templates.render', $t),
                'max_photos' => $t->max_photos,
                'fields' => $t->fields->map->toSchema()->values(),
                'guide' => TemplateRegistry::guide($t->layout, $t->fields->map->toSchema()->all(), $t->sampleContent()),
            ])->values(),
        ])->values();

        $packages = Package::active()->get()->map(fn (Package $p) => [
            'slug' => $p->slug,
            'name' => $p->name_bn ?: $p->name,
            'duration' => $p->durationLabel(),
            'price' => (float) $p->price,
            'price_label' => Bn::money($p->price),
            'compare_label' => $p->compare_price > $p->price ? Bn::money($p->compare_price) : null,
            'description' => $p->description,
            'features' => $p->features ?? [],
            'max_photos' => $p->maxPhotos(),
            'featured' => $p->is_featured,
        ])->values();

        return view('site.order', [
            'boot' => [
                'catalog' => $catalog,
                'packages' => $packages,
                'preselect' => ['template' => $request->query('template'), 'package' => $request->query('package')],
                'bkash' => ['number' => $settings->bkashNumber(), 'type' => $settings->get('bkash_account_type'), 'instructions' => $settings->get('payment_instructions')],
                'limits' => [
                    'max_mb' => (float) $settings->get('max_file_mb', 8),
                    'max_files' => (int) $settings->get('max_files_per_order', 30),
                    'types' => array_values(array_intersect(explode(',', strtolower(str_replace(' ', '', $settings->get('allowed_types', 'jpg,jpeg,png,webp')))), config('golpobari.images.allowed_extensions'))),
                ],
                'ordersEnabled' => $settings->bool('orders_enabled'),
                'deliveryTime' => $settings->get('delivery_time'),
                'urls' => [
                    'upload' => route('order.upload'),
                    'deleteUpload' => url('/order/uploads'),
                    'coupon' => route('order.coupon'),
                    'slug' => route('order.slug'),
                    'preview' => route('order.preview'),
                    'submit' => route('order.store'),
                    'terms' => route('terms'),
                ],
            ],
            'seo' => ['title' => 'অর্ডার করুন · GolpoBari', 'noindex' => true],
            'hideFooter' => true,
        ]);
    }

    public function upload(Request $request, ImageService $images, Settings $settings): JsonResponse
    {
        $maxKb = (int) round(((float) $settings->get('max_file_mb', 8)) * 1024);
        $types = array_intersect(explode(',', strtolower(str_replace(' ', '', $settings->get('allowed_types', 'jpg,jpeg,png,webp')))), config('golpobari.images.allowed_extensions'));

        $data = $request->validate([
            'file' => ['required', 'file', 'max:'.$maxKb, 'mimes:'.implode(',', $types), 'mimetypes:'.implode(',', config('golpobari.images.allowed_mimes'))],
            'field' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/'],
        ], [
            'file.max' => 'ছবিটি '.Bn::digits($settings->get('max_file_mb', 8)).' MB-এর বেশি বড়।',
            'file.mimes' => 'শুধু JPG, PNG বা WebP ছবি দেওয়া যাবে।',
            'file.mimetypes' => 'শুধু JPG, PNG বা WebP ছবি দেওয়া যাবে।',
        ]);

        $token = $this->draftToken($request);
        $count = PageMedia::where('draft_token', $token)->whereNull('order_id')->count();
        if ($count >= (int) $settings->get('max_files_per_order', 30) + 10) {
            return response()->json(['message' => 'অনেক বেশি ছবি আপলোড হয়েছে। কিছু ছবি মুছে আবার চেষ্টা করুন।'], 422);
        }

        try {
            $media = $images->storeDraft($data['file'], $token, $data['field'], $count);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['id' => $media->id, 'thumb' => $media->url(true), 'url' => $media->url(), 'width' => $media->width, 'height' => $media->height]);
    }

    public function deleteUpload(Request $request, PageMedia $media, ImageService $images): JsonResponse
    {
        abort_unless($media->draft_token && hash_equals($media->draft_token, $this->draftToken($request)) && ! $media->order_id, 404);
        $images->delete($media);

        return response()->json(['ok' => true]);
    }

    public function coupon(Request $request, PricingService $pricing): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:40'], 'package' => ['required', 'string'], 'phone' => ['nullable', 'string', 'max:20']]);
        $package = Package::active()->where('slug', $data['package'])->firstOrFail();
        $phone = Phone::normalize($data['phone'] ?? '') ?: null;
        $q = $pricing->quote($package, $data['code'], $phone);

        if ($q['error']) {
            return response()->json(['ok' => false, 'message' => $q['error'], 'original' => $q['original'], 'final' => $q['original']], 422);
        }

        return response()->json([
            'ok' => true,
            'code' => $q['coupon']->code,
            'message' => 'কুপন যোগ হয়েছে! আপনি '.Bn::money($q['discount']).' ছাড় পাচ্ছেন।',
            'original' => $q['original'], 'discount' => $q['discount'], 'final' => $q['final'],
            'labels' => ['original' => Bn::money($q['original']), 'discount' => Bn::money($q['discount']), 'final' => Bn::money($q['final'])],
        ]);
    }

    public function slugCheck(Request $request): JsonResponse
    {
        $slug = SlugRules::normalize($request->input('slug'));
        if ($slug === '') {
            return response()->json(['ok' => false, 'slug' => '', 'message' => 'ইংরেজি অক্ষরে একটা নাম লিখুন।']);
        }
        $problem = SlugRules::problem($slug);

        return response()->json([
            'ok' => $problem === null,
            'slug' => $slug,
            'message' => $problem === null ? 'দারুণ! golpobari.com/'.$slug.' খালি আছে।' : match (true) {
                str_contains($problem, 'reserved') => 'এই নামটি সংরক্ষিত, অন্য একটি দিন।',
                str_contains($problem, 'taken') => 'এই নামটি আগেই নেওয়া। চেষ্টা করুন: '.SlugRules::suggest($slug),
                default => 'লিংক ২–৬০ অক্ষরের হতে হবে (a-z, 0-9, -)।',
            },
        ]);
    }

    public function preview(Request $request, PageRenderer $renderer)
    {
        $template = Template::active()->with('fields')->where('slug', (string) $request->input('template'))->firstOrFail();
        $schema = new ContentSchema($template->fields);
        $content = $schema->sanitize((array) $request->input('content', []));

        $ids = collect($request->input('media', []))->flatten()->map(fn ($x) => (int) $x)->filter()->values()->all();
        $order = array_flip($ids);
        $captions = (array) $request->input('captions', []);
        $media = $ids ? PageMedia::where('draft_token', $this->draftToken($request))->whereIn('id', $ids)->get()
            ->each(function ($m) use ($order, $captions) {
                $m->sort_order = $order[$m->id] ?? 0;
                if (! empty($captions[$m->id])) {
                    $m->metadata = ['caption' => ContentSchema::clean(Str::limit((string) $captions[$m->id], 140, ''))];
                }
            }) : collect();

        return response($renderer->forDraft($template, $content, $media, $request->boolean('fill'))->render())->header('X-Robots-Tag', 'noindex');
    }

    public function store(StoreOrderRequest $request, OrderService $orders): JsonResponse
    {
        $template = $request->templateModel;
        $content = $request->schema->sanitize($request->input('content', []));
        $data = $request->validated();
        $data['customer_name'] = ContentSchema::clean($data['customer_name']);
        $data['customer_note'] = ContentSchema::clean($data['customer_note'] ?? '') ?: null;
        $data['occasion'] = ContentSchema::clean($data['occasion'] ?? '') ?: null;

        $order = $orders->submit($data, $template, $request->packageModel, $content, $this->draftToken($request), $request->ip());

        foreach ((array) $request->input('captions', []) as $id => $caption) {
            $caption = ContentSchema::clean(Str::limit((string) $caption, 140, ''));
            if ($caption !== '') {
                PageMedia::where('order_id', $order->id)->whereKey((int) $id)->update(['metadata' => json_encode(['caption' => $caption], JSON_UNESCAPED_UNICODE)]);
            }
        }

        $mine = (array) $request->session()->get('my_orders', []);
        $mine[] = $order->order_number;
        $request->session()->put('my_orders', array_slice(array_unique($mine), -10));

        // Fresh draft token for the next order.
        Cookie::queue(Cookie::forget(self::DRAFT_COOKIE));

        return response()->json(['ok' => true, 'order' => $order->order_number, 'redirect' => route('order.confirmation', $order)]);
    }

    public function confirmation(Request $request, Order $order, Settings $settings)
    {
        if (! in_array($order->order_number, (array) $request->session()->get('my_orders', []), true)) {
            return redirect()->route('track')->with('status', 'অর্ডারের তথ্য দেখতে Order ID আর ফোন নম্বর দিন।');
        }

        return view('site.order-confirmation', [
            'order' => $order->load('payment'),
            'seo' => ['title' => 'অর্ডার জমা হয়েছে · GolpoBari', 'noindex' => true],
        ]);
    }

    private function draftToken(Request $request): string
    {
        $token = (string) $request->cookie(self::DRAFT_COOKIE);
        if (! preg_match('/^[A-Za-z0-9]{40}$/', $token)) {
            $token = Str::random(40);
            $request->cookies->set(self::DRAFT_COOKIE, $token);
        }
        // Refresh for 7 days so a refresh / coming back tomorrow keeps the uploaded photos.
        Cookie::queue(self::DRAFT_COOKIE, $token, 60 * 24 * 7, null, null, null, true, false, 'lax');

        return $token;
    }
}
