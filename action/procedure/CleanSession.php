<?php
$APP_ROOT = str_replace('action/procedure', '', getcwd());
error_reporting(E_ERROR);
ini_set('display_errors', false);
ini_set("max_execution_time", "360000");
$d = dir($APP_ROOT."tmp/");
while (false !== ($entry = $d->read())) 
{
	if($entry != '.' && $entry != '..'){
		unlink($d->path.$entry);
	}
}
$d->close();
?>