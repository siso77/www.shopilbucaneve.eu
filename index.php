<?php
header('Content-Type: text/html; charset=ISO-8859-1'); 
define( 'APP_ROOT', str_replace('\\', '/', getcwd()) );
define( 'APPLICATION_CONFIG_FILENAME', 'config.xml' );
define( 'SEO_CONFIG_FILENAME', 'seo.ini' );
define( 'WWW_ROOT', 'http://'.$_SERVER['HTTP_HOST'].'/' );

if(!isset($_SERVER['APPLICATION_ENV']))
	$_SERVER['APPLICATION_ENV'] = 'pro';

ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']);
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);

if(isset($_SERVER['HTTP_SHOW_ERROR']))
	ini_set('display_errors', 'On');
else
	ini_set('display_errors', 'Off');

// ini_set('session.save_path',getcwd().'/tmp');
ini_set('memory_limit','1024M');
ini_set("max_execution_time", "3600");

date_default_timezone_set('Europe/Rome');
//setlocale(LC_ALL, 'ita_ITA');
setlocale(LC_TIME, 'it_IT');

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
		new configureSystem(APPLICATION_CONFIG_FILENAME);		
		
		if(!empty($_REQUEST[SESSION_CUSTOM_NAME]))
		{
			session_id($_REQUEST[SESSION_CUSTOM_NAME]);
			session_name(SESSION_CUSTOM_NAME);
			session_start();
		}
		else
		{
			session_name(SESSION_CUSTOM_NAME);
			session_start();
		}

		$_SESSION['encrypt_key'] = SESSION_CUSTOM_NAME;
		$_SESSION['config_xml'] = true;
		$_SESSION['unique_libs'] = false;
		$this->setCurrentAction();

		if(CURTESY_PAGE)
		{
			if(!empty($_REQUEST['bypass']))
				$_SESSION['bypass'] = true;
				
			if(empty($_SESSION['bypass']))
			{
				echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="robots" content="index,follow"><script type="text/javascript" async="" src="http://www.google-analytics.com/ga.js"></script><script src="https://apis.google.com/_/scs/apps-static/_/js/k=oz.gapi.it.iJ4sQ3Ywibs.O/m=plusone_unsupported/am=iQ/rt=j/d=1/rs=AItRSTPTF6SEVBlYT1Xay2nDXpcGUASB0g/cb=gapi.loaded_0" async=""></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/jquery-1.6.3.min.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/jquery.color.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/cufon/cufon-yui.js"></script><style type="text/css">cufon{text-indent:0!important;}@media screen,projection{cufon{display:inline!important;display:inline-block!important;position:relative!important;vertical-align:middle!important;font-size:1px!important;line-height:1px!important;}cufon cufontext{display:-moz-inline-box!important;display:inline-block!important;width:0!important;height:0!important;overflow:hidden!important;text-indent:-10000in!important;}cufon canvas{position:relative!important;}}@media print{cufon{padding:0!important;}cufon canvas{display:none!important;}}</style><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/cufon/helvetica_700.font.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/cufon/coronet_400.font.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/cufon/cufon-replace-helvetica.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/cufon/cufon-replace-coronet.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/jquery.fancybox-1.3.4.pack.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/cloud-zoom.1.0.2.min.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/superfish.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/jquery.easing.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/jquery.carousel.min.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/slides.min.jquery.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/script.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/apis.google.com/js/plusone.js"></script><link rel="stylesheet" type="text/css" href="'.WWW_ROOT.'css/theme/styles.css" media="all"><link rel="stylesheet" type="text/css" href="'.WWW_ROOT.'css/theme/widgets.css" media="all"><link rel="stylesheet" type="text/css" href="'.WWW_ROOT.'css/theme/print.css" media="print"><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/prototype/prototype.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/lib/ccard.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/prototype/validation.js" gapi_processed="true"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/scriptaculous/builder.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/scriptaculous/effects.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/scriptaculous/dragdrop.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/scriptaculous/controls.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/scriptaculous/slider.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/varien/js.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/varien/form.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/varien/menu.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/mage/translate.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/mage/cookies.js"></script><!--[if IE 8]><link rel="stylesheet" type="text/css" href="'.WWW_ROOT.'css/theme/styles-ie.css" media="all" /><![endif]--><!--[if IE 7]><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/lib/ds-sleight.js"></script><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/ie6.js"></script><![endif]--><link rel="stylesheet" type="text/css" href="'.WWW_ROOT.'css/theme/custom.css" media="all"><!--[if IE 9]><link rel="stylesheet" type="text/css" href="'.WWW_ROOT.'css/theme/custom-ie9.css" media="all" /><![endif]--><!--[if IE 8]><link rel="stylesheet" type="text/css" href="'.WWW_ROOT.'css/theme/custom-ie8.css" media="all" /><![endif]--><!--[if IE 7]><link rel="stylesheet" type="text/css" href="'.WWW_ROOT.'css/theme/custom-ie7.css" media="all" /><![endif]--><script type="text/javascript">var Translator = new Translate({"Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.":"Please use only letters (a-z or A-Z), numbers (0-9) or underscores (_) in this field, first character must be a letter."});</script><link media="all" href="'.WWW_ROOT.'css/theme/jquery.fancybox-1.3.4.css" type="text/css" rel="stylesheet"><script type="text/javascript" src="'.WWW_ROOT.'javascript/theme/jscolor/jscolor.js"></script><link media="all" href="'.WWW_ROOT.'css/theme/styles.css" type="text/css" rel="stylesheet"><meta content="text/html; charset=ISO-8859-1" http-equiv="Content-Type">		<title>Florsistemi - Stream  - </title><meta name="description" content="Negozio online di biciclette, mountain bike, componenti ed accessori per mountain bike, Pro Bike,, "><meta name="keywords" content="Mountain bike, pro bike, pro-bike.it, Pro Bike,, "><meta name="robots" content="INDEX,FOLLOW"><meta name="language" content="it"><script type="text/javascript" src="chrome-extension://dlnembnfbcpjnepmfjmngjenhhajpdfd/resources/LocalScript.js"></script><script type="text/javascript" src="chrome-extension://dlnembnfbcpjnepmfjmngjenhhajpdfd/libraries/DataExchangeScript.js"></script><script type="text/javascript" async="" src="http://sg.perion.com/v1.1/js/gt.js"></script></head><img src="'.WWW_ROOT.'themes/uploads/2013/03/logo_email.png"><br><p class="welcome-msg"><div class="header-container" style="width:100%"><div class="page" style="width:70%"><div class="header" style="width:84%"><div class="quick-access-left" style="width:90%"><p class="welcome-msg" style="font-size:82px">Servizio in Manutenzione!</p></div></div></div></div>';
				exit();
			}
		}
		
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

		Session::set('action', $this->getCurrentAction());
		
		if((!$_SESSION['LoggedUser'] || $_SESSION['LoggedUser'] == array()))
			include_once(APP_ROOT.'/UserProfile/0.php');
		else
			include_once(APP_ROOT.'/UserProfile/'.$_SESSION['LoggedUser']['id_type'].'.php');
			
		$_SESSION['profiledActions'] = $profileActions;

		if(in_array($this->getCurrentAction(), $profileActions) || $this->getCurrentAction() == 'Logout')
		{
			if(!empty($this->module))
				$controlerNamePath = APP_ROOT.'/action/'.$this->module.'/'.$this->getCurrentAction().'.php';
			else
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
		$exp = explode('/', $this->act);
		if(count($exp) > 1)
		{
			$this->module = $exp[0];
			return ucfirst($exp[1]);
		}
		else
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

		if($this->act == 'Search' && $_SESSION['LoggedUser']['id_type'] != 1)
		{
			$_REQUEST['act'] = DEFAULT_ACTION;
			$this->act = DEFAULT_ACTION;
		}

// 		if(
// 				(!$_SESSION['LoggedUser'] || $_SESSION['LoggedUser'] == array()) && 
// 				$this->act != 'CreateAccount' &&
// 				$this->act != 'Login' &&
// 				$this->act != 'ImportData' &&
// 				$this->act != 'ImportRealTimePhoto'
// 				)
// 			$this->act = DEFAULT_ACTION;
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
// $log .= date('d/m/Y H:i:s').' - ';
// $log .= '[UTENTE = '.$_SESSION['LoggedUser']['username'].']';
// $log .= '[REMOTE IP = '.$_SERVER['REMOTE_ADDR'].']';
// foreach ($_REQUEST as $key => $value)
// {
// 	$log .= '['.$key.' = '.$value.']';
// }
// $log .= '[REQUEST METHOD = '.$_SERVER['REQUEST_METHOD'].']';
// $log .= '[USER AGENT = '.$_SERVER['HTTP_USER_AGENT'].']';
// MyLog::action_log($log);
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