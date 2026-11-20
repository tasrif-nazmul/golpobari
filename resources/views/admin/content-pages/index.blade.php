@extends('layouts.admin')
@section('title', 'Site pages')
@section('content')
<x-admin.header title="Site pages" sub="About, policies and terms. Written in Markdown." />
<div class="a-card divide-y divide-stone-100">
    @foreach ($items as $p)
        <div class="flex items-center gap-3 p-4">
            <div class="min-w-0 flex-1"><div class="font-semibold">{{ $p->title }}</div><div class="font-mono text-xs text-stone-500">/{{ $p->slug }} · updated {{ $p->updated_at?->diffForHumans() }}</div></div>
            <a href="{{ url('/'.$p->slug) }}" target="_blank" class="a-btn a-btn-ghost a-btn-sm">View ↗</a>
            <a href="{{ route('admin.content-pages.edit', $p) }}" class="a-btn a-btn-ghost a-btn-sm">Edit</a>
        </div>
    @endforeach
</div>
@endsection
