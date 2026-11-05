<?php

// Makes a consistent copy of the working CMS database for verification. The source is only ever
// opened read-only; the copy is the only database this run's migrations and browser edits touch.
// Usage: php -d extension=sqlite3 scripts/cms-completion-prepare.php <copy-path>

$root = dirname(__DIR__);
$source = $root.'/storage/app/website-v1.sqlite';
$target = $argv[1] ?? '';
if ($target === '' || realpath(dirname($target)) !== realpath($root.'/storage/app') || basename($target) === basename($source)) {
    fwrite(STDERR, "Refusing: the copy must be a different file directly inside storage/app.\n");
    exit(1);
}
if (is_file($target) && ! unlink($target)) {
    fwrite(STDERR, "Could not replace the previous verification copy.\n");
    exit(1);
}
$before = hash_file('sha256', $source);
$from = new SQLite3($source, SQLITE3_OPEN_READONLY);
$to = new SQLite3($target);
if (! $from->backup($to)) {
    fwrite(STDERR, "SQLite backup failed.\n");
    exit(1);
}
$counts = [];
foreach (['cms_pages', 'cms_sections', 'cms_items', 'cms_media', 'cms_submissions', 'cms_redirects', 'admins', 'roles'] as $table) {
    $counts[$table] = $to->querySingle('SELECT count(*) FROM "'.$table.'"');
}
$report = [
    'source' => $source,
    'source_sha256_before' => $before,
    'source_sha256_after' => hash_file('sha256', $source),
    'copy' => $target,
    'integrity' => $to->querySingle('PRAGMA integrity_check'),
    'counts' => $counts,
];
$from->close();
$to->close();
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
