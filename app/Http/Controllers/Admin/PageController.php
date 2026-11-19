<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PageStatus;
use App\Enums\PageVisibility;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Package;
use App\Models\Page;
use App\Models\PageMedia;
use App\Models\Template;
use App\Services\ImageService;
use App\Services\PageRenderer;
use App\Services\PageService;
use App\Support\ActivityLogger;
use App\Support\ContentSchema;
use App\Support\SlugRules;
use App\Support\TemplateRegistry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use RuntimeException;

class PageController extends Controller
{
    public function __construct(private PageService $pages) {}

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'all');
        $days = (int) config('golpobari.expiring_soon_days', 7);

        $pages = Page::query()->with(['template:id,name,category_id', 'template.category:id,name', 'order:id,order_number,customer_name'])
            ->when($request->query('q'), fn ($q, $v) => $q->where(fn ($w) => $w->where('slug', 'like', "%{$v}%")->orWhere('title', 'like', "%{$v}%")
                ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$v}%")->orWhere('customer_name', 'like', "%{$v}%"))))
            ->when(in_array($tab, array_column(PageStatus::cases(), 'value'), true), fn ($q) => $q->where('status', $tab))
            ->when($tab === 'expiring', fn ($q) => $q->expiringSoon($days))
            ->when($request->query('template'), fn ($q, $v) => $q->where('template_id', (int) $v))
            ->when($request->query('category'), fn ($q, $v) => $q->whereHas('template', fn ($t) => $t->where('category_id', (int) $v)))
            ->when($request->query('visibility'), fn ($q, $v) => $q->where('visibility', $v))
            ->orderBy(in_array($request->query('sort'), ['expires_at', 'views_count', 'published_at', 'slug'], true) ? $request->query('sort') : 'updated_at', $request->query('dir') === 'asc' ? 'asc' : 'desc')
            ->paginate(25)->withQueryString();

        $counts = Page::select('status', DB::raw('count(*) c'))->groupBy('status')->pluck('c', 'status');

        return view('admin.pages.index', [
            'pages' => $pages, 'tab' => $tab, 'counts' => $counts,
            'expiringCount' => Page::expiringSoon($days)->count(),
            'templates' => Template::orderBy('name')->pluck('name', 'id'),
            'categories' => Category::orderBy('sort_order')->pluck('name', 'id'),
        ]);
    }

    public function storeFromOrder(Order $order)
    {
        if (! in_array($order->payment_status?->value, ['verified'], true)) {
            return back()->with('error', 'Verify the payment before creating the page.');
        }
        try {
            $page = $this->pages->createFromOrder($order->load(['package', 'page']));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page draft created from the order. Review, then publish.');
    }

    public function edit(Page $page)
    {
        $page->load(['template.fields', 'media', 'order.package']);
        $since = now()->subDays(30);

        return view('admin.pages.edit', [
            'page' => $page,
            'templates' => Template::with('category')->orderBy('sort_order')->get(),
            'packages' => Package::orderBy('sort_order')->get(),
            'fonts' => collect(TemplateRegistry::FONTS)->map(fn ($f) => $f['label'])->all(),
            'theme' => $page->template->resolvedTheme(),
            'stats' => [
                'total' => $page->views_count,
                'unique' => $page->views()->distinct('visitor_hash')->count('visitor_hash'),
                'last30' => $page->views()->where('viewed_at', '>=', $since)->count(),
                'devices' => $page->views()->select('device', DB::raw('count(*) c'))->groupBy('device')->pluck('c', 'device'),
                'referrers' => $page->views()->whereNotNull('referrer_host')->select('referrer_host', DB::raw('count(*) c'))->groupBy('referrer_host')->orderByDesc('c')->limit(5)->pluck('c', 'referrer_host'),
            ],
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $page->load('template.fields');
        $request->merge(['slug' => SlugRules::normalize($request->input('slug'))]);

        $template = Template::with('fields')->findOrFail((int) $request->input('template_id', $page->template_id));
        $schema = new ContentSchema($template->fields);

        $data = $request->validate(array_merge([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'min:2', 'max:60', 'regex:'.SlugRules::PATTERN],
            'template_id' => ['required', 'exists:templates,id'],
            'visibility' => ['required', Rule::enum(PageVisibility::class)],
            'access_code' => ['nullable', 'string', 'min:4', 'max:64'],
            'show_branding' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
            'seo.title' => ['nullable', 'string', 'max:120'],
            'seo.description' => ['nullable', 'string', 'max:300'],
            'seo.og_title' => ['nullable', 'string', 'max:120'],
            'seo.og_description' => ['nullable', 'string', 'max:300'],
            'seo.canonical' => ['nullable', 'url', 'max:300'],
            'og_image' => ['nullable', 'file', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
            'theme.primary' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.accent' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.background' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.text' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.font_heading' => ['nullable', Rule::in(array_keys(TemplateRegistry::FONTS))],
            'theme.font_body' => ['nullable', Rule::in(array_keys(TemplateRegistry::FONTS))],
        ], $schema->rules('content', false)), [], $schema->attributes('content'));

        if ($problem = SlugRules::problem($data['slug'], $page->id)) {
            return back()->withInput()->withErrors(['slug' => $problem]);
        }
        if ($data['visibility'] === PageVisibility::Private->value && ! $page->access_code && empty($data['access_code'])) {
            return back()->withInput()->withErrors(['access_code' => 'Set an access code for a private page.']);
        }

        $seo = array_map(fn ($v) => $v === null ? null : ContentSchema::clean($v), $request->input('seo', []));
        $seo['og_image'] = $page->seo['og_image'] ?? null;
        if ($request->hasFile('og_image')) {
            $seo['og_image'] = app(ImageService::class)->storePublic($request->file('og_image'), 'og', 1200);
        }
        if ($request->boolean('remove_og_image')) {
            $seo['og_image'] = null;
        }

        $theme = array_filter((array) $request->input('theme', []), fn ($v) => filled($v));
        $oldSlug = $page->slug;

        $page->fill([
            'title' => ContentSchema::clean($data['title']),
            'slug' => $data['slug'],
            'template_id' => $template->id,
            'visibility' => $data['visibility'],
            'show_branding' => $request->boolean('show_branding'),
            'expires_at' => $data['expires_at'] ? Carbon::parse($data['expires_at'])->endOfDay() : null,
            'seo' => $seo,
            'theme' => $theme ?: null,
            'content' => $schema->sanitize($request->input('content', [])),
        ]);
        if (! empty($data['access_code'])) {
            $page->access_code = Hash::make($data['access_code']);
        }
        if ($page->status === PageStatus::Expired && $page->expires_at && $page->expires_at->isFuture()) {
            $page->status = PageStatus::Active;
        }
        $page->save();

        ActivityLogger::log('page.updated', $page, $oldSlug !== $page->slug ? "/{$oldSlug} → /{$page->slug}" : '/'.$page->slug);

        return back()->with('success', 'Page saved.');
    }

    public function preview(Page $page, PageRenderer $renderer)
    {
        $page->load(['template.fields', 'media']);

        return response($renderer->forPage($page, true)->render())->header('X-Robots-Tag', 'noindex');
    }

    public function action(Request $request, Page $page)
    {
        $action = $request->validate(['action' => ['required', Rule::in(['publish', 'unpublish', 'ready', 'suspend', 'archive', 'activate', 'draft'])]])['action'];
        $page->load('order.package');

        try {
            match ($action) {
                'publish', 'activate' => $this->pages->publish($page),
                'unpublish' => $this->pages->unpublish($page),
                'ready' => $this->pages->markReady($page),
                'suspend' => $this->pages->setStatus($page, PageStatus::Suspended),
                'archive' => $this->pages->setStatus($page, PageStatus::Archived),
                'draft' => $this->pages->setStatus($page, PageStatus::Draft),
            };
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $msg = ['publish' => 'Page is live at '.$page->url(), 'activate' => 'Page re-activated.', 'unpublish' => 'Page unpublished.', 'ready' => 'Marked ready.', 'suspend' => 'Page suspended.', 'archive' => 'Page archived.', 'draft' => 'Moved to draft.'][$action];

        return back()->with('success', $msg);
    }

    public function renew(Request $request, Page $page)
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['package', 'date'])],
            'package_id' => ['required_if:mode,package', 'nullable', 'exists:packages,id'],
            'until' => ['required_if:mode,date', 'nullable', 'date', 'after:today'],
        ]);
        try {
            $this->pages->renew($page->load('order'),
                $data['mode'] === 'package' ? Package::find($data['package_id']) : null,
                $data['mode'] === 'date' ? Carbon::parse($data['until'])->endOfDay() : null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Renewed until '.$page->fresh()->expires_at->format('d M Y').'.');
    }

    public function duplicate(Page $page)
    {
        $copy = $this->pages->duplicate($page->load('media'));

        return redirect()->route('admin.pages.edit', $copy)->with('success', 'Duplicated as /'.$copy->slug.' (draft).');
    }

    public function addMedia(Request $request, Page $page, ImageService $images)
    {
        $request->validate([
            'field_key' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/'],
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['file', 'max:12288', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp'],
        ]);
        try {
            foreach ($request->file('files') as $file) {
                if ($page->order) {
                    $images->storeForOrder($file, $page->order, $request->input('field_key'), $page->id);
                } else {
                    $r = $images->process($file, 'pages/'.$page->id);
                    $page->media()->create(['field_key' => $request->input('field_key'), 'path' => $r['path'], 'thumb_path' => $r['thumb'], 'mime' => $r['mime'], 'size' => $r['size'], 'width' => $r['width'], 'height' => $r['height'], 'sort_order' => (int) $page->media()->max('sort_order') + 1]);
                }
            }
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Photos added.');
    }

    /** Captions, order, field assignment and removal for the page's photos. */
    public function updateMedia(Request $request, Page $page, ImageService $images)
    {
        $data = $request->validate([
            'media' => ['array'],
            'media.*.caption' => ['nullable', 'string', 'max:140'],
            'media.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'media.*.field_key' => ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/'],
            'media.*.delete' => ['nullable', 'boolean'],
        ]);
        foreach ($data['media'] ?? [] as $id => $row) {
            $m = PageMedia::where('page_id', $page->id)->find((int) $id);
            if (! $m) {
                continue;
            }
            if (! empty($row['delete'])) {
                // Keep the customer's original upload attached to the order; just detach from the page.
                $m->order_id ? $m->update(['page_id' => null]) : $images->delete($m);

                continue;
            }
            $meta = $m->metadata ?? [];
            $meta['caption'] = ContentSchema::clean($row['caption'] ?? '') ?: null;
            $m->update(['metadata' => $meta, 'sort_order' => (int) ($row['sort_order'] ?? $m->sort_order), 'field_key' => $row['field_key'] ?: $m->field_key]);
        }

        return back()->with('success', 'Photos updated.');
    }

    public function destroy(Page $page)
    {
        if ($page->order_id) {
            $this->pages->setStatus($page, PageStatus::Archived);

            return redirect()->route('admin.pages.index')->with('success', 'Page archived. Pages linked to an order are archived, not deleted, so customer data is kept.');
        }
        ActivityLogger::log('page.deleted', $page, '/'.$page->slug);
        $page->media()->get()->each(fn ($m) => app(ImageService::class)->delete($m));
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }
}
