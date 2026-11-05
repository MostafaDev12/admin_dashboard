<?php

namespace App\Console\Commands;

use App\Services\WebsiteImporter;
use Illuminate\Console\Command;

class ImportWebsite extends Command
{
    protected $signature = 'website:import';

    protected $description = 'Import original website content without overwriting editor changes';

    public function handle(WebsiteImporter $importer): int
    {
        $this->info(json_encode($importer->import()));

        return self::SUCCESS;
    }
}
