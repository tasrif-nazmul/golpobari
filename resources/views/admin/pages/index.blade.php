@extends('layouts.admin')
@section('title', 'Pages')
@section('content')
@php use App\Enums\PageStatus; use App\Enums\PageVisibility; @endphp
<x-admin.header title="Customer pages" :sub="$pages->total().' pages'" />

<nav class="a-scroll mb-4" aria-label="Page status">
    <a href="{{ route('admin.pages.index') }}" class="a-chip" aria-current="{{ $tab === 'all' ? 'true' : 'false' }}">All <span class="n">{{ $counts->sum() }}</span></a>
    @foreach (PageStatus::cases() as $s)
        <a href="{{ route('admin.pages.index', ['tab' => $s->value]) }}" class="a-chip" aria-current="{{ $tab === $s->value ? 'true' : 'false' }}">{{ ['draft' => 'Drafts', 'pending' => 'Ready to publish', 'active' => 'Live'][$s->value] ?? $s->label() }} <span class="n {{ in_array($s->value, ['draft', 'pending'], true) && ($counts[$s->value] ?? 0) && $tab !== $s->value ? 'hot' : '' }}">{{ $counts[$s->value] ?? 0 }}</span></a>
    @endforeach
    <a href="{{ route('admin.pages.index', ['tab' => 'expiring']) }}" class="a-chip" aria-current="{{ $tab === 'expiring' ? 'true' : 'false' }}">Ending soon <span class="n {{ $expiringCount && $tab !== 'expiring' ? 'hot' : '' }}">{{ $expiringCount }}</span></a>
</nav>

<form method="GET" class="a-card mb-4 grid gap-3 p-3 sm:grid-cols-2 sm:p-4 lg:grid-cols-6" x-data="{ more: {{ collect(['category', 'template', 'visibility'])->contains(fn ($k) => filled(request($k))) ? 'true' : 'false' }} }">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div class="flex gap-2 sm:col-span-2">
        <input name="q" value="{{ request('q') }}" class="a-input" placeholder="Link, title, order ID, customer">
        <button type="button" class="a-btn a-btn-ghost sm:hidden" @click="more = !more" aria-label="Filters"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M7 12h10M10 18h4"/></svg></button>
    </div>
    <select name="category" class="a-input" :class="more || 'hidden sm:block'"><option value="">All categories</option>@foreach ($categories as $id => $n)<option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $n }}</option>@endforeach</select>
    <select name="template" class="a-input" :class="more || 'hidden sm:block'"><option value="">All templates</option>@foreach ($templates as $id => $n)<option value="{{ $id }}" @selected((string) request('template') === (string) $id)>{{ $n }}</option>@endforeach</select>
    <select name="visibility" class="a-input" :class="more || 'hidden sm:block'"><option value="">Any visibility</option>@foreach (PageVisibility::options() as $k => $v)<option value="{{ $k }}" @selected(request('visibility') === $k)>{{ $v }}</option>@endforeach</select>
    <div class="gap-2 sm:flex" :class="more ? 'flex' : 'hidden'"><button class="a-btn a-btn-primary flex-1">Filter</button>@if (request()->except(['page', 'tab']))<a href="{{ route('admin.pages.index', ['tab' => $tab]) }}" class="a-btn a-btn-ghost">Reset</a>@endif</div>
</form>

