@extends('layouts.admin')
@section('title', $template->exists ? 'Edit template' : 'New template')
@section('content')
@php
    $fieldData = $fields->map(fn ($f) => [
        'key' => $f['key'], 'label' => $f['label'], 'type' => $f['type'], 'placeholder' => $f['placeholder'] ?? '', 'help' => $f['help'] ?? '',
        'required' => (bool) ($f['required'] ?? false), 'max_length' => $f['max_length'] ?? null, 'options' => (object) ($f['options'] ?? []),
    ])->values();
    if (old('fields_json')) { $fieldData = collect(json_decode(old('fields_json'), true) ?: []); }
@endphp
<x-admin.header :title="$template->exists ? $template->name : 'New template'" :sub="$template->exists ? 'Layout: '.($layouts[$template->layout] ?? $template->layout) : null" :back="route('admin.templates.index')">
    @if ($template->exists)
        <x-slot:actions>
            <a href="{{ route('templates.render', $template) }}" target="_blank" class="a-btn a-btn-ghost">Sample ↗</a>
            <form method="POST" action="{{ route('admin.templates.destroy', $template) }}" data-confirm="Delete this template? Templates with orders can only be hidden.">@csrf @method('DELETE')<button class="a-btn a-btn-ghost text-rose-600">Delete</button></form>
        </x-slot:actions>
    @endif
</x-admin.header>

