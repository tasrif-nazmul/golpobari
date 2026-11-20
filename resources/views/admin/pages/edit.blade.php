@extends('layouts.admin')
@section('title', '/'.$page->slug)
@section('content')
@php
    use App\Enums\PageStatus; use App\Enums\PageVisibility;
    $fields = $page->template->fields->map->toSchema();
    $mediaFields = $fields->filter(fn ($f) => in_array($f['type'], ['image', 'gallery'], true));
    $mediaLabels = $mediaFields->pluck('label', 'key')->all();
    $s = $page->status;
    $actions = match ($s) {
        PageStatus::Draft => ['ready' => 'Mark ready', 'publish' => 'Publish now'],
        PageStatus::Pending => ['publish' => 'Publish now', 'draft' => 'Back to draft'],
        PageStatus::Active => ['unpublish' => 'Unpublish', 'suspend' => 'Suspend'],
        PageStatus::Expired => ['archive' => 'Archive'],
        PageStatus::Suspended => ['activate' => 'Re-activate', 'archive' => 'Archive'],
        PageStatus::Archived => ['draft' => 'Restore as draft'],
    };
    $danger = ['suspend', 'archive', 'unpublish'];
@endphp

<x-admin.header :title="$page->title ?: 'Untitled page'" :sub="'/'.$page->slug.' · '.$page->template->name.($page->order ? ' · '.$page->order->customer_name : '')" :back="$page->order ? route('admin.orders.show', $page->order) : route('admin.pages.index')">
    <x-slot:actions>
        <x-admin.badge :color="$s->color()" class="!text-sm">{{ $s->label() }}</x-admin.badge>
        <a href="{{ route('admin.pages.preview', $page) }}" target="_blank" class="a-btn a-btn-ghost">Preview</a>
        @if ($page->isLive())<a href="{{ $page->url() }}" target="_blank" rel="noopener" class="a-btn a-btn-ghost">Open live</a>@endif
        @foreach ($actions as $a => $label)
            @php $go = in_array($a, ['publish', 'activate'], true); @endphp
            <form method="POST" action="{{ route('admin.pages.action', $page) }}" @if (in_array($a, $danger, true)) data-confirm="{{ $label }} /{{ $page->slug }}? Visitors will no longer see it." data-danger @elseif ($go) data-confirm="{{ $label }} /{{ $page->slug }}? Save your edits first. It goes live for everyone with the link." @endif>
                @csrf<input type="hidden" name="action" value="{{ $a }}">
                <button class="a-btn {{ $go ? 'a-btn-success' : 'a-btn-ghost' }}">{{ $label }}</button>
            </form>
        @endforeach
    </x-slot:actions>
</x-admin.header>

@if ($page->isLive())
    <div class="mb-6">@include('admin.partials.send-link', ['page' => $page])</div>
@elseif (in_array($s, [PageStatus::Draft, PageStatus::Pending], true))
    <ol class="mb-6 grid gap-2 text-sm sm:grid-cols-3">
        @foreach (['Check the text and photos below', 'Save, then open Preview', 'Happy? Tap Publish'] as $n => $tip)
            <li class="flex items-center gap-3 rounded-xl border-[1.5px] border-dashed border-stone-300 px-3 py-2.5"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-stone-900 text-xs font-bold text-white">{{ $n + 1 }}</span>{{ $tip }}</li>
        @endforeach
    </ol>
@endif

