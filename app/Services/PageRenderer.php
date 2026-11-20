<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageMedia;
use App\Models\Template;
use App\Support\Settings;
use App\Support\TemplateData;
use App\Support\TemplateRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

/**
 * Builds the view for a template from page data, sample data or a draft order.
 */
class PageRenderer
{
    public function __construct(private Settings $settings) {}

    public function forPage(Page $page, bool $isPreview = false): \Illuminate\Contracts\View\View
    {
        $template = $page->template;
        $media = $this->groupMedia($page->media);

        return $this->make($template, $page->content ?? [], $media, $page->theme ?? [], [
            'page' => $page,
            'title' => $page->seo('title', $page->title),
            'showBranding' => $page->show_branding && $this->settings->bool('branding_enabled'),
            'isPreview' => $isPreview,
        ]);
    }

    /** @return array{0: array, 1: array} [content, media] */
    private function splitSample(Template $template): array
    {
        $media = [];
        $content = [];
        $mediaKeys = $template->fields->filter->isMedia()->pluck('key')->all();
        foreach ($template->sampleContent() as $k => $v) {
            if (in_array($k, $mediaKeys, true) || (is_array($v) && (isset($v['url']) || isset($v[0]['url'])))) {
                $media[$k] = isset($v['url']) ? [$v] : $v;
            } else {
                $content[$k] = $v;
            }
        }

        return [$content, $media];
    }

    public function forSample(Template $template): \Illuminate\Contracts\View\View
    {
        [$content, $media] = $this->splitSample($template);

        return $this->make($template, $content, $media, [], [
            'page' => null,
            'title' => $template->name.' · Preview',
            'showBranding' => true,
            'isPreview' => true,
        ]);
    }

    /** Preview built from the wizard's unsaved data and this browser's draft uploads. */
    public function forDraft(Template $template, array $content, Collection $draftMedia, bool $fillSample = false): \Illuminate\Contracts\View\View
    {
        $media = $this->groupMedia($draftMedia);
        $placeholders = [];
        if ($fillSample) {
            // Show faded sample content where the customer hasn't written yet, so they can see where each answer goes.
            [$sc, $sm] = $this->splitSample($template);
            $fieldKeys = $template->fields->pluck('key')->all();
            foreach ($sc as $k => $v) {
                $cur = $content[$k] ?? null;
                if (in_array($k, $fieldKeys, true) && (is_array($cur) ? count($cur) === 0 : trim((string) $cur) === '')) {
                    $content[$k] = $v;
                    $placeholders[] = $k;
                }
            }
            foreach ($sm as $k => $v) {
                if (in_array($k, $fieldKeys, true) && empty($media[$k])) {
                    $media[$k] = $v;
                    $placeholders[] = $k;
                }
            }
        }

        return $this->make($template, $content, $media, [], [
            'placeholders' => $placeholders,
            'page' => null,
            'title' => 'আপনার পেজের প্রিভিউ',
            'isDraft' => true,
            'showBranding' => true,
            'isPreview' => true,
        ]);
    }

    /** @param Collection<int, PageMedia> $items */
    public function groupMedia(Collection $items): array
    {
        return $items->sortBy('sort_order')->groupBy('field_key')->map(fn ($g) => $g->map(fn (PageMedia $m) => [
            'id' => $m->id,
            'url' => $m->url(),
            'thumb' => $m->url(true),
            'caption' => $m->caption(),
            'width' => $m->width,
            'height' => $m->height,
        ])->values()->all())->all();
    }

    private function make(Template $template, array $content, array $media, array $themeOverrides, array $extra): \Illuminate\Contracts\View\View
    {
        $layout = TemplateRegistry::layout($template->layout);
        $theme = $template->resolvedTheme($themeOverrides);
        $fields = $template->fields->map->toSchema();
        $known = collect($layout['fields'])->pluck('key')->all();

        $data = new TemplateData($content, $media, $fields, $theme, $known, $extra['placeholders'] ?? []);
        unset($extra['placeholders']);

        return View::make($template->viewName(), array_merge($extra, [
            'd' => $data,
            'template' => $template,
            'theme' => $theme,
            'fontsUrl' => TemplateRegistry::googleFontsUrl($theme),
            'brandingText' => $this->settings->get('branding_text', 'Created with GolpoBari ❤️'),
        ]));
    }
}
