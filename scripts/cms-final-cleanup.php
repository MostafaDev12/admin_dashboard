<?php
// Remove only files created by the isolated browser audit, never application/user data.
require __DIR__.'/../vendor/autoload.php';
$audit=realpath(__DIR__.'/../storage/app/cms-final-audit.sqlite');
putenv('APP_ENV=cms');$_ENV['APP_ENV']='cms';$_SERVER['APP_ENV']='cms';
putenv('DB_CONNECTION=sqlite');$_ENV['DB_CONNECTION']='sqlite';$_SERVER['DB_CONNECTION']='sqlite';
putenv('DB_DATABASE='.$audit);$_ENV['DB_DATABASE']=$audit;$_SERVER['DB_DATABASE']=$audit;
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(realpath(config('database.connections.sqlite.database'))!==$audit || basename($audit)!=='cms-final-audit.sqlite')throw new RuntimeException('Not the isolated audit database.');
$removed=0;
foreach(App\Models\CmsMedia::where('name','final-audit.png')->where('is_original',false)->get() as $media){
 if(!str_starts_with($media->path,'/storage/cms/'))throw new RuntimeException('Unexpected audit media path.');
 if(app(App\Services\CmsMediaReferences::class)->isUsed($media->path))throw new RuntimeException('Audit media is referenced; refusing cleanup.');
 Illuminate\Support\Facades\Storage::disk('public')->delete(substr($media->path,9));$media->delete();$removed++;
}
$db=new SQLite3($audit,SQLITE3_OPEN_READONLY);echo json_encode(['removed_audit_uploads'=>$removed,'audit_integrity'=>$db->querySingle('PRAGMA integrity_check')]).PHP_EOL;$db->close();
