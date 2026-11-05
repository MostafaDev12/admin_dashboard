<?php
require __DIR__.'/../vendor/autoload.php';
putenv('APP_ENV=cms');$_ENV['APP_ENV']='cms';$_SERVER['APP_ENV']='cms';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$routes=[];$missing=[];$getMutations=[];
foreach(Illuminate\Support\Facades\Route::getRoutes() as $route){
 $row=['uri'=>$route->uri(),'methods'=>$route->methods(),'name'=>$route->getName(),'action'=>$route->getActionName(),'middleware'=>$route->gatherMiddleware()];$routes[]=$row;
 if(str_contains($row['action'],'@')){[$class,$method]=explode('@',$row['action'],2);if(!method_exists($class,$method))$missing[]=$row;}
 if(in_array('GET',$row['methods'])&&preg_match('~^admin/(?:.*/delete/|cache/clear|general-settings/admin/loader/|logout$)~',$row['uri']))$getMutations[]=$row;
}
$seed=json_decode(file_get_contents(database_path('content/website.json')),true,512,JSON_THROW_ON_ERROR);
$collections=[];$fields=['text'=>0,'image'=>0,'link'=>0,'icon'=>0,'fixed'=>0];
foreach($seed['sections'] as $section){foreach($section['schema'] as $field)$fields[$field['type']]++;foreach($section['items'] as $item)foreach($item['schema'] as $field)$fields[$field['type']]++;foreach($section['groups'] as $group)$collections[]=['section'=>$section['key'],'key'=>$group['key'],'label'=>$group['label'],'parent_group'=>$group['parent_group']];}
$report=['registered_routes'=>count($routes),'cms_routes'=>count(array_filter($routes,fn($r)=>str_starts_with($r['uri'],'admin/cms'))),'missing_controller_methods'=>$missing,'legacy_get_mutations'=>$getMutations,'seed_fields'=>$fields,'collections'=>$collections];
echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
