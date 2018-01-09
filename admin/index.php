<?php
header('Content-Type: text/html; charset=ISO-8859-1'); 
define( 'APP_ROOT', str_replace('\\', '/', getcwd()) );
define( 'ECM_APP_ROOT', str_replace('/admin', '', str_replace('\\', '/', getcwd())) );

define( 'WWW_ROOT', 'http://'.$_SERVER['HTTP_HOST'].'/admin/' );
define( 'ECM_WWW_ROOT', 'http://'.$_SERVER['HTTP_HOST'].'/' );

define( 'APPLICATION_CONFIG_FILENAME', 'config/config_'.$_SERVER['HTTP_HOST'].'.xml' );
define( 'SEO_CONFIG_FILENAME', 'seo.ini' );

if(!isset($_SERVER['APPLICATION_ENV']))
	$_SERVER['APPLICATION_ENV'] = 'pro';

ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);

if(isset($_SERVER['HTTP_SHOW_ERROR']))
	ini_set('display_errors', 'On');
else
	ini_set('display_errors', 'Off');

ini_set('memory_limit','512M');

if(!empty($_GET['PHP_INFO']))
{
	phpinfo();
	exit();
}
include_once(APP_ROOT.'/libs/INI.php');
include_once(APP_ROOT.'/libs/configureSystem.php');
include_once(APP_ROOT.'/libs/Session.php');
include_once(APP_ROOT.'/libs/debugTime.php');
include_once(APP_ROOT.'/libs/BeanBase.php');
include_once(APP_ROOT.'/libs/xml_parser.php');
include_once(APP_ROOT.'/libs/Dump.php');
include_once(APP_ROOT.'/libs/Currency.php');
include_once(APP_ROOT.'/libs/ext/MSWORD/html_to_doc.inc.php');

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
		// Configuro il sistema
		new configureSystem();		
		Session::start(SESSION_CUSTOM_NAME);
		
		$_SESSION['encrypt_key'] = SESSION_CUSTOM_NAME;
		$_SESSION['config_xml'] = true;
		$_SESSION['unique_libs'] = false;

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
		
		$bypassProfiledAction = false;
		if(
				!empty($_REQUEST['import_from_mail']) || 
				!empty($_REQUEST['export_google_merchant'])
			)
			$bypassProfiledAction = true;
		
		if((!$_SESSION['LoggedUser'] || $_SESSION['LoggedUser'] == array()) && !$bypassProfiledAction)
		{
			$this->act = DEFAULT_ACTION;
		}
		Session::set('action', $this->getCurrentAction());
		
		if((!$_SESSION['LoggedUser'] || $_SESSION['LoggedUser'] == array()))
			include_once(APP_ROOT.'/UserProfile/0.php');
		else
			include_once(APP_ROOT.'/UserProfile/'.$_SESSION['LoggedUser']['id_type'].'.php');
			
		$_SESSION['profiledActions'] = $profileActions;
		
		if(in_array($this->getCurrentAction(), $profileActions) || $this->getCurrentAction() == 'Logout' || $bypassProfiledAction)
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
		{
			$_SESSION['ControllerError']['message'] = $controllerErrorMessage;
			header('Location:'.WWW_ROOT.'?act=ControllerError');
			exit();
		}
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
			$this->act = 'Home';		
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

/**
 * LOG 
 */
$log .= date('d/m/Y H:i:s').' - ';
$log .= '[UTENTE = '.$_SESSION['LoggedUser']['username'].']';
$log .= '[REMOTE IP = '.$_SERVER['REMOTE_ADDR'].']';
foreach ($_REQUEST as $key => $value)
{
	$log .= '['.$key.' = '.$value.']';
}
$log .= '[REQUEST METHOD = '.$_SERVER['REQUEST_METHOD'].']';
$log .= '[USER AGENT = '.$_SERVER['HTTP_USER_AGENT'].']';
MyLog::action_log($log);
/**
 * LOG 
 */

if ( isset ($_SERVER['HTTP_SHOW_HEADERS']))
{
	echo '<pre>';
	print_r($_SERVER);
	exit();
}
?>