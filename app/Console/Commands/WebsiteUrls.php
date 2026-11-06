<?php

namespace App\Console\Commands;

use App\Services\CmsUrls;
use Illuminate\Console\Command;

class WebsiteUrls extends Command
{
    protected $signature = 'website:urls {--dry-run : Inspect only (the default)} {--apply : Apply the reviewed plan} {--plan-hash= : Hash from a fresh dry run}';

    protected $description = 'Review or explicitly apply the stable-identity clean URL upgrade';

    public function handle(CmsUrls $urls): int
    {
        if ($this->option('apply') && $this->option('dry-run')) {
            $this->error('Choose either dry-run or apply.');

            return 1;
        }
        $plan = $this->option('apply') ? $urls->apply((string) $this->option('plan-hash')) : $urls->plan();
        $this->line(json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $plan['conflicts'] ? 1 : 0;
    }
}
