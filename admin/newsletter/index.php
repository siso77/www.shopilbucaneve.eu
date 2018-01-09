<?php
header('Content-Type: text/html; charset=utf-8'); 
define( 'APP_ROOT', str_replace('\\', '/', getcwd()) );
define( 'APPLICATION_CONFIG_FILENAME', 'config.xml' );
define( 'SEO_CONFIG_FILENAME', 'seo.ini' );
define( 'WWW_ROOT', 'http://'.$_SERVER['HTTP_HOST'].'/newsletter/' );

date_default_timezone_set('Europe/Rome');
//Linx
//setlocale(LC_TIME, 'it_IT');
//Windows
setlocale(LC_TIME, 'ita', 'it_IT');

if(!isset($_SERVER['APPLICATION_ENV']))
	$_SERVER['APPLICATION_ENV'] = 'pro';

ini_set('error_reporting', E_ALL & ~E_NOTICE);
if(isset($_SERVER['HTTP_SHOW_ERROR']))
	ini_set('display_errors', 'On');
else
	ini_set('display_errors', 'Off');

ini_set("max_execution_time",36000);	
ini_set('memory_limit','512M');

include_once(APP_ROOT.'/libs/INI.php');
include_once(APP_ROOT.'/libs/configureSystem.php');
include_once(APP_ROOT.'/libs/Session.php');
include_once(APP_ROOT.'/libs/debugTime.php');
include_once(APP_ROOT.'/libs/BeanBase.php');
include_once(APP_ROOT.'/libs/xml_parser.php');
include_once(APP_ROOT.'/libs/Dump.php');
include_once(APP_ROOT.'/libs/Currency.php');
//include_once(APP_ROOT.'/libs/GenericEncripting.php');

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

class Bootstrap
{
	var $act = null;
	var $controllerErrorMessage = null;
	
	function Bootstrap()
	{
		Session::start('pro-bike');
		
		$_SESSION['encrypt_key'] = 'pro-bike';
		$_SESSION['config_xml'] = true;
		$_SESSION['unique_libs'] = false;

		// Configuro il sistema
		new configureSystem(APPLICATION_CONFIG_FILENAME);
		$this->setCurrentAction();
		$this->run();
	}

	function run()
	{
		Session::set('last_action', Session::get('action'));
		if($this->getCurrentAction() != 'SetLang')
			Session::set('last_query_string', $_SERVER['QUERY_STRING']);
		
		if(Session::get('action') != $this->getCurrentAction())
		{
			/*		Logica per lo svuotamento della sessione	*/
		}

		$ini = parse_ini_file(APP_ROOT.'/seo.ini', true);

		if(!empty($_REQUEST['from'])){
			if(!empty($ini['RoutesControllers.it'][ucfirst($this->act)])) 
				$this->act = $ini['RoutesControllers.it'][ucfirst($this->act)];		
			if(!empty($ini['RoutesControllers.en'][ucfirst($this->act)])) 
				$this->act = $ini['RoutesControllers.en'][ucfirst($this->act)];		
			if(!empty($ini['RoutesControllers.de'][ucfirst($this->act)])) 
				$this->act = $ini['RoutesControllers.de'][ucfirst($this->act)];		
			if(!empty($ini['RoutesControllers.es'][ucfirst($this->act)])) 
				$this->act = $ini['RoutesControllers.es'][ucfirst($this->act)];
		}
		else
			$this->act = !empty($ini['RoutesControllers.'.$_SESSION['lang']][ucfirst($this->act)]) ? $ini['RoutesControllers.'.$_SESSION['lang']][ucfirst($this->act)] : $this->act;

		Session::set('action', $this->getCurrentAction());
		if( (!$_SESSION['LoggedUser'] || $_SESSION['LoggedUser'] == array()) && $this->getCurrentAction() != 'LastNewsletter' && $this->getCurrentAction() != 'NewsletterSubscribe')
		{
			if($this->getCurrentAction() != 'Login')
			{
				header('Location: '.SITE_ROOT.'?act=Login');
				exit();
			}
			include_once(APP_ROOT.'/UserProfile/0.php');
		}
		else
			include_once(APP_ROOT.'/UserProfile/'.$_SESSION['LoggedUser']['id_type'].'.php');
			
		if(in_array($this->getCurrentAction(), $profileActions) || $this->getCurrentAction() == 'Login' || $this->getCurrentAction() == 'Logout' || $this->getCurrentAction() == 'LastNewsletter')
		{
			$controlerNamePath = APP_ROOT.'/action/'.$this->getCurrentAction().'.php';
			if(is_file($controlerNamePath))
			{
				include_once($controlerNamePath);
				if(class_exists($this->getCurrentAction()))
				{
					$controller = $this->getCurrentAction();
					$obj = new $controller();
					$this->destroy($obj);
				}
				else
					$controllerErrorMessage = 'La classe per l\'azione chiamata non esiste!';
			} 
			else
				$controllerErrorMessage = 'Il file dell\'azione chiamata non esiste!';
		}
		else
			$controllerErrorMessage = 'Non hai i permessi per '.ucfirst($this->getCurrentAction()).'!';
			
		if(isset($controllerErrorMessage))
			exit($controllerErrorMessage);
	}

	private function getCurrentAction()
	{
		return ucfirst($this->act);
	}
	
	private function setCurrentAction()
	{
		// Recupero e setto l'azione richiesta dal client
		if($_POST[PARAM_ACTION_REQUEST])
			$this->act = $_POST[PARAM_ACTION_REQUEST];
		elseif($_GET[PARAM_ACTION_REQUEST])
			$this->act = $_GET[PARAM_ACTION_REQUEST];
		else
			$this->act = DEFAULT_ACTION;		
	}
	
	function destroy($obj)
	{
		if($obj->conn->connection)
		{
			MyDB::disconnect($obj->conn);
		}
	}
}

$debugTime = new debugTime();
new Bootstrap();
$debugTime->OutPutDebugTime('Esecuzione avvenuta in sec: ');


if ( isset ($_SERVER['HTTP_SHOW_HEADERS']))
{
	echo '<pre>';
	print_r($_SERVER);
}
if ( isset ($_SERVER['HTTP_SHOW_PHP_INFO']))
{
	phpinfo();
}
?>