@extends('layouts.admin')
@section('title', $category->exists ? 'Edit category' : 'New category')
@section('content')
<x-admin.header :title="$category->exists ? $category->name : 'New category'" :back="route('admin.categories.index')" />
<form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf @if ($category->exists) @method('PUT') @endif
    <section class="a-card grid gap-4 p-5 sm:grid-cols-2">
        <x-admin.field name="name" label="Name (English)" :value="$category->name" required />
        <x-admin.field name="name_bn" label="Name (Bangla)" :value="$category->name_bn" placeholder="যেমন: ভালোবাসার গল্প" />
        <x-admin.field name="slug" label="Address" :value="$category->slug" help="Lowercase, used in /categories/…. Made from the name if empty." />
        <x-admin.field name="emoji" label="Emoji" :value="$category->emoji" placeholder="💌" help="Shown when there's no icon image." />
        <x-admin.field name="tagline" label="Tagline" :value="$category->tagline" class="sm:col-span-2" />
        <x-admin.field name="description" label="Description" type="textarea" rows="3" :value="$category->description" class="sm:col-span-2" />
        <x-admin.field name="occasions" label="Occasions" :value="implode(', ', $category->occasions ?? [])" help="Comma separated, e.g. Proposal, Valentine's Day, Anniversary" class="sm:col-span-2" />
        <div x-data="{ v: @js(old('accent_color', $category->accent_color ?? '#d9482b')) }">
            <label class="a-label" for="f_accent_color">Accent colour</label>
            <div class="flex gap-2"><input type="color" x-model="v" class="h-10 w-12 rounded-lg ring-1 ring-stone-300"><input id="f_accent_color" name="accent_color" x-model="v" class="a-input font-mono"></div>
            @error('accent_color')<p class="a-error">{{ $message }}</p>@enderror
        </div>
        <x-admin.field name="sort_order" label="Sort order" type="number" :value="$category->sort_order" />
        <div>
            <x-admin.field name="icon" label="Icon image" type="file" help="Square, at least 256px." />
            @if ($category->iconUrl())<div class="mt-2 flex items-center gap-3"><img src="{{ $category->iconUrl() }}" alt="" class="h-12 w-12 rounded-lg object-cover"><label class="flex items-center gap-1.5 text-sm"><input type="checkbox" name="remove_icon" value="1"> Remove</label></div>@endif
        </div>
        <div>
            <x-admin.field name="cover" label="Cover image" type="file" help="Wide, used on the category page." />
            @if ($category->coverUrl())<img src="{{ $category->coverUrl() }}" alt="" class="mt-2 h-16 w-28 rounded-lg object-cover">@endif
        </div>
        <x-admin.field name="is_active" type="checkbox" label="Show on the website" :value="$category->is_active" class="sm:col-span-2" />
    </section>
    @include('admin.partials.form-footer', ['cancel' => route('admin.categories.index')])
</form>
@endsection
