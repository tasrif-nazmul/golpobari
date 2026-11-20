@extends('layouts.admin')
@section('title', $order->order_number)
@section('content')
@php
    use App\Enums\OrderStatus;
    $money = fn ($v) => '৳'.number_format((float) $v, 2);
    $fields = $order->template?->fields->map->toSchema() ?? collect();
    $mediaByField = $order->media->groupBy('field_key');
    $page = $order->page;
    $canCreate = ! $page && $order->payment_status?->value === 'verified';
@endphp

<x-admin.header :title="$order->order_number" :back="route('admin.orders.index')" :sub="'Placed '.$order->created_at->format('d M Y, h:i A').' · '.$order->created_at->diffForHumans()">
    <x-slot:actions>
        @include('admin.partials.payment-status', ['status' => $order->payment_status])
        @include('admin.partials.order-status', ['order' => $order])
        @if ($page)
            <a href="{{ route('admin.pages.edit', $page) }}" class="a-btn a-btn-primary">Open page editor</a>
        @elseif ($canCreate)
            <form method="POST" action="{{ route('admin.orders.page', $order) }}">@csrf<button class="a-btn a-btn-brand">Create page</button></form>
        @endif
    </x-slot:actions>
</x-admin.header>

@if (! $page && ! $canCreate)
    <div class="mb-5 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200">Next step: verify the bKash payment below. Then you can create the page.</div>
@elseif ($canCreate)
    <div class="mb-5 rounded-lg bg-sky-50 px-4 py-3 text-sm text-sky-900 ring-1 ring-sky-200">Payment verified. Next step: <b>Create page</b> builds a draft from the customer's content and photos.</div>
@endif

