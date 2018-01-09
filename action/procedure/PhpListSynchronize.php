<?php
define( 'APP_ROOT', str_replace('/action/procedure', '', str_replace('\\', '/', getcwd()) ));
define( 'APPLICATION_CONFIG_FILENAME', 'config.xml' );

if(!isset($_SERVER['APPLICATION_ENV']))
	$_SERVER['APPLICATION_ENV'] = 'pro';

error_reporting(E_ERROR);
ini_set('display_errors', true);
ini_set("max_execution_time", "360000");

include_once(APP_ROOT.'/libs/Dump.php');
include_once(APP_ROOT.'/libs/INI.php');
include_once(APP_ROOT.'/libs/configureSystem.php');
include_once(APP_ROOT.'/libs/BeanBase.php');
include_once(APP_ROOT.'/libs/xml_parser.php');
include_once(APP_ROOT.'/libs/debugTime.php');
/**
 * Inclusione dello ZendCache
 */
define( 'CACHE_CONFIG_INI_PATH', APP_ROOT.'/ZendCache/' );
ini_set('include_path', APP_ROOT.'/ZendCache/');
require_once(APP_ROOT.'/ZendCache/Zend/Cache.php');
require_once(APP_ROOT.'/ZendCache/Cache.php');
/**
 * Inclusione dello ZendCache
*/
new configureSystem();

/***
 * Inizio Logica di Caricamento
 */
	$conn;
	$operator;
	$email_customer_name;	
	$email_customer_logo;
	
	$conn = MyDB::connect();
	$operator = 'StreamImportProcedure';

	$email_customer_name = PREFIX_META_TITLE;
	$email_customer_logo = WWW_ROOT.'themes/uploads/2013/03/logo1.png';
	
	$debugTime = new debugTime();
	Start();
	$debugTime->OutPutDebugTime('Esecuzione avvenuta in sec: ');
	
	
	function Start()
	{
		global $conn;

		if(true)
//  		if($_REQUEST['user'] == 'admin' && $_REQUEST['pwd'] == 'f7b44cfafd5c52223d5498196c8a2e7b' && $_SERVER['HTTP_STREAM_DEMO_INTEGRATION'] == 'f7b44cfafd5c52223d5498196c8a2e7b') //pwd = md5('stream')
		{
			include_once(APP_ROOT.'/beans/customer.php');

			
			//PhpListSynchronize.php
			$Bean = new customer();
			$customer = $Bean->dbGetAll($conn);
			
			foreach ($customer as $value)
			{
				if(empty($value['email']))
					continue;
				
				$query="SELECT MAX(id) as max FROM `phplist_user_user`";
				$result=$conn->query($query);
				if($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
					$next_id=$row['max']+1;

				$query="SELECT * FROM `phplist_user_user` WHERE email ='".str_replace("'", "", $value['email'])."'";
				
				$result=$conn->query($query);
				if($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
					continue;
				
				$q="INSERT INTO `phplist_user_user` (
						`id` ,
						`email` ,
						`confirmed` ,
						`blacklisted` ,
						`optedin` ,
						`bouncecount` ,
						`entered` ,
						`modified` ,
						`uniqid` ,
						`htmlemail` ,
						`subscribepage` ,
						`rssfrequency` ,
						`password` ,
						`passwordchanged` ,
						`disabled` ,
						`extradata` ,
						`foreignkey`
						)
						VALUES ('".$next_id."',  '".strtolower($value['email'])."',  '1',  '0',  '0',  '0', NULL , '".date('Y-m-d H:i:s')."' , NULL ,  '1', NULL , NULL , NULL , NULL ,  '0', NULL , NULL)";
				$conn->query($q);

				$q2="INSERT INTO `phplist_listuser` (
						`userid` ,
						`listid` ,
						`entered` ,
						`modified`
						)
						VALUES ('".$next_id."',  2, '".date('Y-m-d H:i:s')."' , '".date('Y-m-d H:i:s')."')";
				$conn->query($q2);
			}
			//sendEmailConfirmation();
		}
	}
	
	function sendEmailConfirmation()
	{
		global $email_customer_name;
		$headers .= 'From: Stream <".EMAIL_ADMIN_FROM.">' . "\r\n";
		$to = "siso77@gmail.com";
		mail($to, "Sincronizzazione Email per Phplist ".$email_customer_name,"OPERAZIONE AVVENUTA CON SUCCESSO", $headers);
	}	
?>