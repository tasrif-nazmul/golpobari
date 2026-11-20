@extends('layouts.admin')
@section('title', $order->order_number)
@section('content')
@php
    use App\Enums\OrderStatus;
    $money = fn ($v) => '৳'.number_format((float) $v, fmod((float) $v, 1) ? 2 : 0);
    $fields = $order->template?->fields->map->toSchema() ?? collect();
    $mediaByField = $order->media->groupBy('field_key');
    $page = $order->page;
    $paid = $order->payment_status?->value === 'verified';
    $canCreate = ! $page && $paid;
    $pending = $order->payments->first(fn ($p) => $p->status->value === 'pending');
    $closed = in_array($order->status->value, ['cancelled', 'refunded', 'expired'], true);
    $phone = preg_replace('/\D+/', '', (string) $order->customer_phone);
    $wa = 'https://wa.me/'.(str_starts_with($phone, '880') ? $phone : '88'.$phone);

    // Where this order is on the road: received → paid → page made → live
    $at = match (true) {
        $page && $page->isLive() => 4,
        (bool) $page => 3,
        $paid => 2,
        default => 1,
    };
    $road = ['Order received', 'Payment checked', 'Page made', 'Live'];
@endphp

<x-admin.header :title="$order->customer_name" :back="route('admin.orders.index')" :sub="$order->order_number.' · '.$order->templateName().' · '.$order->created_at->diffForHumans()">
    <x-slot:actions>
        @include('admin.partials.order-status', ['order' => $order])
        <a href="tel:{{ $order->customer_phone }}" class="a-btn a-btn-ghost a-btn-sm">Call</a>
        <a href="{{ $wa }}" target="_blank" rel="noopener" class="a-btn a-btn-ghost a-btn-sm">WhatsApp</a>
    </x-slot:actions>
</x-admin.header>

{{-- Progress --}}
@unless ($closed)
<ol class="mb-6 grid grid-cols-4 gap-1">
    @foreach ($road as $n => $label)
        @php $done = $n + 1 < $at || ($at === 4 && $n === 3); $now = $n + 1 === $at && ! $done; @endphp
        <li class="flex flex-col gap-1.5">
            <span class="h-1.5 rounded-full {{ $done ? 'bg-leaf' : ($now ? 'bg-brand' : 'border-t-[1.5px] border-dashed border-stone-300 bg-transparent') }}"></span>
            <span class="text-[11px] font-semibold leading-tight sm:text-xs {{ $done ? 'text-leaf' : ($now ? 'text-brand' : 'text-stone-400') }}">{{ $label }}</span>
        </li>
    @endforeach
</ol>
@endunless

