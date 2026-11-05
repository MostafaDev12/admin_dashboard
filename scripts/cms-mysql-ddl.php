<?php

// Prints the SQL the CMS migrations would run on MySQL so it can be reviewed before any approved
// run. Nothing connects to a database: statements are compiled in Laravel's pretend mode, and the
// connection points at a closed port so an unexpected connection attempt would fail loudly.
foreach (['APP_ENV' => 'cms', 'DB_CONNECTION' => 'mysql', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => '1', 'DB_DATABASE' => 'preview_only'] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$connection = Illuminate\Support\Facades\DB::connection('mysql');

foreach (['2026_11_05_000001_create_website_cms_tables.php', '2026_11_05_000002_add_cms_content_relationships.php'] as $file) {
    $migration = require database_path('migrations/'.$file);
    foreach (['up', 'down'] as $direction) {
        echo PHP_EOL.'-- '.$file.' :: '.$direction.PHP_EOL;
        foreach ($connection->pretend(fn () => $migration->$direction()) as $query) {
            echo $query['query'].';'.PHP_EOL;
        }
    }
}
