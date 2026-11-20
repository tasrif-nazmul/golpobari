<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Template;
use App\Models\TemplateField;
use App\Services\ImageService;
use App\Support\ActivityLogger;
use App\Support\TemplateRegistry;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TemplateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:manage-catalog')];
    }

    public function index(Request $request)
    {
        return view('admin.templates.index', [
            'templates' => Template::with('category')->withCount(['fields'])
                ->when($request->query('category'), fn ($q, $v) => $q->where('category_id', (int) $v))
                ->orderBy('category_id')->orderBy('sort_order')->get(),
            'categories' => Category::orderBy('sort_order')->pluck('name', 'id'),
            'orderCounts' => DB::table('orders')->select('template_id', DB::raw('count(*) c'))->groupBy('template_id')->pluck('c', 'template_id'),
        ]);
    }

    public function create(Request $request)
    {
        $layout = array_key_exists((string) $request->query('layout'), TemplateRegistry::layouts()) ? $request->query('layout') : 'minimal-portfolio';
        $template = new Template(['layout' => $layout, 'is_active' => true, 'max_photos' => 20, 'sort_order' => (int) Template::max('sort_order') + 1]);
        $fields = collect(TemplateRegistry::layout($layout)['fields']);

        return $this->form($template, $fields);
    }

    public function store(Request $request, ImageService $images)
    {
        [$data, $fields] = $this->validated($request, $images);
        $template = DB::transaction(function () use ($data, $fields) {
            $t = Template::create($data);
            $this->syncFields($t, $fields);

            return $t;
        });
        ActivityLogger::log('template.created', $template, $template->name);

        return redirect()->route('admin.templates.edit', $template)->with('success', 'Template created.');
    }

    public function edit(Template $template)
    {
        return $this->form($template, $template->fields->map->toSchema());
    }

    public function update(Request $request, Template $template, ImageService $images)
    {
        [$data, $fields] = $this->validated($request, $images, $template);
        DB::transaction(function () use ($template, $data, $fields) {
            $template->update($data);
            $this->syncFields($template, $fields);
        });
        ActivityLogger::log('template.updated', $template, $template->name);

        return redirect()->route('admin.templates.edit', $template)->with('success', 'Template saved.');
    }

    public function destroy(Template $template)
    {
        if ($template->pages()->exists() || \App\Models\Order::where('template_id', $template->id)->exists()) {
            return back()->with('error', 'This template has orders or pages. Deactivate it instead so their history stays intact.');
        }
        ActivityLogger::log('template.deleted', $template, $template->name);
        $template->delete();

        return redirect()->route('admin.templates.index')->with('success', 'Template deleted.');
    }

    private function form(Template $template, $fields)
    {
        return view('admin.templates.form', [
            'template' => $template,
            'fields' => collect($fields)->values(),
            'categories' => Category::orderBy('sort_order')->pluck('name', 'id'),
            'layouts' => TemplateRegistry::options(),
            'fonts' => collect(TemplateRegistry::FONTS)->map(fn ($f) => $f['label'])->all(),
            'defaults' => TemplateRegistry::layout($template->layout)['theme'],
            'types' => TemplateField::TYPES,
        ]);
    }

    private function validated(Request $request, ImageService $images, ?Template $template = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:80', Rule::unique('templates', 'slug')->ignore($template?->id)],
            'category_id' => ['required', 'exists:categories,id'],
            'layout' => ['required', Rule::in(array_keys(TemplateRegistry::layouts()))],
            'tagline' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'max_photos' => ['required', 'integer', 'min:0', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'], 'is_featured' => ['boolean'], 'is_premium' => ['boolean'],
            'preview' => ['nullable', 'file', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
            'theme.primary' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.accent' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.background' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.text' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.font_heading' => ['nullable', Rule::in(array_keys(TemplateRegistry::FONTS))],
            'theme.font_body' => ['nullable', Rule::in(array_keys(TemplateRegistry::FONTS))],
            'theme.font_accent' => ['nullable', Rule::in(array_keys(TemplateRegistry::FONTS))],
            'fields_json' => ['required', 'json'],
        ]);

        $fields = json_decode($data['fields_json'], true) ?: [];
        $v = Validator::make(['fields' => $fields], [
            'fields' => ['array', 'max:60'],
            'fields.*.key' => ['required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/', 'distinct'],
            'fields.*.label' => ['required', 'string', 'max:120'],
            'fields.*.type' => ['required', Rule::in(array_keys(TemplateField::TYPES))],
            'fields.*.placeholder' => ['nullable', 'string', 'max:200'],
            'fields.*.help' => ['nullable', 'string', 'max:255'],
            'fields.*.required' => ['boolean'],
            'fields.*.max_length' => ['nullable', 'integer', 'min:1', 'max:20000'],
            'fields.*.options' => ['nullable', 'array'],
            'fields.*.options.choices' => ['nullable', 'array', 'max:50'],
            'fields.*.options.choices.*' => ['string', 'max:80'],
            'fields.*.options.max' => ['nullable', 'integer', 'min:1', 'max:100'],
            'fields.*.options.fields' => ['nullable', 'array', 'max:8'],
            'fields.*.options.fields.*.key' => ['required', 'string', 'max:40', 'regex:/^[a-z][a-z0-9_]*$/'],
            'fields.*.options.fields.*.label' => ['required', 'string', 'max:80'],
            'fields.*.options.fields.*.type' => ['required', Rule::in(['text', 'textarea', 'url', 'select', 'date'])],
        ], [], ['fields.*.key' => 'field key']);
        $v->validate();

        $theme = array_filter((array) $request->input('theme', []), fn ($x) => filled($x));
        $out = collect($data)->only(['name', 'slug', 'category_id', 'layout', 'tagline', 'description', 'max_photos', 'sort_order'])->all();
        $out['sort_order'] = (int) ($out['sort_order'] ?? 0);
        $out['theme'] = $theme ?: null;
        $out['is_active'] = $request->boolean('is_active');
        $out['is_featured'] = $request->boolean('is_featured');
        $out['is_premium'] = $request->boolean('is_premium');
        if ($request->hasFile('preview')) {
            $out['preview_path'] = $images->storePublic($request->file('preview'), 'templates', 1200);
        }

        return [$out, $fields];
    }

    private function syncFields(Template $template, array $fields): void
    {
        $keep = [];
        foreach (array_values($fields) as $i => $f) {
            $field = $template->fields()->updateOrCreate(['key' => $f['key']], [
                'label' => $f['label'], 'type' => $f['type'],
                'section' => in_array($f['type'], TemplateField::MEDIA_TYPES, true) ? 'photos' : 'info',
                'placeholder' => $f['placeholder'] ?? null, 'help' => $f['help'] ?? null,
                'options' => array_filter($f['options'] ?? [], fn ($v) => $v !== null && $v !== []) ?: null,
                'is_required' => (bool) ($f['required'] ?? false), 'max_length' => $f['max_length'] ?? null, 'sort_order' => $i + 1,
            ]);
            $keep[] = $field->id;
        }
        $template->fields()->whereNotIn('id', $keep)->delete();
    }
}
