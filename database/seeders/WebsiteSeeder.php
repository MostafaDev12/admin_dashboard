<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        app(\App\Services\WebsiteImporter::class)->import();
    }
}
