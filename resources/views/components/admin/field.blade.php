@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'help' => null, 'options' => [], 'required' => false, 'rows' => 4, 'placeholder' => null, 'step' => null])
@php
    $id = 'f_'.str_replace(['[', ']', '.'], '_', $name);
    $dot = str_replace(['[', ']'], ['.', ''], $name);
    $val = old($dot, $value);
@endphp
<div {{ $attributes->only('class') }}>
    @if ($type === 'checkbox')
        <label class="flex items-start gap-2.5 text-sm font-medium text-stone-700">
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked($val) class="mt-0.5 h-4 w-4 rounded border-stone-300 accent-stone-900">
            <span>{{ $label }} @if ($help)<span class="block text-xs font-normal text-stone-500">{{ $help }}</span>@endif</span>
        </label>
    @else
        @if ($label)<label for="{{ $id }}" class="a-label">{{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif</label>@endif
        @if ($type === 'textarea')
            <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" class="a-input" placeholder="{{ $placeholder }}" @required($required) {{ $attributes->except('class') }}>{{ $val }}</textarea>
        @elseif ($type === 'select')
            <select id="{{ $id }}" name="{{ $name }}" class="a-input" @required($required) {{ $attributes->except('class') }}>
                @foreach ($options as $k => $v)<option value="{{ $k }}" @selected((string) $val === (string) $k)>{{ $v }}</option>@endforeach
            </select>
        @elseif ($type === 'file')
            <input id="{{ $id }}" type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-stone-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white" {{ $attributes->except('class') }}>
        @else
            <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ $val }}" class="a-input" placeholder="{{ $placeholder }}" @if ($step) step="{{ $step }}" @endif @required($required) {{ $attributes->except('class') }}>
        @endif
        @if ($help)<p class="a-help">{{ $help }}</p>@endif
    @endif
    @error($dot)<p class="a-error">{{ $message }}</p>@enderror
</div>
