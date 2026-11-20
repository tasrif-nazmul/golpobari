<?php

namespace App\Console\Commands;

use App\Services\PageService;
use Illuminate\Console\Command;

class ExpirePages extends Command
{
    protected $signature = 'golpobari:expire-pages';

    protected $description = 'Mark active pages past their expiry date as expired';

    public function handle(PageService $pages): int
    {
        $n = $pages->expireDue();
        $this->info("Expired {$n} page(s).");

        return self::SUCCESS;
    }
}
