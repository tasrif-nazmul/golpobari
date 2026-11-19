@extends('layouts.admin')
@section('title', 'Admins')
@section('content')
<x-admin.header title="Admins" sub="Owners manage everything. Staff handle orders, payments and pages.">
    <x-slot:actions><a href="{{ route('admin.admins.create') }}" class="a-btn a-btn-primary">+ Add admin</a></x-slot:actions>
</x-admin.header>
<div class="a-card overflow-hidden"><div class="overflow-x-auto">
    <table class="a-table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last sign-in</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach ($admins as $a)
            <tr>
                <td class="font-semibold">{{ $a->name }} @if ($a->id === auth('admin')->id())<span class="text-xs font-normal text-stone-500">(you)</span>@endif</td>
                <td>{{ $a->email }}</td>
                <td><x-admin.badge :color="$a->role === 'owner' ? 'violet' : 'sky'">{{ ucfirst($a->role) }}</x-admin.badge></td>
                <td class="whitespace-nowrap text-stone-500">{{ $a->last_login_at?->diffForHumans() ?? 'Never' }}@if ($a->last_login_ip)<div class="font-mono text-xs">{{ $a->last_login_ip }}</div>@endif</td>
                <td>@if ($a->is_active)<x-admin.badge color="emerald">Active</x-admin.badge>@else<x-admin.badge>Disabled</x-admin.badge>@endif</td>
                <td class="whitespace-nowrap text-right">
                    <a href="{{ route('admin.admins.edit', $a) }}" class="a-btn a-btn-ghost a-btn-sm">Edit</a>
                    @if ($a->id !== auth('admin')->id())<form method="POST" action="{{ route('admin.admins.destroy', $a) }}" class="inline" data-confirm="Remove {{ $a->name }}'s access?">@csrf @method('DELETE')<button class="a-btn a-btn-ghost a-btn-sm text-rose-600">Remove</button></form>@endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div></div>
@endsection
