<?php

// Read-only verification against the immutable, consistent pre-activation backup.
$root = dirname(__DIR__);
$backup = $argv[1] ?? '';
if (! is_file($backup)) {
    throw new RuntimeException('A verified pre-activation backup is required.');
}
$before = new SQLite3($backup, SQLITE3_OPEN_READONLY);
$target = $argv[2] ?? $root.'/storage/app/website-v1.sqlite';
$after = new SQLite3($target, SQLITE3_OPEN_READONLY);
$report = ['integrity' => $after->querySingle('PRAGMA integrity_check'), 'violations' => [], 'tables' => [], 'changes' => []];
$query = $after->query('PRAGMA foreign_key_check');
while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
    $report['violations'][] = $row;
}
$hasTrash = $after->querySingle("SELECT count(*) FROM sqlite_master WHERE type='table' AND name='cms_trash'");
$headerArchive = $hasTrash ? $after->querySingle("SELECT payload FROM cms_trash WHERE type='archive' ORDER BY id LIMIT 1") : null;
$archive = json_decode($headerArchive ?: '{}', true);
foreach (['cms_pages', 'cms_sections', 'cms_items', 'cms_media', 'cms_submissions', 'cms_redirects', 'admins', 'roles'] as $table) {
    $count = 0;
    $query = $before->query('SELECT * FROM "'.$table.'" ORDER BY id');
    while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
        $count++;
        $current = $after->querySingle('SELECT * FROM "'.$table.'" WHERE id='.(int) $row['id'], true);
        if (! $current) {
            $report['changes'][] = $table.':'.$row['id'].':missing';

            continue;
        }
        foreach ($row as $key => $value) {
            if (array_key_exists($key, $current) && $current[$key] === $value) {
                continue;
            }
            if ($table === 'cms_sections' && $row['key'] === 'global-header' && in_array($key, ['template', 'schema', 'groups', 'updated_at'], true)) {
                if (($archive['section'][$key] ?? null) !== $value) {
                    $report['changes'][] = 'header archive does not retain '.$key;
                }

                continue;
            }
            if ($table === 'cms_sections' && $row['template'] === 'website.generated.contact-s3' && in_array($key, ['schema', 'content', 'updated_at'], true)) {
                if ($key !== 'updated_at') {
                    $old = json_decode($value, true);
                    $new = json_decode($current[$key], true);
                    foreach ($old as $field => $original) {
                        if (! array_key_exists($field, $new) || $new[$field] !== $original) {
                            $report['changes'][] = $table.':'.$row['id'].':'.$key.':'.$field;
                        }
                    }
                }

                continue;
            }
            // Do not emit account values, content or personal information.
            $report['changes'][] = $table.':'.$row['id'].':'.$key;
        }
    }
    $report['tables'][$table] = ['before' => $count, 'after' => $after->querySingle('SELECT count(*) FROM "'.$table.'"')];
}
$report['item_foreign_keys'] = [];
$query = $after->query('PRAGMA foreign_key_list(cms_items)');
while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
    $report['item_foreign_keys'][] = $row;
}
$report['migration_count'] = $after->querySingle('SELECT count(*) FROM migrations');
$report['passed'] = $report['integrity'] === 'ok' && $report['violations'] === [] && $report['changes'] === [];
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($report['passed'] ? 0 : 1);
