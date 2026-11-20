<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use App\Support\ActivityLogger;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SettingController extends Controller implements HasMiddleware
{
    public const GROUPS = ['general' => 'General', 'home' => 'Homepage', 'payment' => 'Payment', 'order' => 'Orders', 'upload' => 'Uploads', 'seo' => 'SEO'];

    private const FILES = ['logo_path' => 'logo', 'favicon_path' => 'favicon', 'og_image_path' => 'og_image'];

    public static function middleware(): array
    {
        return [new Middleware('can:manage-settings')];
    }

    public function edit(Settings $settings, ?string $group = 'general')
    {
        $group = array_key_exists((string) $group, self::GROUPS) ? $group : 'general';

        return view('admin.settings.edit', ['group' => $group, 'groups' => self::GROUPS, 's' => $settings]);
    }

    public function update(Request $request, string $group, Settings $settings, ImageService $images)
    {
        abort_unless(array_key_exists($group, self::GROUPS), 404);
        $keys = array_keys(config("golpobari.settings.$group"));

        $rules = [
            'general' => [
                'site_name' => ['required', 'string', 'max:60'], 'site_name_bn' => ['nullable', 'string', 'max:60'],
                'tagline' => ['nullable', 'string', 'max:160'], 'tagline_en' => ['nullable', 'string', 'max:160'],
                'contact_phone' => ['nullable', 'string', 'max:30'], 'contact_email' => ['nullable', 'email', 'max:120'],
                'whatsapp_number' => ['nullable', 'string', 'max:30'], 'office_hours' => ['nullable', 'string', 'max:120'],
                'facebook_url' => ['nullable', 'url', 'max:255'], 'messenger_url' => ['nullable', 'url', 'max:255'],
                'instagram_url' => ['nullable', 'url', 'max:255'], 'youtube_url' => ['nullable', 'url', 'max:255'], 'tiktok_url' => ['nullable', 'url', 'max:255'],
                'branding_text' => ['nullable', 'string', 'max:80'], 'branding_enabled' => ['boolean'],
                'logo' => ['nullable', 'file', 'max:4096', 'mimes:png,jpg,jpeg,webp'], 'favicon' => ['nullable', 'file', 'max:1024', 'mimes:png,jpg,jpeg,webp'],
            ],
            'home' => [
                'hero_title' => ['required', 'string', 'max:120'], 'hero_subtitle' => ['nullable', 'string', 'max:500'],
                'hero_cta_primary' => ['required', 'string', 'max:60'], 'hero_cta_secondary' => ['required', 'string', 'max:60'],
                'about_title' => ['nullable', 'string', 'max:120'], 'about_text' => ['nullable', 'string', 'max:1500'],
                'final_cta_title' => ['nullable', 'string', 'max:160'], 'final_cta_button' => ['nullable', 'string', 'max:60'],
                'slug_examples' => ['nullable', 'string', 'max:300', 'regex:/^[a-z0-9,\- ]*$/'],
                'show_templates' => ['boolean'], 'show_pricing' => ['boolean'], 'show_testimonials' => ['boolean'], 'show_faq' => ['boolean'],
            ],
            'payment' => [
                'bkash_number' => ['nullable', 'regex:/^01[3-9]\d{8}$/'], 'bkash_account_type' => ['nullable', 'in:Personal,Agent,Merchant'],
                'payment_instructions' => ['nullable', 'string', 'max:1000'],
            ],
            'order' => [
                'delivery_time' => ['nullable', 'string', 'max:60'], 'order_instructions' => ['nullable', 'string', 'max:1000'],
                'orders_enabled' => ['boolean'], 'extra_reserved_slugs' => ['nullable', 'string', 'max:1000', 'regex:/^[a-z0-9,\- ]*$/'],
            ],
            'upload' => [
                'max_file_mb' => ['required', 'numeric', 'min:1', 'max:20'], 'max_files_per_order' => ['required', 'integer', 'min:1', 'max:100'],
                'allowed_types' => ['required', 'string', 'regex:/^(jpg|jpeg|png|webp)(,(jpg|jpeg|png|webp))*$/'],
            ],
            'seo' => [
                'seo_title' => ['required', 'string', 'max:120'], 'seo_description' => ['nullable', 'string', 'max:300'],
                'google_site_verification' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_\-]*$/'],
                'og_image' => ['nullable', 'file', 'max:8192', 'mimes:png,jpg,jpeg,webp'],
            ],
        ][$group];

        if ($group === 'upload') {
            $request->merge(['allowed_types' => strtolower(str_replace(' ', '', (string) $request->input('allowed_types')))]);
        }
        $data = $request->validate($rules, ['bkash_number.regex' => 'Enter an 11-digit bKash number like 01XXXXXXXXX.']);

        $values = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, self::FILES)) {
                $input = self::FILES[$key];
                if ($request->hasFile($input)) {
                    $values[$key] = $images->storePublic($request->file($input), 'branding', $input === 'favicon' ? 256 : 1600);
                } elseif ($request->boolean('remove_'.$input)) {
                    $values[$key] = null;
                }

                continue;
            }
            if (array_key_exists($key, $rules) && in_array('boolean', $rules[$key], true)) {
                $values[$key] = $request->boolean($key) ? '1' : '0';
            } elseif (array_key_exists($key, $data)) {
                $values[$key] = is_string($data[$key]) ? trim(strip_tags($data[$key])) : $data[$key];
            }
        }
        $settings->set($values);
        ActivityLogger::log('settings.updated', null, self::GROUPS[$group], ['keys' => array_keys($values)]);

        return back()->with('success', self::GROUPS[$group].' settings saved.');
    }
}
