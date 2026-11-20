{{-- Editable customer content, generated from the template's field definitions. --}}
@php $content = $content ?? []; @endphp
<div class="space-y-5">
    @foreach ($fields as $f)
        @continue(in_array($f['type'], ['image', 'gallery'], true))
        @php $name = 'content['.$f['key'].']'; $val = old('content.'.$f['key'], $content[$f['key']] ?? ''); @endphp
        @if ($f['type'] === 'repeater')
            @php $rows = array_values((array) $val); $subs = $f['options']['fields'] ?? []; $blank = array_fill_keys(array_column($subs, 'key'), ''); $rows = array_merge($rows, [$blank, $blank]); @endphp
            <fieldset>
                <legend class="a-label">{{ $f['label'] }} <span class="font-normal text-stone-400">(empty rows are ignored)</span></legend>
                <div class="space-y-2">
                    @foreach ($rows as $i => $row)
                        <div class="grid gap-2 rounded-lg bg-stone-50 p-2.5 ring-1 ring-stone-200 {{ [1 => "sm:grid-cols-1", 2 => "sm:grid-cols-2", 3 => "sm:grid-cols-3"][min(max(count($subs), 1), 3)] }}">
                            @foreach ($subs as $sub)
                                @if (($sub['type'] ?? '') === 'textarea')
                                    <textarea name="{{ $name }}[{{ $i }}][{{ $sub['key'] }}]" rows="2" class="a-input sm:col-span-full" placeholder="{{ $sub['label'] }}">{{ $row[$sub['key']] ?? '' }}</textarea>
                                @else
                                    <input name="{{ $name }}[{{ $i }}][{{ $sub['key'] }}]" value="{{ $row[$sub['key']] ?? '' }}" class="a-input" placeholder="{{ $sub['label'] }}">
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
                @error('content.'.$f['key'])<p class="a-error">{{ $message }}</p>@enderror
            </fieldset>
        @elseif ($f['type'] === 'textarea')
            <x-admin.field :name="$name" :label="$f['label'].($f['required'] ? ' *' : '')" type="textarea" :value="$val" rows="5" />
        @elseif ($f['type'] === 'music')
            <x-admin.field :name="$name" :label="$f['label']" type="select" :value="$val" :options="collect(\App\Support\TemplateRegistry::MUSIC)->map(fn ($t) => $t['icon'].' '.$t['title'])->all() + ['custom' => 'Customer upload', 'none' => 'No music']" />
        @elseif ($f['type'] === 'select')
            <x-admin.field :name="$name" :label="$f['label']" type="select" :value="$val" :options="['' => '—'] + array_combine($f['options']['choices'] ?? [], $f['options']['choices'] ?? [])" />
        @else
            <x-admin.field :name="$name" :label="$f['label'].($f['required'] ? ' *' : '')" :type="['date' => 'date', 'email' => 'email', 'url' => 'url', 'color' => 'color'][$f['type']] ?? 'text'" :value="$val" />
        @endif
    @endforeach
</div>
