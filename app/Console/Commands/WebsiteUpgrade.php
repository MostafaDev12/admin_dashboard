<?php

namespace App\Console\Commands;

use App\Services\CmsUpgrade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WebsiteUpgrade extends Command
{
    protected $signature = 'website:upgrade {--dry-run : Report what would change, then roll everything back}';

    protected $description = 'Consolidate the menu and link placements to pages, keeping existing content and its display';

    public function handle(CmsUpgrade $upgrade): int
    {
        if ($this->option('dry-run')) {
            DB::beginTransaction();
            try {
                $this->info('Dry run: '.json_encode($upgrade->run(), JSON_PRETTY_PRINT));
            } finally {
                DB::rollBack();
            }
            $this->line('No changes were saved.');

            return self::SUCCESS;
        }
        $this->info(json_encode($upgrade->run(), JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