<div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
    <div class="space-y-6">
        {{-- Customer + Product --}}
        <div class="grid gap-6 md:grid-cols-2">
            <section class="a-card p-5">
                <h2 class="font-semibold">Customer</h2>
                <dl class="a-dl mt-3 grid grid-cols-2 gap-4">
                    <div class="col-span-2"><dt>Name</dt><dd class="font-medium">{{ $order->customer_name }}</dd></div>
                    <div><dt>Phone</dt><dd><a href="tel:{{ $order->customer_phone }}" class="hover:underline">{{ $order->customer_phone }}</a></dd></div>
                    <div><dt>WhatsApp</dt><dd><a href="https://wa.me/88{{ $order->customer_phone }}" target="_blank" rel="noopener" class="text-emerald-700 hover:underline">Open chat ↗</a></dd></div>
                    <div class="col-span-2"><dt>Email</dt><dd>{{ $order->customer_email ?: '—' }}</dd></div>
                    @if ($order->customer_note)<div class="col-span-2"><dt>Customer note</dt><dd class="whitespace-pre-line rounded-lg bg-amber-50 p-2">{{ $order->customer_note }}</dd></div>@endif
                </dl>
            </section>
            <section class="a-card p-5">
                <h2 class="font-semibold">Product</h2>
                <dl class="a-dl mt-3 grid grid-cols-2 gap-4">
                    <div><dt>Category</dt><dd>{{ $order->categoryName() }}</dd></div>
                    <div><dt>Occasion</dt><dd>{{ $order->occasion ?: '—' }}</dd></div>
                    <div><dt>Template</dt><dd>{{ $order->templateName() }}</dd></div>
                    <div><dt>Package</dt><dd>{{ $order->packageName() }}</dd></div>
                    <div><dt>Requested link</dt><dd class="font-mono">{{ $order->desired_slug ? '/'.$order->desired_slug : '—' }}</dd></div>
                    <div><dt>Coupon</dt><dd class="font-mono">{{ $order->coupon_code ?: '—' }}</dd></div>
                </dl>
                <dl class="mt-4 space-y-1 border-t border-dashed border-stone-200 pt-3 text-sm">
                    <div class="flex justify-between"><dt class="text-stone-500">Original price</dt><dd class="tabular-nums">{{ $money($order->original_price) }}</dd></div>
                    @if ((float) $order->discount_amount > 0)<div class="flex justify-between"><dt class="text-stone-500">Coupon discount</dt><dd class="tabular-nums text-emerald-700">−{{ $money($order->discount_amount) }}</dd></div>@endif
                    @if ((float) $order->manual_discount > 0)<div class="flex justify-between"><dt class="text-stone-500">Manual discount</dt><dd class="tabular-nums text-emerald-700">−{{ $money($order->manual_discount) }}</dd></div>@endif
                    <div class="flex justify-between text-base font-bold"><dt>Final price</dt><dd class="tabular-nums">{{ $money($order->final_price) }}</dd></div>
                </dl>
            </section>
        </div>

        {{-- Payment --}}
        <section class="a-card p-5">
            <h2 class="font-semibold">Payment</h2>
            <div class="mt-3 space-y-4">
                @foreach ($order->payments as $p)
                    <div class="rounded-lg p-4 ring-1 {{ $p->status->value === 'pending' ? 'bg-amber-50/50 ring-amber-200' : 'ring-stone-200' }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <dl class="a-dl grid flex-1 grid-cols-2 gap-4 sm:grid-cols-4">
                                <div><dt>Amount sent</dt><dd class="text-lg font-bold tabular-nums {{ $p->amountMismatch() ? 'text-rose-600' : '' }}">{{ $money($p->amount) }}</dd>@if ($p->amountMismatch())<p class="text-xs font-semibold text-rose-600">Expected {{ $money($p->expected_amount) }}</p>@endif</div>
                                <div><dt>Sender bKash</dt><dd class="font-mono">{{ $p->sender_number }}</dd></div>
                                <div x-data="copyText(@js($p->transaction_id))"><dt>Transaction ID</dt><dd class="font-mono font-semibold">{{ $p->transaction_id }} <button type="button" class="text-xs text-stone-400 hover:text-stone-900" @click="copy()" x-text="copied ? '✓' : 'copy'"></button></dd></div>
                                <div><dt>Sent to</dt><dd class="font-mono">{{ $p->receiver_number ?: '—' }}</dd></div>
                            </dl>
                            <x-admin.badge :color="$p->status->color()">{{ $p->status->label() }}</x-admin.badge>
                        </div>
                        @if ($duplicates[$p->id]->isNotEmpty())
                            <p class="mt-3 rounded-md bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">⚠ Same Transaction ID used on: {{ $duplicates[$p->id]->map(fn ($d) => $d->order?->order_number)->filter()->implode(', ') }}</p>
                        @endif
                        <p class="mt-2 text-xs text-stone-500">Submitted {{ $p->created_at->format('d M Y, h:i A') }}
                            @if ($p->verified_at) · Verified {{ $p->verified_at->format('d M, h:i A') }} by {{ $p->verifier?->name ?? '—' }}@endif
                            @if ($p->refunded_at) · Refunded {{ $p->refunded_at->format('d M, h:i A') }}@endif</p>
                        @if ($p->rejection_reason)<p class="mt-1 text-xs text-rose-700">Reason: {{ $p->rejection_reason }}</p>@endif
                        @if ($p->refund_note)<p class="mt-1 text-xs text-stone-600">Refund note: {{ $p->refund_note }}</p>@endif

                        <div class="mt-4 flex flex-wrap items-start gap-2" x-data="{ reject: false, refund: false }">
                            @if (in_array($p->status->value, ['pending', 'rejected'], true))
                                <form method="POST" action="{{ route('admin.payments.verify', $p) }}" data-confirm="Have you checked bKash and received {{ $money($p->amount) }} from {{ $p->sender_number }} (TrxID {{ $p->transaction_id }})?">@csrf<button class="a-btn a-btn-success">✓ Verify payment</button></form>
                            @endif
                            @if ($p->status->value === 'pending')
                                <button type="button" class="a-btn a-btn-ghost" @click="reject = !reject">Reject…</button>
                            @endif
                            @if ($p->status->value === 'verified')
                                <button type="button" class="a-btn a-btn-ghost" @click="refund = !refund">Mark refunded…</button>
                            @endif
                            <form x-show="reject" x-cloak method="POST" action="{{ route('admin.payments.reject', $p) }}" class="flex w-full gap-2">
                                @csrf
                                <input name="reason" required maxlength="200" class="a-input" placeholder="Reason shown to the customer, e.g. Transaction ID not found">
                                <button class="a-btn a-btn-danger shrink-0">Reject</button>
                            </form>
                            <form x-show="refund" x-cloak method="POST" action="{{ route('admin.payments.refund', $p) }}" class="flex w-full gap-2" data-confirm="Mark as refunded? This sets the order to Refunded and suspends its page. Send the money back in bKash yourself." data-danger>
                                @csrf
                                <input name="note" maxlength="200" class="a-input" placeholder="Refund note (optional), e.g. refund TrxID">
                                <button class="a-btn a-btn-danger shrink-0">Mark refunded</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Delivery --}}
        <section class="a-card p-5">
            <h2 class="font-semibold">Delivery</h2>
            @if ($page)
                <dl class="a-dl mt-3 grid gap-4 sm:grid-cols-4">
                    <div class="sm:col-span-2" x-data="copyText(@js($page->url()))"><dt>Public URL</dt><dd><a href="{{ $page->url() }}" target="_blank" class="font-mono font-semibold hover:underline">{{ $page->url() }}</a> <button type="button" class="a-btn a-btn-ghost a-btn-sm ml-1" @click="copy()" x-text="copied ? 'Copied ✓' : 'Copy'"></button></dd></div>
                    <div><dt>Page status</dt><dd><x-admin.badge :color="$page->status->color()">{{ $page->status->label() }}</x-admin.badge> <span class="text-xs text-stone-500">{{ $page->visibility->label() }}</span></dd></div>
                    <div><dt>Expires</dt><dd>{{ $page->expires_at?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt>Published</dt><dd>{{ $page->published_at?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                    <div><dt>Delivered</dt><dd>{{ $order->delivered_at?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt>Views</dt><dd>{{ number_format($page->views_count) }}</dd></div>
                </dl>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('admin.pages.edit', $page) }}" class="a-btn a-btn-primary">Edit page</a>
                    <a href="{{ route('admin.pages.preview', $page) }}" target="_blank" class="a-btn a-btn-ghost">Preview ↗</a>
                    @if ($page->isLive())
                        <a href="https://wa.me/88{{ $order->customer_phone }}?text={{ rawurlencode('আপনার GolpoBari পেজ তৈরি হয়ে গেছে! 🎉 '.$page->url()) }}" target="_blank" rel="noopener" class="a-btn a-btn-success">Send link on WhatsApp ↗</a>
                    @endif
                </div>
            @else
                <p class="mt-2 text-sm text-stone-500">No page yet.</p>
            @endif
        </section>

        {{-- Files --}}
        <section class="a-card p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-semibold">Uploaded files <span class="font-normal text-stone-500">({{ $order->media->count() }})</span></h2>
                @if ($order->media->isNotEmpty())<a href="{{ route('admin.orders.media.zip', $order) }}" class="a-btn a-btn-ghost a-btn-sm">Download all (.zip)</a>@endif
            </div>
            @forelse ($mediaByField as $key => $items)
                <h3 class="mt-4 text-xs font-semibold uppercase tracking-wide text-stone-500">{{ $fields->firstWhere('key', $key)['label'] ?? $key }}</h3>
                <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-5 lg:grid-cols-6">
                    @foreach ($items as $m)
                        <div class="group relative overflow-hidden rounded-lg bg-stone-100 ring-1 ring-stone-200">
                            <a href="{{ $m->url() }}" target="_blank"><img src="{{ $m->url(true) }}" alt="" class="aspect-square w-full object-cover" loading="lazy"></a>
                            <div class="flex items-center justify-between gap-1 px-1.5 py-1 text-[11px] text-stone-500">
                                <span>{{ $m->width }}×{{ $m->height }} · {{ round($m->size / 1024) }}KB</span>
                                <span class="flex gap-1">
                                    <a href="{{ route('admin.orders.media.download', [$order, $m]) }}" class="hover:text-stone-900" title="Download">⬇</a>
                                    <form method="POST" action="{{ route('admin.orders.media.destroy', [$order, $m]) }}" data-confirm="Delete this photo permanently?" data-danger>@csrf @method('DELETE')<button class="hover:text-rose-600" title="Delete">✕</button></form>
                                </span>
                            </div>
                            @if ($m->caption())<p class="truncate px-1.5 pb-1 text-[11px]">{{ $m->caption() }}</p>@endif
                        </div>
                    @endforeach
                </div>
            @empty
                <p class="mt-2 text-sm text-stone-500">No photos uploaded.</p>
            @endforelse
            <form method="POST" action="{{ route('admin.orders.media.store', $order) }}" enctype="multipart/form-data" class="mt-5 flex flex-wrap items-end gap-2 border-t border-dashed border-stone-200 pt-4">
                @csrf
                <label class="text-sm">Add photos to
                    <select name="field_key" class="a-input mt-1">@foreach ($fields->filter(fn ($f) => in_array($f['type'], ['image', 'gallery'])) as $f)<option value="{{ $f['key'] }}">{{ $f['label'] }}</option>@endforeach</select>
                </label>
                <input type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp" required class="text-sm">
                <button class="a-btn a-btn-ghost">Upload</button>
            </form>
        </section>

        {{-- Customer content --}}
        <section class="a-card p-5" x-data="{ edit: false }">
            <div class="flex items-center justify-between"><h2 class="font-semibold">Customer content</h2><button type="button" class="a-btn a-btn-ghost a-btn-sm" @click="edit = !edit" x-text="edit ? 'Cancel' : 'Edit'"></button></div>
            <dl x-show="!edit" class="mt-3 space-y-4">
                @foreach ($fields as $f)
                    @continue(in_array($f['type'], ['image', 'gallery'], true))
                    @php $v = $order->content[$f['key']] ?? null; @endphp
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-stone-500">{{ $f['label'] }}</dt>
                        <dd class="mt-0.5 text-sm">
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
    </div>

    {{-- Sidebar --}}
    <aside class="space-y-6">
        <section class="a-card p-5">
            <h2 class="font-semibold">Order status</h2>
            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3 space-y-2">
                @csrf
                <select name="status" class="a-input">@foreach (OrderStatus::cases() as $s)<option value="{{ $s->value }}" @selected($order->status === $s)>{{ $s->label() }}</option>@endforeach</select>
                <input name="note" maxlength="250" class="a-input" placeholder="Note (optional)">
                <button class="a-btn a-btn-primary w-full">Update status</button>
            </form>
        </section>

        <section class="a-card p-5">
            <h2 class="font-semibold">Internal notes</h2>
            <form method="POST" action="{{ route('admin.orders.notes', $order) }}" class="mt-3">
                @csrf
                <textarea name="admin_notes" rows="4" class="a-input" placeholder="Only admins can see this">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                <button class="a-btn a-btn-ghost mt-2 w-full">Save notes</button>
            </form>
        </section>

        <section class="a-card p-5" x-data="{ open: false }">
            <button type="button" class="flex w-full items-center justify-between font-semibold" @click="open = !open">Edit order <span x-text="open ? '−' : '+'"></span></button>
            <form x-show="open" x-cloak method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-3 space-y-3">
                @csrf @method('PUT')
                <x-admin.field name="customer_name" label="Name" :value="$order->customer_name" required />
                <x-admin.field name="customer_phone" label="Phone" :value="$order->customer_phone" required />
                <x-admin.field name="customer_email" label="Email" type="email" :value="$order->customer_email" />
                <x-admin.field name="desired_slug" label="Requested link" :value="$order->desired_slug" />
                <x-admin.field name="template_id" label="Template" type="select" :value="$order->template_id" :options="$templates->mapWithKeys(fn ($t) => [$t->id => $t->category?->name.' · '.$t->name])->all()" />
                <x-admin.field name="package_id" label="Package" type="select" :value="$order->package_id" :options="$packages->mapWithKeys(fn ($p) => [$p->id => $p->name.' · ৳'.number_format((float) $p->price)])->all()" />
                <x-admin.field name="manual_discount" label="Manual discount (৳)" type="number" step="0.01" :value="(float) $order->manual_discount" help="Recalculates the final price. Doesn't change payments already received." />
                <button class="a-btn a-btn-primary w-full">Save order</button>
            </form>
        </section>

        <section class="a-card p-5">
            <h2 class="font-semibold">History</h2>
            <ol class="mt-3 space-y-3 border-l-2 border-stone-200 pl-4">
                @foreach ($order->histories as $h)
                    <li class="relative text-sm">
                        <span class="absolute -left-[1.4rem] top-1.5 h-2.5 w-2.5 rounded-full bg-stone-400"></span>
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
