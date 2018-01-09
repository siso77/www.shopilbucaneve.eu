<?php
define( 'APP_ROOT', str_replace('/action/procedure', '', str_replace('\\', '/', getcwd()) ));
define( 'APPLICATION_CONFIG_FILENAME', 'config.xml' );

if(!isset($_SERVER['APPLICATION_ENV']))
	$_SERVER['APPLICATION_ENV'] = 'pro';

error_reporting(E_ERROR);
ini_set('display_errors', false);
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

// 		if(true)
 		if($_REQUEST['user'] == 'admin' && $_REQUEST['pwd'] == 'f7b44cfafd5c52223d5498196c8a2e7b' && $_SERVER['HTTP_STREAM_DEMO_INTEGRATION'] == 'f7b44cfafd5c52223d5498196c8a2e7b') //pwd = md5('stream')
		{
			include_once(APP_ROOT.'/beans/giacenze.php');
			include_once(APP_ROOT.'/beans/ecm_basket.php');
			include_once(APP_ROOT.'/beans/ecm_basket_magazzino.php');
			include_once(APP_ROOT.'/beans/ecm_basket_magazzino_fornitori.php');
			include_once(APP_ROOT.'/beans/ecm_basket_magazzino_forn_de.php');
				
			$beanBasket = new ecm_basket($conn);
			$baskets = $beanBasket->dbGetAll($conn);
			foreach ($baskets as $value)
			{
				$diff = getDiff($value['data_inserimento_riga'], date('Y-m-d'));

				if($diff >= 1)
				{
					$beanBasket = new ecm_basket($conn);
					$beanBasket->dbDelete($conn, array($value['id']), false);

					$beanBasketMagazzino = new ecm_basket_magazzino();
					$basketsMagazzino = $beanBasketMagazzino->dbGetAllByIdBasket($conn, $value['id']);

					foreach ($basketsMagazzino as $val)
					{
						$beanBasketMagazzino->dbDelete($conn, array($val['id']), false);
						
						$BeanMagazzino = new giacenze($conn, $val['id_magazzino']);
						$BeanMagazzino->setDisponibilita( ($BeanMagazzino->getDisponibilita()+$val['quantita']) );
						$BeanMagazzino->dbStore($conn);						
					}	

					$beanBasketMagazzinoFornitori = new ecm_basket_magazzino_fornitori();
					$basketsFornitori = $beanBasketMagazzinoFornitori->dbGetAllByIdBasket($conn, $value['id']);
					foreach ($basketsFornitori as $val)
						$beanBasketMagazzinoFornitori->dbDelete($conn, array($val['id']), false);
					
					$beanBasketMagazzinoFornitoriDe = new ecm_basket_magazzino_forn_de();
					$basketsFornitoriDe = $beanBasketMagazzinoFornitoriDe->dbGetAllByIdBasket($conn, $value['id']);
					foreach ($basketsFornitoriDe as $val)
						$beanBasketMagazzinoFornitoriDe->dbDelete($conn, array($val['id']), false);
				}
			}
// 			storeMonitor();
			sendEmailConfirmation();
		}
	}

	function getDiff($date1, $date2)
	{
		$diff = abs(strtotime($date2) - strtotime($date1));
		
		$years = floor($diff / (365*60*60*24));
		$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
		
		// 		printf("%d years, %d months, %d days\n", $years, $months, $days);
		
		return $days;
	}
	
	function sendEmailConfirmation()
	{
		global $email_customer_name;
		$headers .= 'From: Stream <".EMAIL_ADMIN_FROM.">' . "\r\n";
		$to = "siso77@gmail.com";
		mail($to, "Svuotamento Carrelli FlorSystem per ".$email_customer_name,"OPERAZIONE AVVENUTA CON SUCCESSO", $headers);
	}
	
	function storeMonitor()
	{
		global $conn;
		$query = "UPDATE  `monitor` SET `last_execute` = '".date('Y-m-d H:i:s')."'";
		mysql_query($query, $conn->connection);
	}

?>