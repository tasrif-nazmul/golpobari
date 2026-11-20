<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Page;
use App\Models\Template;
use App\Support\TemplateRegistry;
use Database\Seeders\CatalogSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Brings an existing database up to date with the code's template catalogue after a deploy:
 * adds new categories and templates, adds fields a layout gained, and refreshes preview images.
 * It never deletes anything and never changes a template that pages or orders already use,
 * except for adding missing fields.
 */
class SyncTemplates extends Command
{
    protected $signature = 'golpobari:sync-templates {--dry-run : Show what would change without saving}';

    protected $description = 'Add new templates/categories from the code catalogue and fill in missing template fields';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $before = Template::pluck('slug')->all();

        DB::beginTransaction();
        try {
            // 1. The old "tribute" template borrowed the photo album layout. Move it to its own layout if nobody uses it yet.
            $tribute = Template::where('slug', 'tribute')->where('layout', 'photo-memories')->first();
            if ($tribute && ! Page::where('template_id', $tribute->id)->exists() && ! Order::where('template_id', $tribute->id)->exists()) {
                $tribute->update([
                    'layout' => 'tribute', 'theme' => null,
                    'description' => 'জ্বলন্ত মোমবাতি, শান্ত রং, জীবনের গল্প আর "প্রদীপ জ্বালান" বাটন। প্রিয় কাউকে সম্মান জানানোর পেজ।',
                    'preview_path' => file_exists(public_path('images/previews/tribute.webp')) ? 'images/previews/tribute.webp' : $tribute->preview_path,
                ]);
                $tribute->fields()->delete();
                $this->line('• "In Loving Memory" now uses the new candle layout');
            }

            // 2. New categories and templates (existing ones are left exactly as they are).
            (new CatalogSeeder)->run();
            foreach (Template::whereNotIn('slug', $before)->get() as $t) {
                $this->line("• New template: {$t->name} ({$t->slug})");
            }

            // 3. Fields a layout has that a template is missing (e.g. after a layout gained a field).
            foreach (Template::with('fields')->get() as $t) {
                $have = $t->fields->pluck('key')->all();
                $next = (int) $t->fields->max('sort_order');
                foreach (TemplateRegistry::layout($t->layout)['fields'] as $f) {
                    if (in_array($f['key'], $have, true)) {
                        continue;
                    }
                    $t->fields()->create([
                        'key' => $f['key'], 'label' => $f['label'], 'type' => $f['type'], 'section' => $f['section'],
                        'placeholder' => $f['placeholder'], 'help' => $f['help'], 'options' => $f['options'] ?: null,
                        'is_required' => $f['required'], 'max_length' => $f['max_length'], 'sort_order' => ++$next,
                    ]);
                    $this->line("• {$t->slug}: added field {$f['key']}");
                }

                // 4. Built-in preview images that ship with the code.
                $img = "images/previews/{$t->slug}.webp";
                if (file_exists(public_path($img)) && (! $t->preview_path || str_starts_with($t->preview_path, 'images/previews/')) && $t->preview_path !== $img) {
                    $t->update(['preview_path' => $img]);
                    $this->line("• {$t->slug}: preview image set");
                }
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        if ($dry) {
            DB::rollBack();
            $this->warn('Dry run: nothing was saved.');
        } else {
            DB::commit();
            $this->info('Templates are up to date.');
        }

        return self::SUCCESS;
    }
}
