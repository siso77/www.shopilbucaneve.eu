<?php
	define( 'APP_ROOT', str_replace('\\', '/', getcwd()) );
	define( 'APPLICATION_CONFIG_FILENAME', 	'config.xml' );
	include_once(APP_ROOT.'/../libs/xml_parser.php');
	$config = new xml_parser(APP_ROOT.'/../'.APPLICATION_CONFIG_FILENAME);
	$dbPrams = $config->getDbParams();
	$link = mysql_pconnect($dbPrams['Server'], $dbPrams['User'], !empty($dbPrams['Password']) ? $dbPrams['Password'] : '');
	$db = mysql_select_db ($dbPrams['Database']);
	
  	header("Content-type:text/xml");
	ini_set('max_execution_time', 600);
	print("<?xml version=\"1.0\"?>");
	
	if (!isset($_GET["pos"])) 
		$_GET["pos"]=0;

	$sql = "SELECT DISTINCT ".$_REQUEST['field']." FROM ".$_REQUEST['tbl_name']." WHERE ".$_REQUEST['field']." like '".mysql_real_escape_string($_GET["mask"])."%'";
	$sql.= " ORDER BY ".$_REQUEST['field']." LIMIT ". $_GET["pos"].",20";

	if ( $_GET["pos"]==0)
		print("<complete>");
	else
		print("<complete add='true'>");
	$res = mysql_query ($sql);
	if($res)
	{
		while($row=mysql_fetch_array($res))
		{
			print("<option value=\"".$row[$_REQUEST['field']]."\">");
			print($row[$_REQUEST['field']]);
			print("</option>");
		}
	}
//	else
//		echo mysql_errno().": ".mysql_error()." at ".__LINE__." line in ".__FILE__." file<br>";
	print("</complete>");
	mysql_close($link);
?>
