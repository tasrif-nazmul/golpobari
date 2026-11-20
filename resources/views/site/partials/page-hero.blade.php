<section class="px-4 pb-6 pt-8 sm:px-6 md:pt-14">
    <div class="container-x">
        @isset($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endisset
        <h1 class="font-fancy mt-2 text-[2.5rem] leading-tight sm:text-6xl">{{ $title }}</h1>
        @isset($sub)<p class="mt-3 max-w-2xl text-lg text-ink-soft">{{ $sub }}</p>@endisset
    </div>
</section>
