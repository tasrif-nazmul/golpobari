@extends('layouts.admin')
@section('title', 'Pages')
@section('content')
@php use App\Enums\PageStatus; use App\Enums\PageVisibility; @endphp
<x-admin.header title="Customer pages" :sub="$pages->total().' pages'" />

<div class="mb-3 flex flex-wrap gap-2">
    <a href="{{ route('admin.pages.index') }}" class="a-btn a-btn-sm {{ $tab === 'all' ? 'a-btn-primary' : 'a-btn-ghost' }}">All <span class="opacity-60">{{ $counts->sum() }}</span></a>
    @foreach (PageStatus::cases() as $s)
        <a href="{{ route('admin.pages.index', ['tab' => $s->value]) }}" class="a-btn a-btn-sm {{ $tab === $s->value ? 'a-btn-primary' : 'a-btn-ghost' }}">{{ $s->label() }} <span class="opacity-60">{{ $counts[$s->value] ?? 0 }}</span></a>
    @endforeach
    <a href="{{ route('admin.pages.index', ['tab' => 'expiring']) }}" class="a-btn a-btn-sm {{ $tab === 'expiring' ? 'a-btn-primary' : 'a-btn-ghost' }}">Expiring soon <span class="{{ $expiringCount ? 'text-orange-500' : 'opacity-60' }}">{{ $expiringCount }}</span></a>
</div>

<form method="GET" class="a-card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-6">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <input name="q" value="{{ request('q') }}" class="a-input lg:col-span-2" placeholder="Slug, title, order ID, customer">
    <select name="category" class="a-input"><option value="">All categories</option>@foreach ($categories as $id => $n)<option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $n }}</option>@endforeach</select>
    <select name="template" class="a-input"><option value="">All templates</option>@foreach ($templates as $id => $n)<option value="{{ $id }}" @selected((string) request('template') === (string) $id)>{{ $n }}</option>@endforeach</select>
    <select name="visibility" class="a-input"><option value="">Any visibility</option>@foreach (PageVisibility::options() as $k => $v)<option value="{{ $k }}" @selected(request('visibility') === $k)>{{ $v }}</option>@endforeach</select>
    <div class="flex gap-2"><button class="a-btn a-btn-primary flex-1">Filter</button>@if (request()->except(['page', 'tab']))<a href="{{ route('admin.pages.index', ['tab' => $tab]) }}" class="a-btn a-btn-ghost">Reset</a>@endif</div>
</form>

<div class="a-card overflow-hidden">
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
                        @if ($p->visibility !== PageVisibility::Public)<div class="mt-1 text-xs text-stone-500">{{ $p->visibility === PageVisibility::Private ? '🔒 Private' : 'Unlisted' }}</div>@endif</td>
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
{{ $pages->links() }}
@endsection
