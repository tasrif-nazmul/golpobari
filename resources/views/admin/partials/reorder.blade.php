{{-- Up/down arrows for catalogue ordering. Needs $type and $id. --}}
<div class="inline-flex flex-col leading-none">
    @foreach (['up' => '▲', 'down' => '▼'] as $dir => $arrow)
        <form method="POST" action="{{ route('admin.reorder', $type) }}">@csrf<input type="hidden" name="id" value="{{ $id }}"><input type="hidden" name="direction" value="{{ $dir }}">
            <button class="px-1.5 py-0.5 text-[10px] text-stone-400 hover:text-stone-900 disabled:opacity-20" @disabled(($dir === 'up' && $first) || ($dir === 'down' && $last)) aria-label="Move {{ $dir }}">{{ $arrow }}</button></form>
    @endforeach
</div>
