{{-- The one button that moves this order forward. Needs $order (with payment, page). --}}
@php
    $st = $order->status->value;
    $pg = $order->page;
@endphp
@if (in_array($st, ['submitted', 'payment_pending'], true) && $order->payment && $order->payment->status->value === 'pending')
    <a href="{{ route('admin.orders.show', $order) }}#payment" class="a-btn a-btn-brand a-btn-sm">Check payment</a>
@elseif (in_array($st, ['payment_verified', 'in_review'], true) && ! $pg)
    <form method="POST" action="{{ route('admin.orders.page', $order) }}">@csrf<button class="a-btn a-btn-brand a-btn-sm">Make page</button></form>
@elseif ($pg && in_array($pg->status->value, ['draft', 'pending'], true))
    <a href="{{ route('admin.pages.edit', $pg) }}" class="a-btn a-btn-primary a-btn-sm">Finish page</a>
@elseif ($pg && $pg->isLive())
    <span x-data="copyText(@js($pg->url()), 'Link copied')"><button type="button" class="a-btn a-btn-ghost a-btn-sm" @click="copy()"><span x-text="copied ? 'Copied' : 'Copy link'"></span></button></span>
@else
    <a href="{{ route('admin.orders.show', $order) }}" class="a-btn a-btn-ghost a-btn-sm">Open</a>
@endif
