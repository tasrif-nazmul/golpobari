<div class="divide-y divide-dashed divide-ink/15 rounded-[var(--radius-card)] bg-white px-5 shadow-[var(--shadow-soft)] sm:px-7">
    @foreach ($faqs as $faq)
        <details class="group py-1" @if ($loop->first) open @endif>
            <summary class="flex min-h-[3.5rem] cursor-pointer list-none items-center justify-between gap-4 py-3 text-lg font-semibold [&::-webkit-details-marker]:hidden">
                {{ $faq->question }}
                <x-icon name="chevron-down" class="h-5 w-5 shrink-0 text-ink-mute transition group-open:rotate-180" />
            </summary>
            <div class="pb-5 leading-relaxed text-ink-soft">{!! nl2br(e($faq->answer)) !!}</div>
        </details>
    @endforeach
</div>
