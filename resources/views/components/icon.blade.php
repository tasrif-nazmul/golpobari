@props(['name', 'class' => 'h-6 w-6'])
@php
$paths = [
    'sparkle' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6"/>',
    'phone' => '<rect x="7" y="2.5" width="10" height="19" rx="2.5"/><path d="M11 18.5h2"/>',
    'code-off' => '<path d="m8 8-4 4 4 4M16 8l4 4-4 4M3 3l18 18"/>',
    'bolt' => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>',
    'taka' => '<path d="M7 4c2 0 3 1 3 3v9a3 3 0 0 0 6 0v-1M5 10h9"/>',
    'heart' => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z"/>',
    'shield' => '<path d="M12 3 5 6v5c0 4.5 3 8 7 10 4-2 7-5.5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
    'link' => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
    'check' => '<path d="m5 12 5 5 9-10"/>',
    'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    'upload' => '<path d="M12 16V4M7 9l5-5 5 5M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/>',
    'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
    'copy' => '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/>',
    'desktop' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
    'mobile' => '<rect x="7" y="2.5" width="10" height="19" rx="2.5"/>',
    'lock' => '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
    'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
    'chevron-left' => '<path d="m15 6-6 6 6 6"/>',
    'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 9"/>',
];
@endphp
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? '' !!}</svg>
