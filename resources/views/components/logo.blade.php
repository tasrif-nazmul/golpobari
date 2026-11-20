@props(['class' => 'h-9 w-9', 'tone' => 'sindoor'])
{{-- House + open book: "golpo" (story) + "bari" (home) --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 48 48" fill="none" aria-hidden="true">
    <path d="M6 22 24 7l18 15" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M11 21v17.5c4.5-2.6 9-2.6 13 0 4-2.6 8.5-2.6 13 0V21" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M24 26v12.5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
    <path d="M24 21.6c-1.4-1.9-4.6-1.1-4.1 1.5.4 2 4.1 4.2 4.1 4.2s3.7-2.2 4.1-4.2c.5-2.6-2.7-3.4-4.1-1.5Z" fill="var(--color-sindoor)"/>
</svg>
