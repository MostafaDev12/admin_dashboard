<?php

// Read-only preservation audit against the approved baseline snapshot.
require __DIR__.'/../vendor/autoload.php';
$clone = $argv[1] ?? '';
$backup = $argv[2] ?? '';
if (! is_file($clone) || ! is_file($backup) || realpath($clone) === realpath(__DIR__.'/../storage/app/website-v1.sqlite')) {
    throw new RuntimeException('Supply an isolated clone and the read-only baseline backup.');
}
foreach (['APP_ENV' => 'cms', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => realpath($clone)] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$old = new SQLite3($backup, SQLITE3_OPEN_READONLY);
$current = new SQLite3($clone, SQLITE3_OPEN_READONLY);
$report = ['integrity' => $current->querySingle('PRAGMA integrity_check'), 'foreign_keys' => [], 'tables' => [], 'unexpected_changes' => []];
$q = $current->query('PRAGMA foreign_key_check');
while ($row = $q->fetchArray(SQLITE3_ASSOC)) {
    $report['foreign_keys'][] = $row;
}
$links = app(App\Services\CmsPageLinks::class);
foreach (['cms_pages', 'cms_sections', 'cms_items', 'cms_media', 'cms_submissions', 'admins', 'roles', 'cms_trash', 'cms_tombstones'] as $table) {
    $query = $old->query('SELECT * FROM "'.$table.'" ORDER BY id');
    $count = 0;
    while ($before = $query->fetchArray(SQLITE3_ASSOC)) {
        $count++;
        $after = $current->querySingle('SELECT * FROM "'.$table.'" WHERE id='.(int) $before['id'], true);
        if (! $after) {
            $report['unexpected_changes'][] = $table.' #'.$before['id'].' missing';

            continue;
        }
        foreach ($before as $field => $value) {
            if ($field === 'updated_at' && in_array($table, ['cms_pages', 'cms_sections', 'cms_items'])) {
                continue;
            }
            if ($table === 'cms_pages' && $field === 'slug') {
                $page = App\Models\CmsPage::find($before['id']);
                $value = app(App\Services\CmsUrls::class)->path($page->type, config('cms_urls.'.$page->key));
            }
            if (in_array($table, ['cms_sections', 'cms_items']) && $field === 'content') {
                $value = json_encode($links->canonicalize(json_decode($value, true), json_decode($before['schema'], true)));
                if (json_decode($value, true) === json_decode($after[$field], true)) {
                    continue;
                }
            }
            if ($table === 'cms_sections' && $field === 'groups') {
                $groups = json_decode($value, true) ?? [];
                foreach ($groups as &$group) {
                    $group['defaults'] = $links->canonicalize($group['defaults'] ?? [], $group['schema'] ?? []);
                }unset($group);
                if ($groups === (json_decode($after[$field], true) ?? [])) {
                    continue;
                }
            }
            if ($value !== $after[$field]) {
                $report['unexpected_changes'][] = $table.' #'.$before['id'].' '.$field;
            }
        }
    }
    $report['tables'][$table] = ['before' => $count, 'after' => $current->querySingle('SELECT COUNT(*) FROM "'.$table.'"')];
}
$report['public_pages'] = App\Models\CmsPage::orderBy('id')->get(['key', 'type', 'slug', 'public_slug', 'is_visible'])->toArray();
$report['plan'] = app(App\Services\CmsUrls::class)->plan();
$report['working_database_sha256'] = hash_file('sha256', __DIR__.'/../storage/app/website-v1.sqlite');
$report['env_sha256'] = hash_file('sha256', __DIR__.'/../.env');
$report['cms_env_sha256'] = hash_file('sha256', __DIR__.'/../.env.cms');
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($report['integrity'] === 'ok' && ! $report['foreign_keys'] && ! $report['unexpected_changes'] ? 0 : 1);
