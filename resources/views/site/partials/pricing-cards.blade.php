@php use App\Support\Bn; @endphp
<div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($packages as $package)
        <div class="reveal relative {{ $package->is_featured ? 'lg:-translate-y-3' : '' }}">
            @if ($package->is_featured)
                <span class="absolute -top-3 right-5 z-10 rotate-3 rounded-md bg-holud px-3 py-1 text-sm font-bold text-ink shadow">সবচেয়ে জনপ্রিয়</span>
            @endif
            <div class="ticket flex h-full flex-col rounded-[var(--radius-card)] p-6 {{ $package->is_featured ? 'bg-ink text-paper' : 'bg-white' }}" style="box-shadow: var(--shadow-soft)">
                <p class="font-hand text-2xl {{ $package->is_featured ? 'text-holud' : 'text-sindoor' }}">{{ $package->name_bn ?: $package->name }}</p>
                <p class="mt-2 flex items-baseline gap-2">
                    <span class="font-display text-4xl font-extrabold {{ $package->is_featured ? '!text-paper' : '' }}">{{ Bn::money($package->price) }}</span>
                    @if ($package->compare_price && $package->compare_price > $package->price)
                        <s class="text-sm opacity-50">{{ Bn::money($package->compare_price) }}</s>
                    @endif
                </p>
                <p class="mt-2 text-[0.95rem] {{ $package->is_featured ? 'text-paper/70' : 'text-ink-soft' }}">{{ $package->description }}</p>
                <div class="my-5 border-t-2 border-dashed {{ $package->is_featured ? 'border-paper/20' : 'border-ink/10' }}"></div>
                <ul class="flex-1 space-y-2.5 text-[0.95rem]">
                    @foreach ($package->features ?? [] as $f)
                        <li class="flex gap-2"><x-icon name="check" class="mt-1 h-4 w-4 shrink-0 {{ $package->is_featured ? 'text-holud' : 'text-kolapata' }}" /> {{ $f }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('order.create', ['package' => $package->slug]) }}" class="btn mt-6 w-full {{ $package->is_featured ? 'btn-primary' : 'btn-ink' }}">এটা নেব</a>
            </div>
        </div>
    @endforeach
</div>
