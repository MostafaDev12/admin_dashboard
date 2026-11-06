<?php

require __DIR__.'/../vendor/autoload.php';
$database = $argv[1] ?? '';
$backup = $argv[2] ?? '';
if (! is_file($database) || ! is_file($backup) || realpath($database) === realpath(__DIR__.'/../storage/app/website-v1.sqlite')) {
    throw new RuntimeException('Only an existing staging clone is allowed.');
}
foreach (['APP_ENV' => 'cms', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $database] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
$original = new SQLite3($backup, SQLITE3_OPEN_READONLY);
$report = [];
foreach (['cms_pages', 'cms_sections', 'cms_items', 'cms_media', 'cms_submissions', 'cms_redirects', 'admins', 'roles'] as $table) {
    $query = $original->query('SELECT * FROM "'.$table.'" ORDER BY id');
    $same = true;
    $count = 0;
    while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
        $count++;
        $current = (array) $db->table($table)->where('id', $row['id'])->first();
        foreach ($row as $key => $value) {
            if (! array_key_exists($key, $current) || $current[$key] !== $value) {
                $same = false;
            }
        }
    }
    $report['schema_migration_preservation'][$table] = ['before' => $count, 'after' => $db->table($table)->count(), 'original_columns_exact' => $same];
}
$report['item_foreign_keys'] = $db->select('PRAGMA foreign_key_list(cms_items)');
$report['indexes'] = $db->select("SELECT name,tbl_name,sql FROM sqlite_master WHERE type='index' ORDER BY name");
$beforeIds = $db->table('cms_items')->pluck('id')->all();
$beforeMedia = $db->table('cms_media')->pluck('id')->all();
$db->beginTransaction();
try {
    $report['upgrade'] = app(App\Services\CmsUpgrade::class)->run();
    $report['removed_item_ids'] = array_values(array_diff($beforeIds, $db->table('cms_items')->pluck('id')->all()));
    $report['removed_media_ids'] = array_values(array_diff($beforeMedia, $db->table('cms_media')->pluck('id')->all()));
    $report['changed_original_item_content'] = [];
    $originalItems = $original->query('SELECT id,content FROM cms_items');
    while ($row = $originalItems->fetchArray(SQLITE3_ASSOC)) {
        if ($db->table('cms_items')->where('id', $row['id'])->value('content') !== $row['content']) {
            $report['changed_original_item_content'][] = $row['id'];
        }
    }
    foreach (['cms_pages', 'cms_sections', 'cms_items', 'cms_media', 'cms_trash', 'cms_tombstones'] as $table) {
        $report['inside_rolled_back_upgrade'][$table] = $db->table($table)->count();
    }
    // Verify actual enforcement, not just a declaration in migration PHP.
    $item = $db->table('cms_items')->first();
    try {
        $db->table('cms_items')->where('id', $item->id)->update(['target_page_id' => 999999999]);
        $report['invalid_target_page_rejected'] = false;
    } catch (Throwable $e) {
        $report['invalid_target_page_rejected'] = true;
    }
} finally {
    $db->rollBack();
}
$report['integrity'] = $db->select('PRAGMA integrity_check');
$report['foreign_key_check'] = $db->select('PRAGMA foreign_key_check');
$report['upgrade_rolled_back'] = $db->table('cms_trash')->count() === 0;
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