{{-- Phone: cards --}}
<div class="space-y-2 md:hidden">
    @forelse ($pages as $p)
        <div class="a-card p-4">
            <a href="{{ route('admin.pages.edit', $p) }}" class="block">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ $p->order?->customer_name ?? ($p->title ?: 'Untitled') }}</p>
                        <p class="truncate font-mono text-xs text-stone-500">/{{ $p->slug }}</p>
                    </div>
                    <x-admin.badge :color="$p->status->color()">{{ $p->status->label() }}</x-admin.badge>
                </div>
                <p class="mt-1 truncate text-sm text-stone-600">{{ $p->template?->name }}@if ($p->expires_at) · <span class="{{ $p->hasExpired() ? 'text-rose-700' : '' }}">ends {{ $p->expires_at->format('d M Y') }}</span>@endif</p>
            </a>
            <div class="mt-3 flex items-center justify-end gap-2 border-t border-dashed border-stone-200 pt-3">
                @if ($p->isLive())
                    @include('admin.partials.send-link', ['page' => $p, 'compact' => true])
                @else
                    <a href="{{ route('admin.pages.preview', $p) }}" target="_blank" class="a-btn a-btn-ghost a-btn-sm">Preview</a>
                    <a href="{{ route('admin.pages.edit', $p) }}" class="a-btn a-btn-primary a-btn-sm">Edit</a>
                @endif
            </div>
        </div>
    @empty
        <div class="a-card px-5 py-12 text-center text-sm text-stone-500">No pages here yet. Pages are made from a paid order.</div>
    @endforelse
</div>

<div class="a-card hidden overflow-hidden md:block">
    <div class="overflow-x-auto">
        <table class="a-table">
            <thead><tr>
                <th><x-admin.sort-link column="slug" label="Page" /></th>
                <th>Template</th>
                <th>Order</th>
                <th>Status</th>
                <th><x-admin.sort-link column="published_at" label="Published" /></th>
                <th><x-admin.sort-link column="expires_at" label="Expires" /></th>
                <th class="text-right"><x-admin.sort-link column="views_count" label="Views" /></th>
                <th></th>
            </tr></thead>
            <tbody>
            @forelse ($pages as $p)
                @php $soon = $p->expires_at && $p->expires_at->isFuture() && $p->expires_at->lte(now()->addDays(config('golpobari.expiring_soon_days', 7))); @endphp
                <tr>
                    <td>
                        <a href="{{ route('admin.pages.edit', $p) }}" class="font-semibold hover:underline">{{ $p->title ?: 'Untitled' }}</a>
                        <div class="font-mono text-xs text-stone-500">/{{ $p->slug }}</div>
                    </td>
                    <td>{{ $p->template?->name }}<div class="text-xs text-stone-500">{{ $p->template?->category?->name }}</div></td>
                    <td>@if ($p->order)<a href="{{ route('admin.orders.show', $p->order) }}" class="font-mono text-xs hover:underline">{{ $p->order->order_number }}</a><div class="text-xs text-stone-500">{{ $p->order->customer_name }}</div>@else<span class="text-xs text-stone-400">Manual</span>@endif</td>
                    <td><x-admin.badge :color="$p->status->color()">{{ $p->status->label() }}</x-admin.badge>
                        @if ($p->visibility !== PageVisibility::Public)<div class="mt-1 text-xs text-stone-500">{{ $p->visibility === PageVisibility::Private ? 'Private (code)' : 'Unlisted' }}</div>@endif</td>
                    <td class="whitespace-nowrap text-stone-500">{{ $p->published_at?->format('d M Y') ?? '—' }}</td>
                    <td class="whitespace-nowrap {{ $p->hasExpired() ? 'text-rose-600' : ($soon ? 'font-semibold text-orange-600' : 'text-stone-500') }}">
                        {{ $p->expires_at?->format('d M Y') ?? '—' }}
                        @if ($p->expires_at && ! $p->hasExpired())<div class="text-xs">{{ $p->expires_at->diffForHumans(['parts' => 1]) }}</div>@endif
                    </td>
                    <td class="text-right tabular-nums">{{ number_format($p->views_count) }}</td>
                    <td class="whitespace-nowrap text-right">
                        @if ($p->isLive())<a href="{{ $p->url() }}" target="_blank" rel="noopener" class="a-btn a-btn-ghost a-btn-sm">View ↗</a>@endif
                        <a href="{{ route('admin.pages.edit', $p) }}" class="a-btn a-btn-ghost a-btn-sm">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="py-12 text-center text-stone-500">No pages here yet. Pages are created from a verified order.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $pages->links() }}</div>
@endsection