<form method="POST" action="{{ $template->exists ? route('admin.templates.update', $template) : route('admin.templates.store') }}" enctype="multipart/form-data" class="space-y-6"
      x-data="fieldEditor(@js($fieldData), @js($types))">
    @csrf @if ($template->exists) @method('PUT') @endif
    <input type="hidden" name="fields_json" :value="json">

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="a-card grid gap-4 p-5 sm:grid-cols-2">
            <h2 class="font-semibold sm:col-span-2">Details</h2>
            <x-admin.field name="name" label="Name" :value="$template->name" required />
            <x-admin.field name="slug" label="Address" :value="$template->slug" help="Made from the name if empty." />
            <x-admin.field name="category_id" label="Category" type="select" :value="$template->category_id" :options="$categories->all()" required />
            <div>
                <x-admin.field name="layout" label="Layout" type="select" :value="$template->layout" :options="$layouts" required />
                @unless ($template->exists)<p class="a-help">To load another layout's default fields, <a class="underline" href="#" onclick="location.href='{{ route('admin.templates.create') }}?layout='+document.getElementById('f_layout').value;return false">reload with it</a>.</p>@endunless
            </div>
            <x-admin.field name="tagline" label="Tagline" :value="$template->tagline" class="sm:col-span-2" />
            <x-admin.field name="description" label="Description" type="textarea" rows="3" :value="$template->description" class="sm:col-span-2" />
            <x-admin.field name="max_photos" label="Max photos" type="number" :value="$template->max_photos" help="Packages can lower this further." />
            <x-admin.field name="sort_order" label="Sort order" type="number" :value="$template->sort_order" />
            <div class="space-y-2 sm:col-span-2">
                <x-admin.field name="is_active" type="checkbox" label="Show on the website" :value="$template->is_active" />
                <x-admin.field name="is_featured" type="checkbox" label="Featured on the homepage" :value="$template->is_featured" />
                <x-admin.field name="is_premium" type="checkbox" label="Premium badge" :value="$template->is_premium" />
            </div>
        </section>

        <section class="a-card grid content-start gap-4 p-5 sm:grid-cols-2">
            <h2 class="font-semibold sm:col-span-2">Look</h2>
            @foreach (['primary' => 'Primary', 'accent' => 'Accent', 'background' => 'Background', 'text' => 'Text'] as $k => $label)
                <div x-data="{ v: @js(old("theme.$k", $template->theme[$k] ?? '')) }">
                    <label class="a-label" for="theme_{{ $k }}">{{ $label }}</label>
                    <div class="flex gap-2">
                        <input type="color" :value="v || @js($defaults[$k] ?? '#000000')" @input="v = $event.target.value" class="h-10 w-12 cursor-pointer rounded-lg ring-1 ring-stone-300">
                        <input id="theme_{{ $k }}" name="theme[{{ $k }}]" x-model="v" class="a-input font-mono" placeholder="{{ $defaults[$k] ?? '' }}">
                    </div>
                    @error("theme.$k")<p class="a-error">{{ $message }}</p>@enderror
                </div>
            @endforeach
            @foreach (['font_heading' => 'Heading font', 'font_body' => 'Body font', 'font_accent' => 'Accent font'] as $k => $label)
                <x-admin.field :name="'theme['.$k.']'" :label="$label" type="select" :value="$template->theme[$k] ?? ''" :options="['' => 'Layout default'.(isset($defaults[$k]) ? ' ('.($fonts[$defaults[$k]] ?? $defaults[$k]).')' : '')] + $fonts" />
            @endforeach
            <div class="sm:col-span-2">
                <x-admin.field name="preview" label="Preview image" type="file" help="Tall screenshot, shown on template cards. 1200px wide." />
                @if ($template->previewUrl())<img src="{{ $template->previewUrl() }}" alt="" class="mt-2 h-28 rounded-lg object-cover object-top ring-1 ring-stone-200">@endif
            </div>
        </section>
    </div>

    <section class="a-card p-5">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="font-semibold">Fields customers fill in</h2>
                <p class="text-sm text-stone-500">The layout shows fields it knows by key. Extra fields appear in a “More” section on the page.</p>
            </div>
            <button type="button" class="a-btn a-btn-ghost" @click="add()">+ Add field</button>
        </div>
        @error('fields_json')<p class="a-error mb-2">{{ $message }}</p>@enderror
        @if ($errors->has('fields.*'))<div class="mb-3 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">@foreach ($errors->get('fields.*') as $msgs)@foreach ($msgs as $m)<div>{{ $m }}</div>@endforeach @endforeach</div>@endif

        <ol class="space-y-2">
            <template x-for="(f, i) in fields" :key="i">
                <li class="rounded-lg ring-1 ring-stone-200">
                    <div class="flex items-center gap-2 p-2.5">
                        <div class="flex flex-col leading-none">
                            <button type="button" class="px-1 text-[10px] text-stone-400 hover:text-stone-900" @click="move(i, -1)" aria-label="Move up">▲</button>
                            <button type="button" class="px-1 text-[10px] text-stone-400 hover:text-stone-900" @click="move(i, 1)" aria-label="Move down">▼</button>
                        </div>
                        <button type="button" class="flex min-w-0 flex-1 items-center gap-2 text-left" @click="f._open = !f._open">
                            <span class="truncate font-medium" x-text="f.label || 'New field'"></span>
                            <span class="font-mono text-xs text-stone-400" x-text="f.key"></span>
                            <span class="rounded bg-stone-100 px-1.5 py-0.5 text-xs text-stone-600" x-text="types[f.type]"></span>
                            <span x-show="f.required" class="text-xs text-rose-500">required</span>
                        </button>
                        <button type="button" class="a-btn a-btn-ghost a-btn-sm text-rose-600" @click="remove(i)">Remove</button>
                    </div>
                    <div x-show="f._open" class="grid gap-3 border-t border-stone-100 p-3 sm:grid-cols-3">
                        <div><label class="a-label">Label</label><input x-model="f.label" @blur="slugKey(f)" class="a-input"></div>
                        <div><label class="a-label">Key</label><input x-model="f.key" class="a-input font-mono" pattern="[a-z][a-z0-9_]*"><p class="a-help">Lowercase, used by the layout.</p></div>
                        <div><label class="a-label">Type</label><select x-model="f.type" class="a-input"><template x-for="(l, k) in types" :key="k"><option :value="k" x-text="l" :selected="k === f.type"></option></template></select></div>
                        <div><label class="a-label">Placeholder</label><input x-model="f.placeholder" class="a-input"></div>
                        <div><label class="a-label">Help text</label><input x-model="f.help" class="a-input"></div>
                        <div class="flex items-end gap-3">
                            <div class="flex-1"><label class="a-label">Max length</label><input type="number" x-model="f.max_length" min="1" class="a-input"></div>
                            <label class="flex items-center gap-1.5 pb-2.5 text-sm"><input type="checkbox" x-model="f.required" class="accent-stone-900"> Required</label>
                        </div>
                        <div class="sm:col-span-3" x-show="f.type === 'select'"><label class="a-label">Choices</label><input x-model="f._choices" class="a-input" placeholder="Comma separated"></div>
                        <div x-show="f.type === 'gallery' || f.type === 'repeater'"><label class="a-label">Max items</label><input type="number" x-model="f.options.max" min="1" class="a-input"></div>
                        <div class="sm:col-span-3" x-show="f.type === 'repeater'"><label class="a-label">Item fields (JSON)</label><textarea x-model="f._sub" rows="3" class="a-input font-mono text-xs"></textarea><p class="a-help">e.g. [{"key":"title","label":"Title","type":"text"},{"key":"text","label":"Story","type":"textarea"}]</p></div>
                    </div>
                </li>
            </template>
        </ol>
        <p x-show="!fields.length" class="py-6 text-center text-sm text-stone-500">No fields. Add at least one so customers have something to fill in.</p>
    </section>

    @include('admin.partials.form-footer', ['cancel' => route('admin.templates.index'), 'label' => 'Save template'])
</form>
@endsection
