@props(['color' => 'stone', 'dot' => true])
@php
$map = [
    'slate' => ['bg-stone-100 text-stone-700 ring-stone-300', 'bg-stone-400'],
    'stone' => ['bg-stone-100 text-stone-700 ring-stone-300', 'bg-stone-400'],
    'zinc' => ['bg-stone-100 text-stone-500 ring-stone-200', 'bg-stone-300'],
    'amber' => ['bg-amber-50 text-amber-900 ring-amber-300', 'bg-amber-500'],
    'orange' => ['bg-orange-50 text-orange-900 ring-orange-300', 'bg-orange-500'],
    'sky' => ['bg-sky-50 text-sky-900 ring-sky-200', 'bg-sky-600'],
    'violet' => ['bg-indigo-50 text-indigo-900 ring-indigo-200', 'bg-indigo-500'],
    'teal' => ['bg-teal-50 text-teal-900 ring-teal-200', 'bg-teal-600'],
    'emerald' => ['bg-emerald-50 text-emerald-900 ring-emerald-300', 'bg-emerald-600'],
    'rose' => ['bg-rose-50 text-rose-900 ring-rose-200', 'bg-rose-600'],
];
[$cls, $dotCls] = $map[$color] ?? $map['stone'];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset '.$cls]) }}>@if ($dot)<span class="h-1.5 w-1.5 rounded-full {{ $dotCls }}"></span>@endif{{ $slot }}</span>
