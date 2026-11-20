@extends('layouts.admin')
@section('title', 'Packages')
@section('content')
<x-admin.header title="Packages" sub="Prices and durations. Existing orders keep the price they were placed at.">
    <x-slot:actions><a href="{{ route('admin.packages.create') }}" class="a-btn a-btn-primary">+ New package</a></x-slot:actions>
</x-admin.header>
<div class="a-card overflow-hidden"><div class="overflow-x-auto">
    <table class="a-table">
        <thead><tr><th class="w-10"></th><th>Package</th><th>Duration</th><th>Price</th><th>Photos</th><th>Branding</th><th>Orders</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($packages as $p)
            <tr>
                <td>@include('admin.partials.reorder', ['type' => 'packages', 'id' => $p->id, 'first' => $loop->first, 'last' => $loop->last])</td>
                <td><div class="font-semibold">{{ $p->name }} @if ($p->is_featured)<x-admin.badge color="amber">Popular</x-admin.badge>@endif</div><div class="text-xs text-stone-500">{{ $p->name_bn }}</div></td>
                <td>{{ $p->duration_value }} {{ $p->duration_unit }}{{ $p->duration_value > 1 ? 's' : '' }}</td>
                <td class="tabular-nums"><span class="font-semibold">৳{{ number_format((float) $p->price) }}</span>@if ($p->compare_price)<span class="ml-1 text-xs text-stone-400 line-through">৳{{ number_format((float) $p->compare_price) }}</span>@endif</td>
                <td>{{ $p->maxPhotos() ?? 'Template max' }}</td>
                <td>{{ $p->showsBranding() ? 'Shown' : 'Removed' }}</td>
                <td class="tabular-nums">{{ $p->orders_count }}</td>
                <td>@if ($p->is_active)<x-admin.badge color="emerald">Active</x-admin.badge>@else<x-admin.badge>Hidden</x-admin.badge>@endif</td>
                <td class="whitespace-nowrap text-right">
                    <a href="{{ route('admin.packages.edit', $p) }}" class="a-btn a-btn-ghost a-btn-sm">Edit</a>
                    @unless ($p->orders_count)<form method="POST" action="{{ route('admin.packages.destroy', $p) }}" class="inline" data-confirm="Delete {{ $p->name }}?">@csrf @method('DELETE')<button class="a-btn a-btn-ghost a-btn-sm text-rose-600">Delete</button></form>@endunless
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="py-12 text-center text-stone-500">No packages yet. Customers can't order until you add one.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
