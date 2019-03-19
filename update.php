#!/usr/local/bin/php
<?php

/**
http://um02.eset.com/eset_upd/v8/
http://91.228.166.13/eset_upd/v8/
http://um03.eset.com/eset_upd/v8/
http://um21.eset.com/eset_upd/v8/
http://91.228.167.21/eset_upd/v8/
http://update.eset.com/eset_upd/v8/
*/
define("DEFAULT_SAVE_PATH","localhost/baza/");

$servers = array(
//array('host'=>'http://um02.eset.com/eset_upd/v8:2221/','savepath'=>DEFAULT_SAVE_PATH."/nod32/"),
array('host'=>'http://um02.eset.com/eset_upd/v8:2221/')
//,array('host'=>'http://ñåðâåð_îáíîâëåíèé.su:2221/','user'=>'','password'=>'')
);

ini_set("display_errors",0);
ini_set("user_agent","WGET");

include("functions.php");

$start = microtime(true);

foreach ($servers as $server){    
    if(!isset($server['savepath'])){
        $server['savepath'] = DEFAULT_SAVE_PATH."/nod32/";
    }
    
    if(file_exists($server['savepath']."update.ver"))
        $current_db = parseDB(file_get_contents($server['savepath']."update.ver"));
    
    echo "Checking {$server['host']}\n";
    $updatedb = parseDB(getHTTPFile($server['host'],"update.ver","",@$server['user'],@$server['password']));

    if(!$updatedb){
        echo "Invalid server!\n";
        continue;
    }

    foreach ($updatedb as $section=>$vars){
        echo "Checking {$section} ({$vars['file']},".@$vars['date'].")\n";

        if(!isset($current_db) or ((@$current_db[$section]['versionid'] < @$vars['versionid'] ) or (@$current_db[$section]['build'] < @$vars['build']))){
            echo "Obtaining {$vars['file']} (size: {$vars['size']}, verison: {$vars['date']})\n";
            getHTTPFile($server['host'],$vars['file'],$server['savepath'].$vars['file'],@$server['user'],@$server['password']);
            $current_db[$section] = $vars;
        }else{
            echo "Also have rather version\n";
        }
    }
    echo "Generation new update.ver\n";
    $new_db = createDB($current_db);
    echo "Saving new update.ver\n";
    file_put_contents($server['savepath']."update.ver",$new_db);
}
echo "Execution time ",round(microtime(true)-$start,4)," sec.";
?>