<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
    <div class="space-y-6">
        <form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data" class="space-y-6" x-data="{ vis: @js(old('visibility', $page->visibility->value)) }">
            @csrf @method('PUT')

            <section class="a-card p-5">
                <h2 class="mb-4 font-semibold">Basics</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.field name="title" label="Page title" :value="$page->title" required class="sm:col-span-2" />
                    <div>
                        <label class="a-label" for="f_slug">Address <span class="text-rose-500">*</span></label>
                        <div class="flex items-stretch overflow-hidden rounded-lg ring-1 ring-stone-300 focus-within:ring-2 focus-within:ring-stone-900">
                            <span class="flex items-center bg-stone-50 px-2.5 text-sm text-stone-500">{{ parse_url(url('/'), PHP_URL_HOST) }}/</span>
                            <input id="f_slug" name="slug" value="{{ old('slug', $page->slug) }}" required maxlength="60" class="min-w-0 flex-1 border-0 px-2 py-2 font-mono text-sm focus:outline-none">
                        </div>
                        <p class="a-help">Changing it breaks links the customer already shared.</p>
                        @error('slug')<p class="a-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="a-label" for="f_template_id">Template</label>
                        <select id="f_template_id" name="template_id" class="a-input">
                            @foreach ($templates->groupBy(fn ($t) => $t->category?->name) as $cat => $list)
                                <optgroup label="{{ $cat }}">@foreach ($list as $t)<option value="{{ $t->id }}" @selected((int) old('template_id', $page->template_id) === $t->id)>{{ $t->name }}</option>@endforeach</optgroup>
                            @endforeach
                        </select>
                        <p class="a-help">Switching template keeps content with matching field names. Save, then review the fields.</p>
                    </div>
                    <div>
                        <label class="a-label" for="f_visibility">Visibility</label>
                        <select id="f_visibility" name="visibility" class="a-input" x-model="vis">
                            @foreach (PageVisibility::cases() as $v)<option value="{{ $v->value }}">{{ ['public' => 'Public: anyone, search engines allowed', 'unlisted' => 'Unlisted: anyone with the link, not indexed', 'private' => 'Private: needs an access code'][$v->value] }}</option>@endforeach
                        </select>
                    </div>
                    <div x-show="vis === 'private'" x-cloak>
                        <x-admin.field name="access_code" label="Access code" type="text" autocomplete="off" :help="$page->access_code ? 'A code is set. Leave empty to keep it; type a new one to replace it.' : 'Share this with the customer. It is stored hashed and cannot be shown again.'" />
                    </div>
                    <div>
                        <x-admin.field name="expires_at" label="Expires on" type="date" :value="$page->expires_at?->format('Y-m-d')" help="Empty means it is set from the package when you publish." />
                    </div>
                    <x-admin.field name="show_branding" type="checkbox" label="Show “Made with GolpoBari” footer" :value="$page->show_branding" class="sm:col-span-2" />
                </div>
            </section>

            <section class="a-card p-5">
                <h2 class="mb-1 font-semibold">Content</h2>
                <p class="mb-4 text-sm text-stone-500">Plain text only. Line breaks are kept; HTML is removed on save.</p>
                @include('admin.partials.content-form', ['fields' => $fields, 'content' => $page->content])
            </section>

            <section class="a-card p-5" x-data="{ open: {{ $page->theme ? 'true' : 'false' }} }">
                <button type="button" class="flex w-full items-center justify-between font-semibold" @click="open = !open">Colours & fonts <span class="text-sm font-normal text-stone-500" x-text="open ? 'Hide' : '{{ $page->theme ? 'Customised' : 'Template default' }}'"></span></button>
                <div x-show="open" x-collapse>
                    <p class="mt-2 mb-4 text-sm text-stone-500">Leave a value empty to use the template default (shown as the placeholder).</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach (['primary' => 'Primary', 'accent' => 'Accent', 'background' => 'Background', 'text' => 'Text'] as $k => $label)
                            @php $cur = old("theme.$k", $page->theme[$k] ?? ''); @endphp
                            <div x-data="{ v: @js($cur) }">
                                <label class="a-label" for="theme_{{ $k }}">{{ $label }}</label>
                                <div class="flex gap-2">
                                    <input type="color" :value="v || @js($theme[$k] ?? '#000000')" @input="v = $event.target.value" class="h-10 w-12 cursor-pointer rounded-lg ring-1 ring-stone-300">
                                    <input id="theme_{{ $k }}" name="theme[{{ $k }}]" x-model="v" class="a-input font-mono" placeholder="{{ $theme[$k] ?? '' }}" pattern="#[0-9a-fA-F]{6}">
                                    <button type="button" class="a-btn a-btn-ghost a-btn-sm self-center" x-show="v" @click="v = ''">Reset</button>
                                </div>
                                @error("theme.$k")<p class="a-error">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                        @foreach (['font_heading' => 'Heading font', 'font_body' => 'Body font'] as $k => $label)
                            <x-admin.field :name="'theme['.$k.']'" :label="$label" type="select" :value="$page->theme[$k] ?? ''" :options="['' => 'Default ('.($fonts[$theme[$k] ?? ''] ?? '—').')'] + $fonts" />
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="a-card p-5" x-data="{ open: false }">
                <button type="button" class="flex w-full items-center justify-between font-semibold" @click="open = !open">SEO & sharing preview <span class="text-sm font-normal text-stone-500" x-text="open ? 'Hide' : 'Show'"></span></button>
                <div x-show="open" x-collapse>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-admin.field name="seo[title]" label="Meta title" :value="$page->seo['title'] ?? ''" :placeholder="$page->title" />
                        <x-admin.field name="seo[og_title]" label="Share title (Facebook, WhatsApp)" :value="$page->seo['og_title'] ?? ''" :placeholder="$page->title" />
                        <x-admin.field name="seo[description]" label="Meta description" type="textarea" rows="2" :value="$page->seo['description'] ?? ''" />
                        <x-admin.field name="seo[og_description]" label="Share description" type="textarea" rows="2" :value="$page->seo['og_description'] ?? ''" />
                        <x-admin.field name="seo[canonical]" label="Canonical URL" type="url" :value="$page->seo['canonical'] ?? ''" :placeholder="$page->url()" class="sm:col-span-2" />
                        <div class="sm:col-span-2">
                            <label class="a-label">Share image</label>
                            <div class="flex flex-wrap items-center gap-4">
                                @if (! empty($page->seo['og_image']))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($page->seo['og_image']) }}" alt="" class="h-20 w-36 rounded-lg object-cover ring-1 ring-stone-200">
                                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_og_image" value="1" class="accent-stone-900"> Remove</label>
                                @else
                                    <p class="text-sm text-stone-500">None set. The first photo on the page is used.</p>
                                @endif
                                <x-admin.field name="og_image" type="file" class="min-w-60 flex-1" help="1200×630 works best. JPG, PNG or WebP." />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="sticky bottom-[calc(4.25rem+env(safe-area-inset-bottom))] z-20 flex items-center justify-end gap-2 rounded-2xl bg-card/95 p-2 shadow-lg ring-1 ring-stone-200 backdrop-blur lg:bottom-3">
                <a href="{{ route('admin.pages.preview', $page) }}" target="_blank" class="a-btn a-btn-ghost flex-1 sm:flex-none">Preview</a>
                <button class="a-btn a-btn-primary flex-1 sm:flex-none">Save changes</button>
            </div>
        </form>

        {{-- Photos --}}
        <section class="a-card p-5">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-semibold">Photos <span class="text-sm font-normal text-stone-500">({{ $page->media->count() }})</span></h2>
                @if ($page->order)<a href="{{ route('admin.orders.show', $page->order) }}#files" class="text-sm text-stone-500 hover:text-stone-900">Customer's original uploads →</a>@endif
            </div>
            @if ($page->media->isNotEmpty())
                <form method="POST" action="{{ route('admin.pages.media.update', $page) }}">
                    @csrf @method('PUT')
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($page->media as $m)
                            <div class="rounded-lg p-2 ring-1 ring-stone-200" x-data="{ del: false }" :class="del && 'opacity-40'">
                                <a href="{{ $m->url() }}" target="_blank"><img src="{{ $m->url(true) }}" alt="" loading="lazy" class="aspect-[4/3] w-full rounded-md bg-stone-100 object-cover"></a>
                                <div class="mt-2 space-y-1.5">
                                    <input name="media[{{ $m->id }}][caption]" value="{{ $m->caption() }}" maxlength="140" class="a-input !min-h-8 text-xs" placeholder="Caption">
                                    <div class="flex gap-1.5">
                                        <select name="media[{{ $m->id }}][field_key]" class="a-input !min-h-8 flex-1 text-xs">
                                            @foreach ($mediaLabels + (isset($mediaLabels[$m->field_key]) ? [] : [$m->field_key => $m->field_key.' (not in template)']) as $k => $l)<option value="{{ $k }}" @selected($m->field_key === $k)>{{ $l }}</option>@endforeach
                                        </select>
                                        <input type="number" name="media[{{ $m->id }}][sort_order]" value="{{ $m->sort_order }}" min="0" max="9999" class="a-input !min-h-8 w-16 text-xs" title="Order" aria-label="Order">
                                    </div>
                                    <label class="flex items-center gap-1.5 text-xs text-rose-600"><input type="checkbox" name="media[{{ $m->id }}][delete]" value="1" x-model="del" class="accent-rose-600"> Remove from page</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex justify-end"><button class="a-btn a-btn-primary">Save photos</button></div>
                </form>
            @else
                <p class="text-sm text-stone-500">No photos on this page yet.</p>
            @endif

            @if ($mediaFields->isNotEmpty())
                <form method="POST" action="{{ route('admin.pages.media.store', $page) }}" enctype="multipart/form-data" class="mt-5 grid gap-3 border-t border-stone-100 pt-5 sm:grid-cols-[200px_1fr_auto] sm:items-end">
                    @csrf
                    <div><label class="a-label" for="add_field">Add to</label><select id="add_field" name="field_key" class="a-input">@foreach ($mediaLabels as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                    <div><label class="a-label" for="add_files">Photos</label><input id="add_files" type="file" name="files[]" multiple required accept="image/jpeg,image/png,image/webp" class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-stone-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white"></div>
                    <button class="a-btn a-btn-ghost">Upload</button>
                    @error('files.*')<p class="a-error sm:col-span-3">{{ $message }}</p>@enderror
                </form>
            @endif
        </section>
    </div>

    <aside class="space-y-6">
        <section class="a-card p-5" x-data="copyText(@js($page->url()))">
            <div class="flex items-center justify-between"><h2 class="font-semibold">Status</h2><x-admin.badge :color="$s->color()">{{ $s->label() }}</x-admin.badge></div>
            <dl class="a-dl mt-4 space-y-3">
                <div><dt>Address</dt><dd class="break-all font-mono">{{ $page->url() }} <button type="button" class="a-btn a-btn-ghost a-btn-sm ml-1" @click="copy()" x-text="copied ? 'Copied' : 'Copy'"></button></dd></div>
                <div><dt>Visibility</dt><dd>{{ $page->visibility->label() }}@if ($page->visibility === PageVisibility::Private) · {{ $page->access_code ? 'code set' : 'no code yet' }}@endif</dd></div>
                <div><dt>Published</dt><dd>{{ $page->published_at?->format('d M Y, h:i A') ?? 'Not yet' }}</dd></div>
                <div><dt>Expires</dt><dd class="{{ $page->hasExpired() ? 'font-semibold text-rose-600' : '' }}">{{ $page->expires_at ? $page->expires_at->format('d M Y').' ('.$page->expires_at->diffForHumans().')' : 'Set on publish' }}</dd></div>
                @if ($page->order)
                    <div><dt>Order</dt><dd><a href="{{ route('admin.orders.show', $page->order) }}" class="font-mono hover:underline">{{ $page->order->order_number }}</a> · {{ $page->order->customer_name }}</dd></div>
                    <div><dt>Package</dt><dd>{{ $page->order->packageName() }}</dd></div>
                @endif
            </dl>
        </section>

        <section id="renew" class="a-card scroll-mt-20 p-5" x-data="{ mode: 'package' }">
            <h2 class="font-semibold">Renew</h2>
            <p class="mt-1 text-sm text-stone-500">Extends from the current expiry, or from today if it has passed. Re-activates an expired page.</p>
            <form method="POST" action="{{ route('admin.pages.renew', $page) }}" class="mt-3 space-y-3" data-confirm="Renew /{{ $page->slug }}?">
                @csrf
                <div class="flex gap-4 text-sm">
                    <label class="flex items-center gap-1.5"><input type="radio" name="mode" value="package" x-model="mode" class="accent-stone-900"> By package</label>
                    <label class="flex items-center gap-1.5"><input type="radio" name="mode" value="date" x-model="mode" class="accent-stone-900"> Until a date</label>
                </div>
                <select name="package_id" class="a-input" x-show="mode === 'package'">@foreach ($packages as $pk)<option value="{{ $pk->id }}" @selected($page->order?->package_id === $pk->id)>{{ $pk->name }} · {{ $pk->durationLabel() }}</option>@endforeach</select>
                <input type="date" name="until" class="a-input" x-show="mode === 'date'" x-cloak min="{{ now()->addDay()->format('Y-m-d') }}">
                <button class="a-btn a-btn-primary w-full">Renew</button>
            </form>
        </section>

        <section class="a-card p-5">
            <h2 class="font-semibold">Visitors</h2>
            <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-lg bg-stone-50 p-2"><div class="text-lg font-bold tabular-nums">{{ number_format($stats['total']) }}</div><div class="text-xs text-stone-500">Total</div></div>
                <div class="rounded-lg bg-stone-50 p-2"><div class="text-lg font-bold tabular-nums">{{ number_format($stats['unique']) }}</div><div class="text-xs text-stone-500">Unique</div></div>
                <div class="rounded-lg bg-stone-50 p-2"><div class="text-lg font-bold tabular-nums">{{ number_format($stats['last30']) }}</div><div class="text-xs text-stone-500">30 days</div></div>
            </div>
            @if ($stats['devices']->isNotEmpty())
                <p class="mt-3 text-xs text-stone-500">@foreach ($stats['devices'] as $d => $c){{ ucfirst($d ?: 'other') }} {{ $c }}@if (! $loop->last) · @endif @endforeach</p>
            @endif
            @if ($stats['referrers']->isNotEmpty())
                <ul class="mt-2 space-y-0.5 text-xs text-stone-500">@foreach ($stats['referrers'] as $host => $c)<li class="flex justify-between"><span class="truncate">{{ $host }}</span><span class="tabular-nums">{{ $c }}</span></li>@endforeach</ul>
            @endif
            <p class="mt-3 text-xs text-stone-400">Counted once per visitor per day. Bots and admins are skipped.</p>
        </section>

        <section class="a-card space-y-2 p-5">
            <h2 class="mb-2 font-semibold">More</h2>
            <form method="POST" action="{{ route('admin.pages.duplicate', $page) }}" data-confirm="Make a draft copy of this page?">@csrf<button class="a-btn a-btn-ghost w-full">Duplicate as draft</button></form>
            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" data-confirm="{{ $page->order_id ? 'Archive this page? It will stop being visible.' : 'Delete this page and its photos permanently?' }}">@csrf @method('DELETE')<button class="a-btn a-btn-ghost w-full text-rose-700">{{ $page->order_id ? 'Archive page' : 'Delete page' }}</button></form>
        </section>
    </aside>
</div>
@endsection