{{-- Next step: the one thing to do now --}}
<div class="mb-6">
    @if ($pending)
        @php $dups = $duplicates[$pending->id] ?? collect(); @endphp
        <section id="payment" class="a-card scroll-mt-20 overflow-hidden ring-amber-300" x-data="{ reject: false }">
            <div class="bg-amber-50 px-5 py-3">
                <h2 class="font-semibold text-amber-950">Next: check this payment in your bKash app</h2>
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div><dt class="text-xs text-stone-500">Amount sent</dt><dd class="text-2xl font-bold tabular-nums {{ $pending->amountMismatch() ? 'text-rose-700' : '' }}">{{ $money($pending->amount) }}</dd>
                        @if ($pending->amountMismatch())<p class="text-xs font-semibold text-rose-700">Price is {{ $money($pending->expected_amount) }}</p>@endif</div>
                    <div x-data="copyText(@js($pending->transaction_id), 'TrxID copied')"><dt class="text-xs text-stone-500">TrxID</dt><dd><button type="button" @click="copy()" class="font-mono text-lg font-bold underline decoration-stone-300 decoration-dashed underline-offset-4 hover:decoration-stone-900">{{ $pending->transaction_id }}</button></dd></div>
                    <div><dt class="text-xs text-stone-500">From</dt><dd class="font-mono">{{ $pending->sender_number }}</dd></div>
                    <div><dt class="text-xs text-stone-500">Sent to</dt><dd class="font-mono">{{ $pending->receiver_number ?: '—' }}</dd></div>
                </dl>
                @if ($dups->isNotEmpty())
                    <p class="mt-4 rounded-lg bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-800 ring-1 ring-rose-200">This TrxID was also used on {{ $dups->map(fn ($d) => $d->order?->order_number)->filter()->implode(', ') }}. Check carefully.</p>
                @endif
                <div class="mt-5 grid gap-2 sm:flex">
                    <form method="POST" action="{{ route('admin.payments.verify', $pending) }}" data-confirm="Did you receive {{ $money($pending->amount) }} from {{ $pending->sender_number }} (TrxID {{ $pending->transaction_id }}) in bKash?">
                        @csrf<input type="hidden" name="make_page" value="1">
                        <button class="a-btn a-btn-success a-btn-lg w-full">Received · make the page</button>
                    </form>
                    <form method="POST" action="{{ route('admin.payments.verify', $pending) }}" data-confirm="Mark {{ $money($pending->amount) }} (TrxID {{ $pending->transaction_id }}) as received?">
                        @csrf<button class="a-btn a-btn-ghost a-btn-lg w-full">Received only</button>
                    </form>
                    <button type="button" class="a-btn a-btn-ghost a-btn-lg text-rose-700" @click="reject = !reject">Not received</button>
                </div>
                <form x-show="reject" x-cloak method="POST" action="{{ route('admin.payments.reject', $pending) }}" class="mt-3 flex gap-2">
                    @csrf
                    <input name="reason" required maxlength="200" class="a-input" placeholder="Reason the customer will see, e.g. TrxID not found">
                    <button class="a-btn a-btn-danger shrink-0">Reject</button>
                </form>
            </div>
        </section>
    @elseif ($canCreate)
        <section class="a-card flex flex-col gap-4 p-5 ring-sky-200 sm:flex-row sm:items-center">
            <div class="flex-1">
                <h2 class="font-semibold">Next: make the page</h2>
                <p class="text-sm text-stone-600">Payment is checked. This builds a draft from {{ $order->customer_name }}'s text and photos. Nothing goes live yet.</p>
            </div>
            <form method="POST" action="{{ route('admin.orders.page', $order) }}">@csrf<button class="a-btn a-btn-brand a-btn-lg w-full">Make page</button></form>
        </section>
    @elseif ($page && in_array($page->status->value, ['draft', 'pending'], true))
        <section class="a-card flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
            <div class="min-w-0 flex-1">
                <h2 class="font-semibold">Next: finish the page and publish</h2>
                <p class="truncate text-sm text-stone-600"><span class="font-mono">/{{ $page->slug }}</span> is a {{ strtolower($page->status->label()) }}. Check the preview, fix anything, then publish.</p>
            </div>
            <div class="grid grid-cols-3 gap-2 sm:flex">
                <a href="{{ route('admin.pages.preview', $page) }}" target="_blank" class="a-btn a-btn-ghost">Preview</a>
                <a href="{{ route('admin.pages.edit', $page) }}" class="a-btn a-btn-primary">Edit</a>
                <form method="POST" action="{{ route('admin.pages.action', $page) }}" data-confirm="Publish /{{ $page->slug }}? It goes live for everyone with the link.">
                    @csrf<input type="hidden" name="action" value="publish"><button class="a-btn a-btn-success w-full">Publish</button>
                </form>
            </div>
        </section>
    @elseif ($page && $page->isLive())
        @include('admin.partials.send-link', ['page' => $page])
    @elseif ($page)
        <section class="a-card flex flex-wrap items-center gap-3 p-5">
            <p class="flex-1 text-sm">Page <span class="font-mono">/{{ $page->slug }}</span> is <x-admin.badge :color="$page->status->color()">{{ $page->status->label() }}</x-admin.badge></p>
            <a href="{{ route('admin.pages.edit', $page) }}" class="a-btn a-btn-primary">Open page</a>
        </section>
    @elseif (! $closed)
        <section class="a-card p-5 text-sm text-stone-600">Waiting for the customer to send their bKash payment.</section>
    @endif
</div>

