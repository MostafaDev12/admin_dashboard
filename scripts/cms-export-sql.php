<?php

// Export the active SQLite CMS from a consistent read-only snapshot. No MySQL connection is made.
require __DIR__.'/../vendor/autoload.php';
foreach (['APP_ENV' => 'cms'] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite') {
    throw new RuntimeException('This exporter requires the active SQLite CMS.');
}
$directory = $argv[1] ?? '';
if (! $directory || file_exists($directory)) {
    throw new RuntimeException('Supply an unused export directory outside public/.');
}
$parent = realpath(dirname($directory));
if (! $parent || str_starts_with(strtolower($parent), strtolower(realpath(public_path())))) {
    throw new RuntimeException('The export must remain outside the web root.');
}
mkdir($directory, 0700);
$sourcePath = config('database.connections.sqlite.database');
$before = hash_file('sha256', $sourcePath);
$source = new SQLite3($sourcePath, SQLITE3_OPEN_READONLY);
$snapshot = new SQLite3($directory.'/snapshot.sqlite');
if (! $source->backup($snapshot)) {
    throw new RuntimeException('Consistent snapshot failed.');
}
$source->close();
if ($snapshot->querySingle('PRAGMA integrity_check') !== 'ok' || $snapshot->query('PRAGMA foreign_key_check')->fetchArray()) {
    throw new RuntimeException('Source integrity check failed.');
}
$tables = [];
$schema = $snapshot->query("SELECT name,sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
while ($row = $schema->fetchArray(SQLITE3_ASSOC)) {
    $tables[$row['name']] = $row['sql'];
}
$expected = ['admins', 'cms_items', 'cms_media', 'cms_pages', 'cms_redirects', 'cms_sections', 'cms_submissions', 'cms_tombstones', 'cms_trash', 'migrations', 'roles'];
if (array_keys($tables) !== $expected) {
    throw new RuntimeException('Unexpected tables: review schema coverage before exporting.');
}
$rows = [];
$columns = [];
$manifest = ['created_at' => gmdate('c'), 'source' => 'Active SQLite CMS after clean-URL activation', 'tables' => [], 'verification' => []];
foreach ($tables as $table => $ddl) {
    $q = $snapshot->query('PRAGMA table_info("'.$table.'")');
    while ($column = $q->fetchArray(SQLITE3_ASSOC)) {
        $columns[$table][] = $column['name'];
    }
    $q = $snapshot->query('SELECT * FROM "'.$table.'" ORDER BY id');
    $rows[$table] = [];
    while ($row = $q->fetchArray(SQLITE3_ASSOC)) {
        $rows[$table][] = $row;
    }
    $manifest['tables'][$table] = ['rows' => count($rows[$table]), 'sha256' => hash('sha256', serialize($rows[$table]))];
}
$sequences = [];
$q = $snapshot->query('SELECT name,seq FROM sqlite_sequence');
while ($row = $q->fetchArray(SQLITE3_ASSOC)) {
    $sequences[$row['name']] = (int) $row['seq'];
}

// Laravel's actual migrations compile MySQL DDL in pretend mode against a deliberately closed port.
config(['database.default' => 'mysql', 'database.connections.mysql.host' => '127.0.0.1', 'database.connections.mysql.port' => 1, 'database.connections.mysql.database' => 'export_ddl_only']);
Illuminate\Support\Facades\DB::purge('mysql');
$connection = Illuminate\Support\Facades\DB::connection('mysql');
$ddl = [];
$queries = $connection->pretend(fn () => Illuminate\Support\Facades\Schema::create('migrations', function (Illuminate\Database\Schema\Blueprint $table) {
    $table->increments('id');
    $table->string('migration');
    $table->integer('batch');
}));
foreach ($queries as $query) {
    $ddl[] = $query['query'];
}
foreach (['2026_11_05_000000_ensure_cms_admin_auth_tables.php', '2026_11_05_000001_create_website_cms_tables.php', '2026_11_05_000002_add_cms_content_relationships.php', '2026_11_06_000003_add_cms_public_slugs.php'] as $file) {
    $migration = require database_path('migrations/'.$file);
    foreach ($connection->pretend(fn () => $migration->up()) as $query) {
        if (preg_match('/^(?:create|alter) table /i', $query['query'])) {
            $ddl[] = $query['query'];
        }
    }
}
$mysqlColumns = [];
foreach ($ddl as $statement) {
    if (! preg_match('/^(create|alter) table `([^`]+)`/i', $statement, $tableMatch)) {
        throw new RuntimeException('Unsupported generated statement.');
    }
    preg_match_all('/'.(strtolower($tableMatch[1]) === 'alter' ? '\badd ' : '').'`([^`]+)`\s+(?:bigint|int|smallint|tinyint|varchar|text|longtext|json|timestamp|datetime)\b/i', $statement, $matches);
    foreach ($matches[1] as $column) {
        $mysqlColumns[$tableMatch[2]][$column] = true;
    }
}
foreach ($columns as $table => $names) {
    $actual = array_keys($mysqlColumns[$table] ?? []);
    sort($actual);
    $wanted = $names;
    sort($wanted);
    if ($actual !== $wanted) {
        throw new RuntimeException('MySQL schema coverage mismatch for '.$table);
    }
}

$mysql = "-- Elbaz Laravel CMS - MySQL / MariaDB export\n-- Import into an EMPTY selected database using phpMyAdmin or mysql.\n-- Contains private CMS data and administrator password hashes. Keep outside public/.\n-- Files/images are separate; this export contains their references only.\n-- Schema compiled from all four CMS migrations; no DROP/REPLACE statements.\nSET NAMES utf8mb4;\nSET @CMS_OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;\nSET FOREIGN_KEY_CHECKS=0;\nSET @CMS_OLD_TIME_ZONE=@@TIME_ZONE;\nSET TIME_ZONE='+00:00';\n\n".implode(";\n", $ddl).";\n\nSTART TRANSACTION;\n";
$sqlite = "-- Elbaz Laravel CMS - native SQLite export; NOT for MySQL/phpMyAdmin.\nPRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n";
foreach ($tables as $table => $statement) {
    $sqlite .= $statement.";\n";
}
function sqlValue($value, bool $mysql): string
{
    if ($value === null) {
        return 'NULL';
    }
    if (is_int($value)) {
        return (string) $value;
    }
    if (is_float($value)) {
        return json_encode($value, JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
    }
    if (! mb_check_encoding($value, 'UTF-8')) {
        throw new RuntimeException('Non-UTF-8 content requires explicit binary handling.');
    }
    $hex = bin2hex($value);

    return $mysql ? ($hex === '' ? "CONVERT('' USING utf8mb4)" : 'CONVERT(0x'.$hex.' USING utf8mb4)') : "CAST(X'".$hex."' AS TEXT)";
}
foreach ($rows as $table => $records) {
    $mysql .= "\n-- ".$table.': '.count($records)." rows\n";
    foreach ($records as $record) {
        $mysql .= 'INSERT INTO `'.$table.'` (`'.implode('`, `', array_keys($record)).'`) VALUES ('.implode(', ', array_map(fn ($v) => sqlValue($v, true), array_values($record))).");\n";
        $sqlite .= 'INSERT INTO "'.$table.'" ("'.implode('", "', array_keys($record)).'") VALUES ('.implode(', ', array_map(fn ($v) => sqlValue($v, false), array_values($record))).");\n";
    }
}
$q = $snapshot->query("SELECT sql FROM sqlite_master WHERE type IN ('index','trigger') AND sql IS NOT NULL ORDER BY type,name");
while ($row = $q->fetchArray(SQLITE3_ASSOC)) {
    $sqlite .= $row['sql'].";\n";
}
foreach ($sequences as $table => $sequence) {
    if (! isset($tables[$table])) {
        continue;
    }
    $sqlite .= 'UPDATE sqlite_sequence SET seq='.$sequence." WHERE name='".$table."';\n";
    $sqlite .= "INSERT INTO sqlite_sequence(name,seq) SELECT '".$table."',".$sequence." WHERE NOT EXISTS (SELECT 1 FROM sqlite_sequence WHERE name='".$table."');\n";
    $mysql .= 'ALTER TABLE `'.$table.'` AUTO_INCREMENT='.($sequence + 1).";\n";
}
$sqlite .= "COMMIT;\nPRAGMA foreign_keys=ON;\n";
$mysql .= "COMMIT;\nSET FOREIGN_KEY_CHECKS=@CMS_OLD_FOREIGN_KEY_CHECKS;\nSET TIME_ZONE=@CMS_OLD_TIME_ZONE;\n";
file_put_contents($directory.'/elbaz_cms_mysql.sql', $mysql);
file_put_contents($directory.'/elbaz_cms_sqlite.sql', $sqlite);

// Execute only the native SQLite dump on an unused local verification database.
$restored = new SQLite3($directory.'/verification.sqlite');
if (! $restored->exec($sqlite)) {
    throw new RuntimeException('SQLite dump restoration failed.');
}
if ($restored->querySingle('PRAGMA integrity_check') !== 'ok' || $restored->query('PRAGMA foreign_key_check')->fetchArray()) {
    throw new RuntimeException('Restored integrity failed.');
}
foreach ($rows as $table => $records) {
    $q = $restored->query('SELECT * FROM "'.$table.'" ORDER BY id');
    $actual = [];
    while ($row = $q->fetchArray(SQLITE3_ASSOC)) {
        $actual[] = $row;
    }
    if ($actual !== $records) {
        throw new RuntimeException('Restored data differs for '.$table);
    }
}
$restored->close();

// Independently decode each serialized MySQL INSERT and compare all rows/column order.
$decoded = array_fill_keys(array_keys($tables), []);
foreach (file($directory.'/elbaz_cms_mysql.sql', FILE_IGNORE_NEW_LINES) as $line) {
    if (! str_starts_with($line, 'INSERT INTO ')) {
        continue;
    }
    if (! preg_match('/^INSERT INTO `([^`]+)` \((.*?)\) VALUES \((.*)\);$/D', $line, $m)) {
        throw new RuntimeException('Unexpected MySQL INSERT encoding.');
    }
    preg_match_all('/`([^`]+)`/', $m[2], $names);
    $values = [];
    foreach (explode(', ', $m[3]) as $literal) {
        if ($literal === 'NULL') {
            $value = null;
        } elseif ($literal === "CONVERT('' USING utf8mb4)") {
            $value = '';
        } elseif (preg_match('/^CONVERT\(0x([0-9a-f]+) USING utf8mb4\)$/D', $literal, $hex)) {
            $value = hex2bin($hex[1]);
        } elseif (preg_match('/^-?[0-9]+$/D', $literal)) {
            $value = (int) $literal;
        } elseif (is_numeric($literal)) {
            $value = (float) $literal;
        } else {
            throw new RuntimeException('Unsupported MySQL value encoding.');
        }
        $values[] = $value;
    }
    $decoded[$m[1]][] = array_combine($names[1], $values);
}
if ($decoded !== $rows) {
    throw new RuntimeException('MySQL dump serialization differs from the snapshot.');
}
$manifest['verification'] = ['snapshot_integrity' => 'ok', 'sqlite_restore' => 'all rows identical; foreign keys valid', 'mysql_serialization' => 'all rows and columns independently decoded and identical', 'mysql_import' => 'Not executed: the owner-disabled MySQL server was not started.', 'source_sha256_before' => $before, 'source_sha256_after' => hash_file('sha256', $sourcePath), 'environment_files_changed' => false];
foreach (['elbaz_cms_mysql.sql', 'elbaz_cms_sqlite.sql'] as $file) {
    $manifest['files'][$file] = ['bytes' => filesize($directory.'/'.$file), 'sha256' => hash_file('sha256', $directory.'/'.$file)];
}
file_put_contents($directory.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
file_put_contents($directory.'/README.txt', "MYSQL / PHPMYADMIN: Import elbaz_cms_mysql.sql into an EMPTY selected database. Do not import over existing tables. No database is created or dropped by this file. Requires MySQL 5.7+ or a compatible modern MariaDB installation. Actual MySQL import was not executed locally because the server remains owner-disabled.\n\nSQLITE: elbaz_cms_sqlite.sql is a separate native SQLite dump. It was restored locally and every row and foreign key verified. snapshot.sqlite is the consistent original backup.\n\nBoth exports include the current CMS content, clean URLs, redirects, media references, preserved trash/archive, migration history, roles and administrator account hashes. Treat these files as private. Image binaries and uploads are not inside SQL: transfer public/website and storage/app/public with the Laravel project, and create the storage link on the host.\n\nImporting MySQL does not change the local app connection. Set the host's database configuration separately. No local .env or working data was changed by this export.\n");
$snapshot->close();
echo json_encode(['directory' => realpath($directory), 'table_counts' => array_map(fn ($v) => $v['rows'], $manifest['tables']), 'files' => $manifest['files'], 'verification' => $manifest['verification']], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
