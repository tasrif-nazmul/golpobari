@extends('layouts.admin')
@section('title', "Today's work")
@section('content')
@php
    $money = fn ($v) => '৳'.number_format((float) $v, fmod((float) $v, 1) ? 2 : 0);
    $h = (int) now()->format('G');
    $hello = $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
    $me = explode(' ', trim(auth('admin')->user()->name))[0];
    $steps = [
        ['verify', 'Check payment', 'bKash received?', $actions['payments']],
        ['create', 'Make page', 'from the order', $actions['to_create']],
        ['finish', 'Finish & publish', 'edit, then go live', $finishCount],
        ['send', 'Send link', 'to the customer', $justLive->count()],
    ];
    $waiting = $actions['payments'] + $actions['to_create'] + $finishCount;
@endphp

<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="text-sm text-stone-500">{{ now()->format('l, j F') }}</p>
        <h1 class="a-title text-2xl sm:text-3xl">{{ $hello }}, {{ $me }}.</h1>
        <p class="mt-0.5 text-sm text-stone-600">
            @if ($waiting) <b>{{ $waiting }}</b> {{ Str::plural('thing', $waiting) }} waiting for you. @else Nothing is waiting. @endif
            @if ($today) {{ $today }} new {{ Str::plural('order', $today) }} today. @endif
        </p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="a-btn a-btn-ghost hidden sm:inline-flex">All orders</a>
</div>

{{-- The daily flow as four steps, joined by a running stitch. --}}
<ol class="relative mb-8 grid grid-cols-4 gap-2 sm:gap-4">
    <li class="pointer-events-none absolute inset-x-[12%] top-6 border-t-[1.5px] border-dashed border-stone-300 sm:top-7" aria-hidden="true"></li>
    @foreach ($steps as $n => [$key, $label, $hint, $count])
        <li class="relative">
            <a href="#{{ $key }}" class="group flex flex-col items-center text-center">
                <span class="relative grid h-12 w-12 place-items-center rounded-full text-lg font-bold tabular-nums ring-4 ring-paper transition sm:h-14 sm:w-14 sm:text-xl
                    {{ $count ? ($n === 0 ? 'bg-brand text-white' : 'bg-stone-900 text-white') : 'bg-card text-stone-400 ring-paper outline-[1.5px] outline-dashed outline-stone-300' }}">{{ $count }}</span>
                <span class="mt-2 text-xs font-semibold leading-tight sm:text-sm {{ $count ? 'text-stone-900' : 'text-stone-500' }}">{{ $label }}</span>
                <span class="hidden text-xs text-stone-500 sm:block">{{ $hint }}</span>
            </a>
        </li>
    @endforeach
</ol>

