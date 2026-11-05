<?php
// Read-only inspection of the effective environment and a consistent local SQLite audit copy.
require __DIR__.'/../vendor/autoload.php';
$mode=$argv[1]??'default';
if($mode==='cms'){putenv('APP_ENV=cms');$_ENV['APP_ENV']='cms';$_SERVER['APP_ENV']='cms';}
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$connection=config('database.default');$config=config('database.connections.'.$connection);
$report=['environment'=>$app->environment(),'configuration_cached'=>$app->configurationIsCached(),'connection'=>$connection,'host'=>$config['host']??null,'port'=>$config['port']??null,'database'=>$config['database']??null,'env_hash'=>hash_file('sha256',base_path('.env')),'cms_env_hash'=>hash_file('sha256',base_path('.env.cms'))];
if($connection==='sqlite'){
 $db=new SQLite3($config['database'],SQLITE3_OPEN_READONLY);
 $report['integrity']=$db->querySingle('PRAGMA integrity_check');
 $report['foreign_key_violations']=[];$q=$db->query('PRAGMA foreign_key_check');while($row=$q->fetchArray(SQLITE3_ASSOC))$report['foreign_key_violations'][]=$row;
 $report['tables']=[];$q=$db->query("SELECT name,sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");while($row=$q->fetchArray(SQLITE3_ASSOC)){$name=$row['name'];$row['count']=$db->querySingle('SELECT count(*) FROM "'.str_replace('"','""',$name).'"');$report['tables'][]=$row;}
 $report['migration_status']=[];$q=$db->query('SELECT migration,batch FROM migrations');while($row=$q->fetchArray(SQLITE3_ASSOC))$report['migration_status'][]=$row;
 if(isset($argv[2])){$auditPath=$argv[2];if(file_exists($auditPath))throw new RuntimeException('Refusing to overwrite an existing audit database.');$target=new SQLite3($auditPath);if(!$db->backup($target))throw new RuntimeException('SQLite snapshot failed.');$target->close();$report['audit_snapshot']=$auditPath;}
 $db->close();
}
echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
