{{--
    Extra surprise sections for personal pages (App\Support\Surprises): quiz, memory jar,
    open-when letters, coupons and a hold-to-reveal finale. Each shows only when it has content.
    Optional: $only = [keys] to render a subset here.
--}}
@php
    $layout = $template->layout ?? null;
    $keys = array_values(array_intersect(\App\Support\Surprises::keys($layout), $only ?? \App\Support\Surprises::keys($layout)));
    $copy = \App\Support\Surprises::copy($layout);
    // Dark page backgrounds (Eid night, tribute…) get accent-coloured headings so they stay readable.
    $bg = ltrim($d->theme('background', '#ffffff'), '#');
    $dark = strlen($bg) === 6 && (0.299 * hexdec(substr($bg, 0, 2)) + 0.587 * hexdec(substr($bg, 2, 2)) + 0.114 * hexdec(substr($bg, 4, 2))) < 110;
    $hc = $dark ? 't-accent' : 't-primary';
    $env = ['#E76F51', '#2A9D8F', '#E9C46A', '#8E5BB5', '#EF476F', '#118AB2'];
@endphp
@foreach ($keys as $key)
    @continue(! $d->has($key))
    @php [$title, $sub] = $copy[$key]; @endphp

    @if ($key === 'quiz')
        @php
            $qs = array_values(array_filter($d->items('quiz'), fn ($r) => trim($r['q'] ?? '') !== '' && trim($r['a'] ?? '') !== ''));
        @endphp
        @if ($qs)
            <section class="t-sp {{ $dark ? 't-sp--dark' : '' }} mx-auto mt-24 max-w-xl text-center" {{ $d->f('quiz') }}>
                <p class="t-sp__kicker">চমক · কুইজ</p>
                <h2 class="t-section-title font-heading text-4xl {{ $hc }} sm:text-5xl">{{ $title }}</h2>
                <p class="mt-2 text-soft">{{ $sub }}</p>
                <div class="t-quiz t-reveal mt-8" data-quiz>
                    <div class="t-quiz__bar" aria-hidden="true"><i data-quiz-bar></i></div>
                    @foreach ($qs as $i => $r)
                        <div class="t-quiz__q" data-quiz-q @if ($i) hidden @endif>
                            <p class="t-quiz__n">প্রশ্ন {{ \App\Support\Bn::digits($i + 1) }} / {{ \App\Support\Bn::digits(count($qs)) }}</p>
                            <p class="font-heading mt-2 text-2xl leading-snug t-primary sm:text-3xl">{{ $r['q'] }}</p>
                            <div class="mt-6 grid gap-3" data-quiz-opts>
                                @foreach (array_filter(['a' => $r['a'] ?? '', 'b' => $r['b'] ?? '', 'c' => $r['c'] ?? ''], fn ($v) => trim($v) !== '') as $k => $opt)
                                    <button type="button" class="t-quiz__opt" data-quiz-opt @if ($k === 'a') data-ok @endif>{{ $opt }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <div class="t-quiz__end" data-quiz-end hidden>
                        <p class="text-6xl" aria-hidden="true" data-quiz-emoji>🏆</p>
                        <p class="font-heading mt-3 text-5xl t-primary"><span data-quiz-score>০</span>/{{ \App\Support\Bn::digits(count($qs)) }}</p>
                        <p class="mt-2 text-lg" data-quiz-msg></p>
                        <button type="button" class="t-btn t-btn--ghost mt-6" data-quiz-again>আবার খেলো ↺</button>
                    </div>
                </div>
            </section>
        @endif

    @elseif ($key === 'memory_jar')
        @php $notes = array_values(array_filter(array_map(fn ($r) => trim($r['text'] ?? ''), $d->items('memory_jar')))); @endphp
        @if ($notes)
            <section class="t-sp {{ $dark ? 't-sp--dark' : '' }} mx-auto mt-24 max-w-xl text-center" {{ $d->f('memory_jar') }}>
                <p class="t-sp__kicker">চমক · বয়াম</p>
                <h2 class="t-section-title font-heading text-4xl {{ $hc }} sm:text-5xl">{{ $title }}</h2>
                <p class="mt-2 text-soft">{{ $sub }}</p>
                <div class="t-jar-wrap t-reveal mt-10" data-jar>
                    <template data-jar-notes>@foreach ($notes as $n)<p>{{ $n }}</p>@endforeach</template>
                    <button type="button" class="t-jar" data-jar-open aria-label="বয়াম থেকে একটা চিরকুট বের করো">
                        <span class="t-jar__lid"></span>
                        <span class="t-jar__body">
                            @foreach (array_slice($notes, 0, 9) as $i => $n)<span class="t-jar__slip" style="--i: {{ $i }}; left: {{ [14, 52, 30, 60, 10, 42, 22, 56, 36][$i] }}%; bottom: {{ [6, 8, 20, 24, 30, 36, 46, 50, 60][$i] }}%"></span>@endforeach
                            <span class="t-jar__label">{{ \App\Support\Surprises::tone($layout) === 'festival' ? 'শুভকামনা' : 'স্মৃতি' }}</span>
                        </span>
                    </button>
                    <div class="t-jar__note" data-jar-out aria-live="polite" hidden></div>
                    <p class="mt-4 text-sm font-semibold text-soft" data-jar-left>{{ \App\Support\Bn::digits(count($notes)) }}টা চিরকুট আছে</p>
                </div>
            </section>
        @endif

    @elseif ($key === 'open_when')
        @php $letters = array_values(array_filter($d->items('open_when'), fn ($r) => trim($r['when'] ?? '') !== '')); @endphp
        @if ($letters)
            <section class="t-sp {{ $dark ? 't-sp--dark' : '' }} mx-auto mt-24 max-w-4xl text-center" {{ $d->f('open_when') }}>
                <p class="t-sp__kicker">চমক · চিঠি</p>
                <h2 class="t-section-title font-heading text-4xl {{ $hc }} sm:text-5xl">{{ $title }}</h2>
                <p class="mt-2 text-soft">{{ $sub }}</p>
                <div class="mt-10 grid grid-cols-2 gap-4 sm:gap-6">
                    @foreach ($letters as $i => $n)
                        <button type="button" class="t-flip t-reveal" data-flip aria-pressed="false" style="--i: {{ $i }}">
                            <span class="t-flip__inner min-h-[11rem]">
                                <span class="t-flip__face t-ow grid place-items-center p-4 text-white" style="--ow: {{ $env[$i % count($env)] }}">
                                    <span><span class="t-ow__seal" aria-hidden="true">♥</span><span class="mt-3 block text-xs font-semibold uppercase tracking-widest opacity-80">যখন</span><span class="font-heading block text-xl leading-snug sm:text-2xl">{{ $n['when'] }}</span></span>
                                </span>
                                <span class="t-flip__face t-flip__back grid place-items-center bg-white p-5 text-left text-[0.98rem] leading-relaxed shadow-lg">{{ $n['message'] ?? '' }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

    @elseif ($key === 'coupons')
        @php $cps = array_values(array_filter($d->items('coupons'), fn ($r) => trim($r['title'] ?? '') !== '')); @endphp
        @if ($cps)
            <section class="t-sp {{ $dark ? 't-sp--dark' : '' }} mx-auto mt-24 max-w-2xl text-center" {{ $d->f('coupons') }}>
                <p class="t-sp__kicker">চমক · কুপন</p>
                <h2 class="t-section-title font-heading text-4xl {{ $hc }} sm:text-5xl">{{ $title }}</h2>
                <p class="mt-2 text-soft">{{ $sub }}</p>
                <div class="mt-10 space-y-4">
                    @foreach ($cps as $i => $c)
                        <button type="button" class="t-coupon t-reveal" data-coupon="{{ $i }}" aria-pressed="false" style="--i: {{ $i }}">
                            <span class="t-coupon__left"><span class="font-heading text-3xl">{{ \App\Support\Bn::digits($i + 1) }}</span><span class="text-[10px] font-bold uppercase tracking-widest">কুপন</span></span>
                            <span class="t-coupon__body">
                                <span class="block text-lg font-bold leading-snug t-primary">{{ $c['title'] }}</span>
                                @if (trim($c['note'] ?? '') !== '')<span class="block text-sm text-soft">{{ $c['note'] }}</span>@endif
                                <span class="t-coupon__cta">ট্যাপ করে ব্যবহার করো</span>
                            </span>
                            <span class="t-coupon__stamp" aria-hidden="true">ব্যবহার<br>হয়েছে ✓</span>
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

    @elseif ($key === 'secret_message')
        @php $icon = $copy[$key][2] ?? '❤'; @endphp
        <section class="t-sp {{ $dark ? 't-sp--dark' : '' }} mx-auto mt-24 max-w-xl text-center" {{ $d->f('secret_message') }}>
            <p class="t-sp__kicker">চমক · শেষটা</p>
            <h2 class="t-section-title font-heading text-4xl {{ $hc }} sm:text-5xl">{{ $title }}</h2>
            <div class="t-hold-wrap t-reveal mt-10" data-hold>
                <button type="button" class="t-hold" data-hold-btn aria-label="চেপে ধরে রাখো, লুকানো কথাটা খুলবে">
                    <svg viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="54" class="t-hold__track"/><circle cx="60" cy="60" r="54" class="t-hold__ring" data-hold-ring/></svg>
                    <span class="t-hold__icon">{{ $icon }}</span>
                </button>
                <p class="mt-5 font-semibold text-soft" data-hold-hint>{{ $sub }}</p>
                <div class="t-hold__secret" data-hold-secret hidden>
                    <p class="font-heading text-3xl leading-snug t-primary sm:text-4xl">{!! \App\Support\TemplateData::lines($d->text('secret_message')) !!}</p>
                </div>
            </div>
        </section>
    @endif
@endforeach