<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
    <div class="space-y-6">
        @if (! $waiting && $justLive->isEmpty())
            <section class="a-card flex flex-col items-center px-6 py-12 text-center">
                <svg class="h-16 w-16 text-stone-300" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="4 3" stroke-linecap="round" aria-hidden="true"><circle cx="32" cy="32" r="28"/><path d="M20 33l8 8 16-17" stroke-dasharray="none" stroke-width="3" class="text-leaf" stroke="var(--color-leaf)"/></svg>
                <p class="a-title mt-4 text-xl">All caught up</p>
                <p class="mt-1 max-w-sm text-sm text-stone-500">New orders show up here as soon as a customer sends their bKash payment.</p>
            </section>
        @endif

        {{-- 1. Payments --}}
        @if ($toVerify->isNotEmpty())
            <section id="verify" class="a-card scroll-mt-20 overflow-hidden">
                <header class="flex items-center justify-between gap-3 px-4 pt-4 pb-3 sm:px-5">
                    <div>
                        <h2 class="font-semibold"><span class="text-brand">1.</span> Check these in your bKash app</h2>
                        <p class="text-xs text-stone-500">Match the amount and TrxID, then tap Received. A page draft is made for you.</p>
                    </div>
                    @if ($actions['payments'] > $toVerify->count())<a href="{{ route('admin.payments.index') }}" class="shrink-0 text-sm font-medium text-stone-500 hover:text-stone-900">All {{ $actions['payments'] }}</a>@endif
                </header>
                <div class="a-stitch-t">
                    @foreach ($toVerify as $p)
                        @php $o = $p->order; $dup = in_array($p->transaction_id, $dupes, true); @endphp
                        <div class="a-row" x-data="{ reject: false }">
                            <div class="flex min-w-0 flex-1 items-start gap-4">
                                <div class="w-24 shrink-0">
                                    <p class="text-xl font-bold tabular-nums {{ $p->amountMismatch() ? 'text-rose-700' : '' }}">{{ $money($p->amount) }}</p>
                                    @if ($p->amountMismatch())<p class="text-[11px] font-semibold leading-tight text-rose-700">should be {{ $money($p->expected_amount) }}</p>@endif
                                </div>
                                <div class="min-w-0 flex-1 text-sm">
                                    <p class="truncate font-semibold">{{ $o->customer_name }} <a href="{{ route('admin.orders.show', $o) }}" class="ml-1 font-mono text-xs font-normal text-stone-500 hover:underline">{{ $o->order_number }}</a></p>
                                    <p class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-stone-600">
                                        <span>from <span class="font-mono">{{ $p->sender_number }}</span></span>
                                        <span x-data="copyText(@js($p->transaction_id), 'TrxID copied')">TrxID <button type="button" @click="copy()" class="font-mono font-semibold text-stone-900 underline decoration-stone-300 decoration-dashed underline-offset-4 hover:decoration-stone-900" title="Copy">{{ $p->transaction_id }}</button></span>
                                    </p>
                                    <p class="mt-0.5 text-xs text-stone-500">{{ $o->templateName() }} · {{ $p->created_at->diffForHumans() }}</p>
                                    @if ($dup)<p class="mt-1 text-xs font-semibold text-rose-700">This TrxID was used on another order too. Check carefully.</p>@endif
                                </div>
                            </div>
                            <div class="flex shrink-0 flex-col gap-2 sm:w-auto">
                                <div class="grid grid-cols-[1fr_auto] gap-2">
                                    <form method="POST" action="{{ route('admin.payments.verify', $p) }}" data-confirm="Did you receive {{ $money($p->amount) }} from {{ $p->sender_number }} (TrxID {{ $p->transaction_id }}) in bKash?">
                                        @csrf<input type="hidden" name="make_page" value="1">
                                        <button class="a-btn a-btn-success w-full">Received · make page</button>
                                    </form>
                                    <button type="button" class="a-btn a-btn-ghost" @click="reject = !reject" :aria-expanded="reject">Reject</button>
                                </div>
                                <form x-show="reject" x-cloak method="POST" action="{{ route('admin.payments.reject', $p) }}" class="flex gap-2">
                                    @csrf
                                    <input name="reason" required maxlength="200" class="a-input" placeholder="Reason, e.g. TrxID not found">
                                    <button class="a-btn a-btn-danger shrink-0">Reject</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- 2. Verified, no page yet --}}
        @if ($toCreate->isNotEmpty())
            <section id="create" class="a-card scroll-mt-20 overflow-hidden">
                <header class="px-4 pt-4 pb-3 sm:px-5">
                    <h2 class="font-semibold"><span class="text-brand">2.</span> Paid, page not made yet</h2>
                    <p class="text-xs text-stone-500">Creates a draft from the customer's text and photos. Nothing goes live yet.</p>
                </header>
                <div class="a-stitch-t">
                    @foreach ($toCreate as $o)
                        <div class="a-row">
                            <div class="min-w-0 flex-1 text-sm">
                                <p class="truncate font-semibold">{{ $o->customer_name }} <a href="{{ route('admin.orders.show', $o) }}" class="ml-1 font-mono text-xs font-normal text-stone-500 hover:underline">{{ $o->order_number }}</a></p>
                                <p class="text-xs text-stone-500">{{ $o->templateName() }} · paid {{ $o->updated_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.orders.show', $o) }}" class="a-btn a-btn-ghost flex-1 sm:flex-none">Open order</a>
                                <form method="POST" action="{{ route('admin.orders.page', $o) }}" class="flex-1 sm:flex-none">@csrf<button class="a-btn a-btn-brand w-full">Make page</button></form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- 3. Drafts to finish --}}
        @if ($toFinish->isNotEmpty())
            <section id="finish" class="a-card scroll-mt-20 overflow-hidden">
                <header class="flex items-center justify-between gap-3 px-4 pt-4 pb-3 sm:px-5">
                    <div>
                        <h2 class="font-semibold"><span class="text-brand">3.</span> Finish and publish</h2>
                        <p class="text-xs text-stone-500">Look at the preview, fix anything, then Publish.</p>
                    </div>
                    @if ($finishCount > $toFinish->count())<a href="{{ route('admin.pages.index', ['tab' => 'draft']) }}" class="shrink-0 text-sm font-medium text-stone-500 hover:text-stone-900">All {{ $finishCount }}</a>@endif
                </header>
                <div class="a-stitch-t">
                    @foreach ($toFinish as $pg)
                        <div class="a-row">
                            <div class="min-w-0 flex-1 text-sm">
                                <p class="flex items-center gap-2"><a href="{{ route('admin.pages.edit', $pg) }}" class="truncate font-semibold hover:underline">{{ $pg->title ?: 'Untitled' }}</a>
                                    <x-admin.badge :color="$pg->status->color()">{{ $pg->status === \App\Enums\PageStatus::Pending ? 'Ready' : 'Draft' }}</x-admin.badge></p>
                                <p class="truncate text-xs text-stone-500"><span class="font-mono">/{{ $pg->slug }}</span> · {{ $pg->order?->customer_name }} · {{ $pg->template?->name }}</p>
                            </div>
                            <div class="grid grid-cols-3 gap-2 sm:flex">
                                <a href="{{ route('admin.pages.preview', $pg) }}" target="_blank" class="a-btn a-btn-ghost">Preview</a>
                                <a href="{{ route('admin.pages.edit', $pg) }}" class="a-btn a-btn-ghost">Edit</a>
                                <form method="POST" action="{{ route('admin.pages.action', $pg) }}" data-confirm="Publish /{{ $pg->slug }}? It goes live for everyone with the link.">
                                    @csrf<input type="hidden" name="action" value="publish">
                                    <button class="a-btn a-btn-success w-full">Publish</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- 4. Just published: send the link --}}
        @if ($justLive->isNotEmpty())
            <section id="send" class="a-card scroll-mt-20 overflow-hidden">
                <header class="px-4 pt-4 pb-3 sm:px-5">
                    <h2 class="font-semibold"><span class="text-brand">4.</span> Live now, send the link</h2>
                    <p class="text-xs text-stone-500">Published in the last 3 days.</p>
                </header>
                <div class="a-stitch-t">
                    @foreach ($justLive as $pg)
                        <div class="a-row">
                            <div class="min-w-0 flex-1 text-sm">
                                <p class="truncate font-semibold">{{ $pg->order?->customer_name }} <span class="ml-1 text-xs font-normal text-stone-500">{{ $pg->published_at->diffForHumans() }}</span></p>
                                <a href="{{ $pg->url() }}" target="_blank" rel="noopener" class="block truncate font-mono text-xs text-stone-600 hover:underline">{{ $pg->url() }}</a>
                            </div>
                            @include('admin.partials.send-link', ['page' => $pg, 'compact' => true])
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Recent orders --}}
        <section class="a-card overflow-hidden">
            <header class="flex items-center justify-between px-4 py-3 sm:px-5"><h2 class="font-semibold">Latest orders</h2><a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-stone-500 hover:text-stone-900">All orders</a></header>
            <ul class="a-stitch-t">
                @forelse ($recent as $o)
                    <li class="a-stitch-b last:border-0">
                        <a href="{{ route('admin.orders.show', $o) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-stone-50 sm:px-5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $o->customer_name }} <span class="ml-1 font-mono text-xs font-normal text-stone-500">{{ $o->order_number }}</span></p>
                                <p class="truncate text-xs text-stone-500">{{ $o->templateName() }} · {{ $o->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="hidden text-sm tabular-nums text-stone-600 sm:block">{{ $money($o->final_price) }}</span>
                            @include('admin.partials.order-status', ['order' => $o])
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-sm text-stone-500">No orders yet. Share the site link to get the first one.</li>
                @endforelse
            </ul>
        </section>
    </div>

    <aside class="space-y-6">
        <section class="a-card overflow-hidden bg-stone-950 text-stone-200 ring-stone-950">
            <div class="p-5">
                <p class="text-xs font-medium uppercase tracking-[.14em] text-stone-400">Earned in {{ now()->format('F') }}</p>
                <p class="a-title mt-1 text-3xl text-white tabular-nums">{{ $money($revenue['month']) }}</p>
                <p class="mt-1 text-xs text-stone-400">Verified bKash payments only</p>
            </div>
            <dl class="grid grid-cols-2 gap-px border-t border-dashed border-white/10 text-sm">
                <div class="p-4"><dt class="text-xs text-stone-400">All time</dt><dd class="font-semibold tabular-nums text-white">{{ $money($revenue['verified']) }}</dd></div>
                <div class="p-4"><dt class="text-xs text-stone-400">Waiting check</dt><dd class="font-semibold tabular-nums text-holud">{{ $money($revenue['pending']) }}</dd></div>
                <div class="p-4 pt-0"><dt class="text-xs text-stone-400">Refunded</dt><dd class="font-semibold tabular-nums">{{ $money($revenue['refunded']) }}</dd></div>
                <div class="p-4 pt-0"><dt class="text-xs text-stone-400">Discounts</dt><dd class="font-semibold tabular-nums">{{ $money($revenue['discounts']) }}</dd></div>
            </dl>
        </section>

        <section class="a-card overflow-hidden">
            <header class="flex items-center justify-between px-5 py-3">
                <h2 class="font-semibold">Ending soon</h2>
                <a href="{{ route('admin.pages.index', ['tab' => 'expiring']) }}" class="text-sm font-medium text-stone-500 hover:text-stone-900">All</a>
            </header>
            <ul class="a-stitch-t">
                @forelse ($expiring as $p)
                    <li class="a-stitch-b last:border-0">
                        <a href="{{ route('admin.pages.edit', $p) }}#renew" class="flex items-center justify-between gap-3 px-5 py-3 text-sm hover:bg-stone-50">
                            <span class="min-w-0"><span class="block truncate font-medium">{{ $p->order?->customer_name ?? $p->title }}</span><span class="block truncate font-mono text-xs text-stone-500">/{{ $p->slug }}</span></span>
                            <span class="shrink-0 text-right text-xs font-semibold text-orange-700">{{ $p->expires_at->diffForHumans(['parts' => 1]) }}<span class="block font-normal text-stone-500">Renew</span></span>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-sm text-stone-500">No page ends this week.</li>
                @endforelse
            </ul>
            @if ($actions['expired_unmarked'])
                <p class="a-stitch-t px-5 py-3 text-xs text-rose-800">{{ $actions['expired_unmarked'] }} page(s) are past their date but still live. The daily cron job may not be running.</p>
            @endif
        </section>

        <section class="a-card p-5">
            <h2 class="mb-3 font-semibold">At a glance</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                @foreach ([
                    ['Orders', $stats['total_orders'], route('admin.orders.index')],
                    ['Live pages', $stats['active_pages'], route('admin.pages.index', ['tab' => 'active'])],
                    ['Delivered', $stats['delivered'], route('admin.orders.index', ['stage' => 'live'])],
                    ['Expired', $stats['expired_pages'], route('admin.pages.index', ['tab' => 'expired'])],
                    ['Coupon uses', $stats['coupon_uses'], route('admin.coupons.index')],
                    ['In progress', $stats['in_production'], route('admin.orders.index', ['stage' => 'making'])],
                ] as [$label, $value, $url])
                    <a href="{{ $url }}" class="group"><dt class="text-xs text-stone-500 group-hover:text-stone-900">{{ $label }}</dt><dd class="text-lg font-bold tabular-nums">{{ number_format($value) }}</dd></a>
                @endforeach
            </dl>
        </section>
    </aside>
</div>
@endsection
