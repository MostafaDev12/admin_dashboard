<?php

// SQLite-only activation evidence. Never opens the working database for writing.
$root = dirname(__DIR__);
$backup = $argv[1] ?? '';
$clone = $argv[2] ?? '';
if (! is_file($backup) || $clone === '' || file_exists($clone)) {
    throw new RuntimeException('Supply an existing backup and an unused clone path.');
}
$source = new SQLite3($backup, SQLITE3_OPEN_READONLY);
$target = new SQLite3($clone);
if (! $source->backup($target)) {
    throw new RuntimeException('Backup API clone failed.');
}
$report = ['backup' => realpath($backup), 'backup_sha256' => hash_file('sha256', $backup), 'clone' => realpath($clone)];
foreach (['backup' => $source, 'clone' => $target] as $name => $db) {
    $report[$name.'_integrity'] = $db->querySingle('PRAGMA integrity_check');
    $violations = [];
    $query = $db->query('PRAGMA foreign_key_check');
    while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
        $violations[] = $row;
    }
    $report[$name.'_foreign_keys'] = $violations;
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
    while ($table = $tables->fetchArray(SQLITE3_ASSOC)) {
        $quoted = '"'.str_replace('"', '""', $table['name']).'"';
        $rows = [];
        $query = $db->query('SELECT * FROM '.$quoted.' ORDER BY id');
        while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        // Store only hashes and counts; never emit credentials or patient content.
        $report[$name.'_tables'][$table['name']] = ['count' => count($rows), 'rows_sha256' => hash('sha256', json_encode($rows))];
    }
}
$target->close();
$source->close();
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
