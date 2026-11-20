<?php

namespace App\Console\Commands;

use App\Models\PageMedia;
use App\Services\ImageService;
use Illuminate\Console\Command;

class CleanupDrafts extends Command
{
    protected $signature = 'golpobari:cleanup-drafts';

    protected $description = 'Delete photos uploaded in the order form that never became an order';

    public function handle(ImageService $images): int
    {
        $cutoff = now()->subHours((int) config('golpobari.draft_upload_ttl_hours', 48));
        $n = 0;
        PageMedia::whereNull('order_id')->whereNull('page_id')->whereNotNull('draft_token')
            ->where('created_at', '<', $cutoff)->chunkById(200, function ($items) use ($images, &$n) {
                foreach ($items as $m) {
                    $images->delete($m);
                    $n++;
                }
            });
        $this->info("Removed {$n} abandoned upload(s).");

        return self::SUCCESS;
    }
}
