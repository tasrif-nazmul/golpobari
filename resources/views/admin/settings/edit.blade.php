@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
@php
    // [key, label, type, help, options]
    $schema = [
        'general' => [
            ['site_name', 'Site name (English)', 'text'], ['site_name_bn', 'Site name (Bangla)', 'text'],
            ['tagline', 'Tagline (Bangla)', 'text'], ['tagline_en', 'Tagline (English)', 'text'],
            ['contact_phone', 'Contact phone', 'tel'], ['contact_email', 'Contact email', 'email'],
            ['whatsapp_number', 'WhatsApp number', 'tel', 'With country code, e.g. 8801XXXXXXXXX'], ['office_hours', 'Office hours', 'text'],
            ['facebook_url', 'Facebook page', 'url'], ['messenger_url', 'Messenger link', 'url', 'e.g. https://m.me/yourpage'],
            ['instagram_url', 'Instagram', 'url'], ['youtube_url', 'YouTube', 'url'], ['tiktok_url', 'TikTok', 'url'],
            ['branding_text', 'Footer text on customer pages', 'text'], ['branding_enabled', 'Show that footer (packages can still remove it)', 'checkbox'],
            ['@file', 'logo', 'Logo', 'logo_path', 'PNG or WebP, transparent background.'],
            ['@file', 'favicon', 'Favicon', 'favicon_path', 'Square, at least 256px.'],
        ],
        'home' => [
            ['hero_title', 'Hero headline', 'text'], ['hero_subtitle', 'Hero text', 'textarea'],
            ['hero_cta_primary', 'Main button', 'text'], ['hero_cta_secondary', 'Second button', 'text'],
            ['about_title', 'About heading', 'text'], ['about_text', 'About text', 'textarea'],
            ['final_cta_title', 'Closing headline', 'text'], ['final_cta_button', 'Closing button', 'text'],
            ['slug_examples', 'Address examples in the hero', 'text', 'Comma separated, e.g. korim,our-story'],
            ['show_templates', 'Show templates section', 'checkbox'], ['show_pricing', 'Show pricing section', 'checkbox'],
            ['show_testimonials', 'Show testimonials (only if some are active)', 'checkbox'], ['show_faq', 'Show FAQ section', 'checkbox'],
        ],
        'payment' => [
            ['bkash_number', 'bKash number', 'tel', 'Customers send money here. Until this is set, the order form says payment details are coming soon.'],
            ['bkash_account_type', 'Account type', 'select', null, ['Personal' => 'Personal (Send Money)', 'Agent' => 'Agent (Cash Out)', 'Merchant' => 'Merchant (Payment)']],
            ['payment_instructions', 'Payment instructions', 'textarea', 'Shown on the payment step. Plain text.'],
        ],
        'order' => [
            ['orders_enabled', 'Accept new orders', 'checkbox', 'Turn off to pause ordering (for example during Eid holidays).'],
            ['delivery_time', 'Delivery time', 'text', 'Shown to customers, e.g. ২৪–৪৮ ঘণ্টা'],
            ['order_instructions', 'Message after ordering', 'textarea'],
            ['extra_reserved_slugs', 'Extra reserved addresses', 'textarea', 'Comma separated. These can never be chosen as page addresses. System routes are always reserved.'],
        ],
        'upload' => [
            ['max_file_mb', 'Max size per photo (MB)', 'number', 'Photos are shrunk in the browser first, so 8 MB is plenty.'],
            ['max_files_per_order', 'Max photos per order', 'number', 'Packages and templates can lower this.'],
            ['allowed_types', 'Allowed types', 'text', 'Any of jpg,jpeg,png,webp. Everything is converted to WebP on upload.'],
        ],
        'seo' => [
            ['seo_title', 'Default page title', 'text'], ['seo_description', 'Default description', 'textarea'],
            ['google_site_verification', 'Google Search Console code', 'text', 'Only the content="…" value.'],
            ['@file', 'og_image', 'Default share image', 'og_image_path', '1200×630. Used when a page has no image of its own.'],
        ],
    ][$group];
@endphp
<x-admin.header title="Settings" />
<div class="grid gap-6 lg:grid-cols-[200px_minmax(0,1fr)]">
    <nav class="flex gap-1 overflow-x-auto lg:flex-col" aria-label="Settings sections">
        @foreach ($groups as $k => $label)
            <a href="{{ route('admin.settings.edit', $k) }}" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium {{ $group === $k ? 'bg-white text-stone-900 ring-1 ring-stone-200' : 'text-stone-600 hover:bg-white/60' }}">{{ $label }}</a>
        @endforeach
    </nav>
    <form method="POST" action="{{ route('admin.settings.update', $group) }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
        @csrf @method('PUT')
        <section class="a-card grid gap-4 p-5 sm:grid-cols-2">
            <h2 class="font-semibold sm:col-span-2">{{ $groups[$group] }}</h2>
            @foreach ($schema as $row)
                @if ($row[0] === '@file')
                    @php [, $input, $label, $key, $help] = $row; $url = $s->fileUrl($key); @endphp
                    <div class="sm:col-span-2">
                        <x-admin.field :name="$input" :label="$label" type="file" :help="$help" />
                        @if ($url)<div class="mt-2 flex items-center gap-3"><img src="{{ $url }}" alt="" class="h-14 max-w-48 rounded-lg bg-stone-50 object-contain p-1 ring-1 ring-stone-200"><label class="flex items-center gap-1.5 text-sm"><input type="checkbox" name="remove_{{ $input }}" value="1"> Remove</label></div>@endif
                    </div>
                @else
                    @php [$key, $label, $type] = $row; $help = $row[3] ?? null; $wide = in_array($type, ['textarea', 'checkbox'], true); @endphp
                    @if ($type === 'checkbox')
                        <x-admin.field :name="$key" :label="$label" type="checkbox" :value="$s->bool($key)" :help="$help" class="sm:col-span-2" />
                    @elseif ($type === 'select')
                        <x-admin.field :name="$key" :label="$label" type="select" :value="$s->get($key)" :options="$row[4]" :help="$help" />
                    @else
                        <x-admin.field :name="$key" :label="$label" :type="$type" :value="$s->get($key)" :help="$help" :rows="3" :class="$wide ? 'sm:col-span-2' : ''" />
                    @endif
                @endif
            @endforeach
        </section>
        <div class="flex justify-end"><button class="a-btn a-btn-primary">Save {{ strtolower($groups[$group]) }} settings</button></div>
    </form>
</div>
@endsection