<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
    <div class="space-y-6">
        {{-- What the customer wrote --}}
        <section class="a-card p-5" x-data="{ edit: false }">
            <div class="flex items-center justify-between"><h2 class="font-semibold">What the customer wrote</h2><button type="button" class="a-btn a-btn-ghost a-btn-sm" @click="edit = !edit" x-text="edit ? 'Cancel' : 'Edit'"></button></div>
            @if ($order->customer_note)<div class="mt-3 rounded-xl bg-amber-50 p-3 text-sm ring-1 ring-amber-200"><p class="text-xs font-semibold uppercase tracking-wide text-amber-900">Note to you</p><p class="mt-0.5 whitespace-pre-line">{{ $order->customer_note }}</p></div>@endif
            <dl x-show="!edit" class="mt-3 divide-y divide-dashed divide-stone-200">
                @foreach ($fields as $f)
                    @continue(in_array($f['type'], ['image', 'gallery'], true))
                    @php $v = $order->content[$f['key']] ?? null; @endphp
                    <div class="py-3 sm:grid sm:grid-cols-[11rem_1fr] sm:gap-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-stone-500 sm:pt-0.5">{{ $f['label'] }}</dt>
                        <dd class="mt-0.5 text-sm sm:mt-0">
                            @if (is_array($v))
                                @if (count($v))
                                    <ol class="list-decimal space-y-1 pl-5">@foreach ($v as $row)<li>{{ implode(' · ', array_filter(array_map('strval', (array) $row))) }}</li>@endforeach</ol>
                                @else <span class="text-stone-400">—</span> @endif
                            @else
                                <span class="whitespace-pre-line">{{ filled($v) ? $v : '—' }}</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
            <form x-show="edit" x-cloak method="POST" action="{{ route('admin.orders.content', $order) }}" class="mt-4">
                @csrf @method('PUT')
                @include('admin.partials.content-form', ['fields' => $fields, 'content' => $order->content])
                <p class="mt-3 text-xs text-stone-500">This edits the order's original content. If the page already exists, edit the page instead.</p>
                <button class="a-btn a-btn-primary mt-4">Save content</button>
            </form>
        </section>

        {{-- Files --}}
        <section id="files" class="a-card scroll-mt-20 p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-semibold">Photos <span class="font-normal text-stone-500">({{ $order->media->count() }})</span></h2>
                @if ($order->media->isNotEmpty())<a href="{{ route('admin.orders.media.zip', $order) }}" class="a-btn a-btn-ghost a-btn-sm">Download all (.zip)</a>@endif
            </div>
            @forelse ($mediaByField as $key => $items)
                <h3 class="mt-4 text-xs font-semibold uppercase tracking-wide text-stone-500">{{ $fields->firstWhere('key', $key)['label'] ?? $key }}</h3>
                <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-5 lg:grid-cols-6">
                    @foreach ($items as $m)
                        <div class="group relative overflow-hidden rounded-xl bg-stone-100 ring-1 ring-stone-200">
                            <a href="{{ $m->url() }}" target="_blank"><img src="{{ $m->url(true) }}" alt="" class="aspect-square w-full object-cover" loading="lazy"></a>
                            <div class="flex items-center justify-between gap-1 px-1.5 py-1 text-[11px] text-stone-500">
                                <span class="truncate">{{ round($m->size / 1024) }}KB</span>
                                <span class="flex gap-2">
                                    <a href="{{ route('admin.orders.media.download', [$order, $m]) }}" class="hover:text-stone-900" title="Download">Save</a>
                                    <form method="POST" action="{{ route('admin.orders.media.destroy', [$order, $m]) }}" data-confirm="Delete this photo permanently?" data-danger>@csrf @method('DELETE')<button class="hover:text-rose-700" title="Delete">Delete</button></form>
                                </span>
                            </div>
                            @if ($m->caption())<p class="truncate px-1.5 pb-1 text-[11px]">{{ $m->caption() }}</p>@endif
                        </div>
                    @endforeach
                </div>
            @empty
                <p class="mt-2 text-sm text-stone-500">No photos uploaded.</p>
            @endforelse
            @php $photoFields = $fields->filter(fn ($f) => in_array($f['type'], ['image', 'gallery'])); @endphp
            @if ($photoFields->isNotEmpty())
            <form method="POST" action="{{ route('admin.orders.media.store', $order) }}" enctype="multipart/form-data" class="mt-5 flex flex-wrap items-end gap-2 border-t border-dashed border-stone-200 pt-4">
                @csrf
                <label class="text-sm">Add photos to
                    <select name="field_key" class="a-input mt-1">@foreach ($photoFields as $f)<option value="{{ $f['key'] }}">{{ $f['label'] }}</option>@endforeach</select>
                </label>
                <input type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp" required class="min-w-0 flex-1 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-stone-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                <button class="a-btn a-btn-ghost">Upload</button>
            </form>
            @endif
        </section>

        {{-- Payment history --}}
        <section class="a-card p-5" @unless ($pending) id="payment" @endunless>
            <h2 class="font-semibold">Payments</h2>
            <div class="mt-3 space-y-3">
                @forelse ($order->payments as $p)
                    <div class="rounded-xl p-4 ring-1 ring-stone-200" x-data="{ refund: false }">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <dl class="a-dl grid flex-1 grid-cols-2 gap-4 sm:grid-cols-4">
                                <div><dt>Amount</dt><dd class="font-bold tabular-nums">{{ $money($p->amount) }}</dd></div>
                                <div><dt>TrxID</dt><dd class="font-mono">{{ $p->transaction_id }}</dd></div>
                                <div><dt>From</dt><dd class="font-mono">{{ $p->sender_number }}</dd></div>
                                <div><dt>Sent</dt><dd>{{ $p->created_at->format('d M, h:i A') }}</dd></div>
                            </dl>
                            <x-admin.badge :color="$p->status->color()">{{ $p->status->label() }}</x-admin.badge>
                        </div>
                        <p class="mt-2 text-xs text-stone-500">
                            @if ($p->verified_at) Checked {{ $p->verified_at->format('d M, h:i A') }} by {{ $p->verifier?->name ?? '—' }}@endif
                            @if ($p->refunded_at) · Refunded {{ $p->refunded_at->format('d M, h:i A') }}@endif</p>
                        @if ($p->rejection_reason)<p class="mt-1 text-xs text-rose-800">Reason: {{ $p->rejection_reason }}</p>@endif
                        @if ($p->refund_note)<p class="mt-1 text-xs text-stone-600">Refund note: {{ $p->refund_note }}</p>@endif
                        @if ($p->status->value === 'rejected')
                            <form method="POST" action="{{ route('admin.payments.verify', $p) }}" class="mt-3" data-confirm="Mark {{ $money($p->amount) }} (TrxID {{ $p->transaction_id }}) as received after all?">@csrf<button class="a-btn a-btn-ghost a-btn-sm">Mark as received after all</button></form>
                        @endif
                        @if ($p->status->value === 'verified')
                            <button type="button" class="mt-3 text-xs font-medium text-stone-500 underline decoration-dashed underline-offset-4 hover:text-rose-700" @click="refund = !refund">Mark refunded…</button>
                            <form x-show="refund" x-cloak method="POST" action="{{ route('admin.payments.refund', $p) }}" class="mt-2 flex gap-2" data-confirm="Mark as refunded? This sets the order to Refunded and suspends its page. Send the money back in bKash yourself." data-danger>
                                @csrf
                                <input name="note" maxlength="200" class="a-input" placeholder="Refund note (optional), e.g. refund TrxID">
                                <button class="a-btn a-btn-danger shrink-0">Mark refunded</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-stone-500">No payment sent yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Side --}}
    <aside class="space-y-6">
        <section class="a-card p-5">
            <h2 class="font-semibold">Customer</h2>
            <dl class="a-dl mt-3 space-y-3">
                <div><dt>Phone</dt><dd class="flex items-center gap-3"><a href="tel:{{ $order->customer_phone }}" class="font-mono hover:underline">{{ $order->customer_phone }}</a><a href="{{ $wa }}" target="_blank" rel="noopener" class="text-xs font-semibold text-leaf hover:underline">WhatsApp</a></dd></div>
                @if ($order->customer_email)<div><dt>Email</dt><dd class="break-all">{{ $order->customer_email }}</dd></div>@endif
                <div><dt>Wanted link</dt><dd class="font-mono">{{ $order->desired_slug ? '/'.$order->desired_slug : '—' }}</dd></div>
                @if ($page)<div><dt>Page</dt><dd><a href="{{ route('admin.pages.edit', $page) }}" class="font-mono hover:underline">/{{ $page->slug }}</a> <x-admin.badge :color="$page->status->color()">{{ $page->status->label() }}</x-admin.badge></dd></div>@endif
            </dl>
        </section>

        <section class="a-card p-5">
            <h2 class="font-semibold">Order</h2>
            <dl class="mt-3 space-y-1.5 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-stone-500">Template</dt><dd class="text-right">{{ $order->templateName() }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-stone-500">Package</dt><dd class="text-right">{{ $order->packageName() }}</dd></div>
                @if ($order->occasion)<div class="flex justify-between gap-3"><dt class="text-stone-500">Occasion</dt><dd class="text-right">{{ $order->occasion }}</dd></div>@endif
                <div class="flex justify-between gap-3 border-t border-dashed border-stone-200 pt-2"><dt class="text-stone-500">Price</dt><dd class="tabular-nums">{{ $money($order->original_price) }}</dd></div>
                @if ((float) $order->discount_amount > 0)<div class="flex justify-between gap-3"><dt class="text-stone-500">Coupon <span class="font-mono">{{ $order->coupon_code }}</span></dt><dd class="tabular-nums text-leaf">−{{ $money($order->discount_amount) }}</dd></div>@endif
                @if ((float) $order->manual_discount > 0)<div class="flex justify-between gap-3"><dt class="text-stone-500">Manual discount</dt><dd class="tabular-nums text-leaf">−{{ $money($order->manual_discount) }}</dd></div>@endif
                <div class="flex justify-between gap-3 text-base font-bold"><dt>Total</dt><dd class="tabular-nums">{{ $money($order->final_price) }}</dd></div>
            </dl>
        </section>

        <section class="a-card p-5">
            <h2 class="font-semibold">Private notes</h2>
            <form method="POST" action="{{ route('admin.orders.notes', $order) }}" class="mt-3">
                @csrf
                <textarea name="admin_notes" rows="3" class="a-input" placeholder="Only admins see this">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                <button class="a-btn a-btn-ghost mt-2 w-full">Save notes</button>
            </form>
        </section>

        <section class="a-card divide-y divide-dashed divide-stone-200">
            <div x-data="{ open: false }" class="p-5">
                <button type="button" class="flex w-full items-center justify-between text-left font-semibold" @click="open = !open">Change status by hand <span class="text-stone-400" x-text="open ? '−' : '+'"></span></button>
                <form x-show="open" x-cloak method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3 space-y-2">
                    @csrf
                    <select name="status" class="a-input">@foreach (OrderStatus::cases() as $s)<option value="{{ $s->value }}" @selected($order->status === $s)>{{ $s->label() }}</option>@endforeach</select>
                    <input name="note" maxlength="250" class="a-input" placeholder="Note (optional)">
                    <button class="a-btn a-btn-primary w-full">Update status</button>
                </form>
            </div>
            <div x-data="{ open: false }" class="p-5">
                <button type="button" class="flex w-full items-center justify-between text-left font-semibold" @click="open = !open">Edit order details <span class="text-stone-400" x-text="open ? '−' : '+'"></span></button>
                <form x-show="open" x-cloak method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-3 space-y-3">
                    @csrf @method('PUT')
                    <x-admin.field name="customer_name" label="Name" :value="$order->customer_name" required />
                    <x-admin.field name="customer_phone" label="Phone" :value="$order->customer_phone" required />
                    <x-admin.field name="customer_email" label="Email" type="email" :value="$order->customer_email" />
                    <x-admin.field name="desired_slug" label="Wanted link" :value="$order->desired_slug" />
                    <x-admin.field name="template_id" label="Template" type="select" :value="$order->template_id" :options="$templates->mapWithKeys(fn ($t) => [$t->id => $t->category?->name.' · '.$t->name])->all()" />
                    <x-admin.field name="package_id" label="Package" type="select" :value="$order->package_id" :options="$packages->mapWithKeys(fn ($p) => [$p->id => $p->name.' · ৳'.number_format((float) $p->price)])->all()" />
                    <x-admin.field name="manual_discount" label="Manual discount (৳)" type="number" step="0.01" :value="(float) $order->manual_discount" help="Recalculates the final price. Doesn't change payments already received." />
                    <button class="a-btn a-btn-primary w-full">Save order</button>
                </form>
            </div>
        </section>

        <section class="a-card p-5">
            <h2 class="font-semibold">History</h2>
            <ol class="mt-3 space-y-3 border-l-[1.5px] border-dashed border-stone-300 pl-4">
                @foreach ($order->histories as $h)
                    <li class="relative text-sm">
                        <span class="absolute -left-[1.3rem] top-1.5 h-2 w-2 rounded-full bg-stone-400 ring-4 ring-card"></span>
                        <p class="font-medium">{{ $h->toLabel() }}</p>
                        @if ($h->note)<p class="text-stone-600">{{ $h->note }}</p>@endif
                        <p class="text-xs text-stone-400">{{ $h->created_at->format('d M, h:i A') }} · {{ $h->admin?->name ?? 'System / customer' }}</p>
                    </li>
                @endforeach
            </ol>
        </section>
    </aside>
</div>
@endsection
