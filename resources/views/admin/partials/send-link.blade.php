{{--
    "Send to customer" box: link, ready-made Bangla message, WhatsApp, share sheet.
    Needs $page (live). Uses $page->order when present.
    Optional: $compact (bool) for a one-line version in lists.
--}}
@php
    $o = $page->order;
    $name = $o?->customer_name;
    $phone = $o ? preg_replace('/\D+/', '', (string) $o->customer_phone) : null;
    $waPhone = $phone ? (str_starts_with($phone, '880') ? $phone : '88'.$phone) : null;
    $msg = ($name ? $name.', ' : '').'আপনার GolpoBari পেজ তৈরি হয়ে গেছে। এই লিংকে দেখুন:';
    $tail = collect([
        $page->visibility === \App\Enums\PageVisibility::Private ? 'পেজটি খুলতে আপনার অ্যাক্সেস কোড লাগবে।' : null,
        $page->expires_at ? 'পেজটি '.$page->expires_at->format('d M Y').' পর্যন্ত চালু থাকবে।' : null,
        'ধন্যবাদ!',
    ])->filter()->implode(' ');
    $full = $msg."\n".$page->url()."\n\n".$tail;
    $wa = $waPhone ? 'https://wa.me/'.$waPhone.'?text='.rawurlencode($full) : 'https://wa.me/?text='.rawurlencode($full);
    $compact = $compact ?? false;
@endphp
@if ($compact)
    <div class="flex flex-wrap gap-1.5" x-data="copyText(@js($page->url()), 'Link copied')">
        <button type="button" class="a-btn a-btn-ghost a-btn-sm" @click="copy()"><span x-text="copied ? 'Copied' : 'Copy link'"></span></button>
        <a href="{{ $wa }}" target="_blank" rel="noopener" class="a-btn a-btn-success a-btn-sm">WhatsApp</a>
    </div>
@else
    <section class="a-card overflow-hidden ring-emerald-300" x-data="copyText(@js($page->url()), 'Link copied')">
        <div class="flex items-center gap-3 bg-emerald-50 px-5 py-3">
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-leaf text-white"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg></span>
            <div class="min-w-0">
                <h2 class="font-semibold text-emerald-950">The page is live. Send it to {{ $name ?: 'the customer' }}.</h2>
                @if ($o)<p class="text-xs text-emerald-900/70">{{ $o->customer_phone }}</p>@endif
            </div>
        </div>
        <div class="space-y-4 p-5">
            <div class="flex items-center gap-2 rounded-xl bg-stone-50 p-1.5 pl-3 ring-1 ring-stone-200">
                <a href="{{ $page->url() }}" target="_blank" rel="noopener" class="min-w-0 flex-1 truncate font-mono text-sm font-semibold hover:underline">{{ $page->url() }}</a>
                <button type="button" class="a-btn a-btn-primary a-btn-sm shrink-0" @click="copy()"><span x-text="copied ? 'Copied' : 'Copy link'"></span></button>
            </div>
            <div>
                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-stone-500">Message</p>
                <p class="whitespace-pre-line rounded-xl border-[1.5px] border-dashed border-stone-300 px-3 py-2.5 text-sm leading-relaxed" lang="bn">{{ $full }}</p>
            </div>
            <div class="grid gap-2 sm:grid-cols-3" x-data="shareLink(@js($page->url()), @js($msg))">
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="a-btn a-btn-success">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1112 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 01-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 00-.7.3 3 3 0 00-.9 2.2 5.2 5.2 0 001.1 2.7 11.8 11.8 0 004.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2s.2-1.1.1-1.2-.2-.2-.4-.3z"/></svg>
                    Send on WhatsApp
                </a>
                <button type="button" class="a-btn a-btn-ghost" @click="copy(@js($full))">Copy message</button>
                <button type="button" class="a-btn a-btn-ghost" x-show="canShare" x-cloak @click="share()">Share…</button>
                <a href="{{ $page->url() }}" target="_blank" rel="noopener" class="a-btn a-btn-ghost" x-show="!canShare">Open page</a>
            </div>
        </div>
    </section>
@endif
