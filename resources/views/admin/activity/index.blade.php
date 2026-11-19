@extends('layouts.admin')
@section('title', 'Activity')
@section('content')
@php $orderNumbers = \App\Models\Order::whereIn('id', $logs->where('subject_type', 'Order')->pluck('subject_id')->filter())->pluck('order_number', 'id'); @endphp
<x-admin.header title="Activity log" sub="Every admin action: payments, status changes, edits, sign-ins." />
<form method="GET" class="a-card mb-4 grid gap-3 p-4 sm:grid-cols-4">
    <input name="q" value="{{ request('q') }}" class="a-input sm:col-span-2" placeholder="Search description">
    <select name="action" class="a-input"><option value="">All actions</option>@foreach ($actions->map(fn ($a) => explode('.', $a)[0])->unique() as $prefix)<option value="{{ $prefix }}." @selected(request('action') === $prefix.'.')>{{ ucfirst(str_replace('_', ' ', $prefix)) }}</option>@endforeach</select>
    <div class="flex gap-2">
        <select name="admin" class="a-input"><option value="">Everyone</option>@foreach ($admins as $id => $n)<option value="{{ $id }}" @selected((string) request('admin') === (string) $id)>{{ $n }}</option>@endforeach</select>
        <button class="a-btn a-btn-primary">Filter</button>
    </div>
</form>
<div class="a-card overflow-hidden"><div class="overflow-x-auto">
    <table class="a-table">
        <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
        <tbody>
        @forelse ($logs as $l)
            <tr>
                <td class="whitespace-nowrap text-stone-500">{{ $l->created_at?->format('d M Y') }}<div class="text-xs">{{ $l->created_at?->format('h:i:s A') }}</div></td>
                <td class="whitespace-nowrap">{{ $l->admin?->name ?? 'System' }}</td>
                <td><span class="rounded bg-stone-100 px-1.5 py-0.5 font-mono text-xs">{{ $l->action }}</span></td>
                <td class="max-w-md break-words">{{ $l->description }}
                    @if ($l->subject_type === 'Order' && isset($orderNumbers[$l->subject_id]))<a href="{{ route('admin.orders.show', $orderNumbers[$l->subject_id]) }}" class="ml-1 text-xs text-stone-500 underline">open</a>
                    @elseif ($l->subject_type === 'Page' && $l->subject_id && $l->action !== 'page.deleted')<a href="{{ route('admin.pages.edit', $l->subject_id) }}" class="ml-1 text-xs text-stone-500 underline">open</a>@endif
                </td>
                <td class="font-mono text-xs text-stone-500">{{ $l->ip_address }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="py-12 text-center text-stone-500">Nothing logged yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
{{ $logs->links() }}
@endsection
