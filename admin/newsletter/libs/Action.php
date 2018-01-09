<?php
include_once(APP_ROOT."/libs/ext/pear/Mail.php");
include_once(APP_ROOT."/libs/ext/pear/Mail/mime.php");

if(isset($_SERVER['HTTP_SHOW_ERROR']))
	ini_set('display_errors', 'On');
else
	ini_set('display_errors', 'Off');

class Action
{
	var $act;
	var $tEngine;
	var $params = null;
	var $hdrs = null;
	var $textHtml = "";
	var $txt = "";
	var $mime = null;
	var $attachment = null;
	var $form_name = '';
	var $Counter;
	var $UserAgent;
	var $IsMobileDevice = false;
	var $templateSettings = false;
	var $rowForPage;
	var $numViewPage;
	
	var $spl_char = array("à" => "&agrave;", 
						  "è" => "&egrave;", 
						  "é" => "&eacute;", 
						  "ì" => "&igrave;", 
						  "ò" => "&oacute;", 
						  "ù" => "&ugrave;",
						  "'" => "&acute;");
	
	function Action()
	{
		$this->act = Session::get('action');

//		if(isset($_GET['lang']))
//			$_SESSION['lang'] = $_GET['lang'];

		$this->UserAgent = $_SERVER['HTTP_USERAGENT'];
//		if(
//			stristr($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') || 
//			stristr($_SERVER['HTTP_USER_AGENT'], 'Nokia') || 
//			stristr($_SERVER['HTTP_USER_AGENT'], 'iphone')
//		  )
//			$this->IsMobileDevice = true;

		$this->getTemplateSettings();
	}
	
	private function getTemplateSettings()
	{
		$ini = parse_ini_file(APP_ROOT.'/style/template/config/templateWeb.ini', true);
		$this->templateSettings = $ini[$this->act];

		$this->numViewPage = $this->templateSettings['numViewPage'];

		if(isset($_GET['rowForPage']))
			$_SESSION[$this->act]['rowForPage'] = $_GET['rowForPage'];

		if(isset($_GET['rowForPage']) && $_GET['rowForPage'] == '')
			$_SESSION[$this->act]['rowForPage'] = $this->templateSettings['rowForPage'];
			
		$this->rowForPage = isset($_SESSION[$this->act]['rowForPage']) ? $_SESSION[$this->act]['rowForPage'] : $this->templateSettings['rowForPage'];
	}

	function default_assignment()
	{
		if($this->IsMobileDevice)
			$this->tEngine->assign('IMG_DIR'		, IMG_DIR.'/wap');
		else
			$this->tEngine->assign('IMG_DIR'		, IMG_DIR.'/web');
		
		$this->tEngine->assign('USER_FULL_NAME'		, $_SESSION['USER_FULL_NAME']);
		$this->tEngine->assign('APP_ROOT'			, APP_ROOT);
		$this->tEngine->assign('PREFIX_META_TITLE'	, PREFIX_META_TITLE);
		$this->tEngine->assign('JS_DIR'				, JS_DIR);
		$this->tEngine->assign('CSS_DIR'			, CSS_DIR);
		$this->tEngine->assign('WWW_ROOT'			, WWW_ROOT);
		$this->tEngine->assign('WWW_INDEX'			, WWW_INDEX);
		$this->tEngine->assign('CHARSET'			, CHARSET);
		$this->tEngine->assign('NEWS_HOME_TRUNCATE'	, NEWS_HOME_TRUNCATE);
		$this->tEngine->assign('external_css'			, EXTERNAL_CSS);
		$this->tEngine->assign('SITE_ROOT'			, SITE_ROOT);
		
		$this->tEngine->assign('CURRENT_DATE'		, date('d-m-Y H:i:s'));
		$this->tEngine->assign('row_for_page'		, $this->rowForPage);
		$this->tEngine->assign('current_action'		, $this->act);
	}

	function defaultLogging($user_profiler_conn)
	{
		MyLog::application_log();
	}
	
	function _redirect($url)
	{
		header("Location: ".WWW_ROOT.$url);
		exit();
	}
	
	function FormatEuro($str)
	{
		if(strstr($str, ","))
		{
			$exp_price = exple(",", $str);
		
			if(strlen($exp_price[1]) == 1)
				$return = $str."0";
			elseif(strlen($exp_price[1]) == 0)
				$return = $str.",00";
			else 
				$return = $str;
		}
		elseif(strstr($str, "."))
		{
			$exp_price = explode(".", $str);
		
			if(strlen($exp_price[1]) == 1)
				$return = $str."0";
			elseif(strlen($exp_price[1]) == 0)
				$return = $str.",00";
			else 
				$return = $str;
		}
		else
			$return = $str.",00";
		
		$return = str_replace(".", ",", $return);
		
		return $return;
	}
	
	function ApplyPercentSale($price, $percent_sale)
	{
		$price = str_replace(",", ".", $price);
		return $price - ($price * $percent_sale / 100);
	}
	
	protected function getComboYears()
	{
		$years = array(
						date('Y')-4,
						date('Y')-3,
						date('Y')-2,
						date('Y')-1,
						date('Y'),
						date('Y')+1,
						date('Y')+2,
						date('Y')+3,
						date('Y')+4,
						date('Y')+5,
						date('Y')+6,
						date('Y')+7,
						date('Y')+8,
						date('Y')+9,
						date('Y')+10,
						date('Y')+11,
						date('Y')+12,
						date('Y')+13,
						date('Y')+14,
						date('Y')+15,
						date('Y')+16,
						date('Y')+17,
						date('Y')+18,
						date('Y')+19,
						date('Y')+20,
						);
						
		return $years;
	}
	
	protected function getComboDays()
	{
		$days = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31');
						
		return $days;
	}
	
	protected function getComboMonths($format = null)
	{
		if(empty($format))
			$format = 'F';
		$month[1] = 'Gennaio';
		$month[2] = 'Febbraio';
		$month[3] = 'Marzo';
		$month[4] = 'Aprile';
		$month[5] = 'Maggio';
		$month[6] = 'Giugno';
		$month[7] = 'Luglio';
		$month[8] = 'Agosto';
		$month[9] = 'Settembre';
		$month[10] = 'Ottobre';
		$month[11] = 'Novembre';
		$month[12] = 'Dicembre';
//		$month[1] = date($format, mktime(0,0,0,1,date('m'),date('Y')));
//		$month[2] = date($format, mktime(0,0,0,2,date('m'),date('Y')));
//		$month[3] = date($format, mktime(0,0,0,3,date('m'),date('Y')));
//		$month[4] = date($format, mktime(0,0,0,4,date('m'),date('Y')));
//		$month[5] = date($format, mktime(0,0,0,5,date('m'),date('Y')));
//		$month[6] = date($format, mktime(0,0,0,6,date('m'),date('Y')));
//		$month[7] = date($format, mktime(0,0,0,7,date('m'),date('Y')));
//		$month[8] = date($format, mktime(0,0,0,8,date('m'),date('Y')));
//		$month[9] = date($format, mktime(0,0,0,9,date('m'),date('Y')));
//		$month[10] = date($format, mktime(0,0,0,10,date('m'),date('Y')));
//		$month[11] = date($format, mktime(0,0,0,11,date('m'),date('Y')));
//		$month[12] = date($format, mktime(0,0,0,12,date('m'),date('Y')));
		return $month;
	}
}


class DBAction extends Action
{
	var $conn;
	var $users_conn;

	function DBAction()
	{
		parent::Action();
		$this->_setConn();
	}
	
	function _setConn()
	{
		$this->conn = MyDB::connect();
	}
	
	protected function uploadFile($customImgRelativePath)
	{
		include_once(APP_ROOT."/beans/wora_carosell.php");

//		if(!file_exists(APP_ROOT.'/uploaded_file/'.$_SESSION['LoggedUser']['id'].'/'))
//			mkdir(APP_ROOT.'/uploaded_file/'.$_SESSION['LoggedUser']['id'].'/', 0777, true);

		if($this->IsMobileDevice)
			$localPath = APP_ROOT.'/'.IMG_DIR.'/wap/'.$customImgRelativePath;
		else
			$localPath = APP_ROOT.'/'.IMG_DIR.'/web/'.$customImgRelativePath;

		if($this->IsMobileDevice)
			$wwwPath = WWW_ROOT.IMG_DIR.'/wap/'.$customImgRelativePath;
		else	
			$wwwPath = WWW_ROOT.IMG_DIR.'/web/'.$customImgRelativePath;
		
		//$fName = str_replace(" ", "", date('d_m_Y_H_i_s_').$_FILES['attach']['name']);
		$fName = str_replace(" ", "", $_FILES['attach']['name']);
		$pathFName = $localPath;

		if(!file_exists($localPath))
			mkdir($localPath, 0777, true);
		
		if(!move_uploaded_file($_FILES['attach']['tmp_name'], $localPath.$fName))
			throw new Exception();

		chmod($localPath.$fName, 0644);
			
		$dateExpire = '0000-00-00 '.'00:00:00';
		
		$BeanWoraFiles = new wora_carosell();
		$BeanWoraFiles->setLocal_path($pathFName);
		$BeanWoraFiles->setWww_path($wwwPath);
		$BeanWoraFiles->setFile_name($fName);
		$BeanWoraFiles->setExpire($dateExpire);
		$BeanWoraFiles->setType($_FILES['attach']['type']);
		
		$BeanWoraFiles->setPermission('public');
		$BeanWoraFiles->setNote($_POST['note']);
		$BeanWoraFiles->setLink($_POST['link']);
		$BeanWoraFiles->dbStore($this->conn);
	}	
}

class SmartyAction extends Action
{
	function SmartyAction()
	{
		parent::Action();
		$this->configure_smarty();
		$this->default_assignment();
	}
	
	function configure_smarty()
	{
		$configCacheKey = str_replace('.', '',APPLICATION_CONFIG_FILENAME);
		if (!$obj = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			if (stristr(APPLICATION_CONFIG_FILENAME, '.xml'))
				$obj = new xml_parser(APP_ROOT.'/'.APPLICATION_CONFIG_FILENAME);
			elseif (stristr(APPLICATION_CONFIG_FILENAME, '.ini'))
				$obj = new INI(APP_ROOT.'/'.APPLICATION_CONFIG_FILENAME);
			else
				exit('The configuration file not valid (type accepted is: .ini|.xml)');

			if($obj->getUseZendCache())
				Base_CacheCore::getInstance()->save($obj, $configCacheKey);
		}

		$ini = $obj->getSmartyTplParams();
		$this->tEngine = new TemplateEngine($ini);
		
		if($this->IsMobileDevice)
		{
			$this->tEngine->compile_dir .= '/wap';
			$this->tEngine->template_dir .= '/wap';
		}
		else
		{
			$this->tEngine->template_dir .= '/web';
			$this->tEngine->compile_dir .= '/web';
		}
	}	
}

class DBSmartyAction extends DBAction
{
	function DBSmartyAction()
	{
		parent::DBAction();
		$this->configure_smarty();
		$this->default_assignment();
	}

	function configure_smarty()
	{
		$configCacheKey = str_replace('.', '',APPLICATION_CONFIG_FILENAME);
		if (!$obj = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			if (stristr(APPLICATION_CONFIG_FILENAME, '.xml'))
				$obj = new xml_parser(APP_ROOT.'/'.APPLICATION_CONFIG_FILENAME);
			elseif (stristr(APPLICATION_CONFIG_FILENAME, '.ini'))
				$obj = new INI(APP_ROOT.'/'.APPLICATION_CONFIG_FILENAME);
			else
				exit('The configuration file not valid (type accepted is: .ini|.xml)');

			if($obj->getUseZendCache())
				Base_CacheCore::getInstance()->save($obj, $configCacheKey);
		}
			
		$ini = $obj->getSmartyTplParams();
		$this->tEngine = new TemplateEngine($ini);
		
		if($this->IsMobileDevice)
		{
			$this->tEngine->compile_dir .= '/wap';
			$this->tEngine->template_dir .= '/wap';
		}
		else
		{
			$this->tEngine->template_dir .= '/web';
			$this->tEngine->compile_dir .= '/web';
		}
	}		
}

class DBMailAction extends DBAction
{
	function DBMailAction()
	{
		parent::DBAction();
		
		if(!isset($this->params) && !isset($this->params))
			$this->default_configure_mail();
		$this->mail_factory();
	}

	function sendMail($to = null)
	{
		if(isset($to))
		{
			$mime_get = $this->mime->get();
			$mime_hdrs = $this->mime->headers($this->hdrs);
			$mime_hdrs['To'] = $this->hdrs[To];
			$mail =& Mail::factory('smtp', $this->params);		
			return $mail->send($to, $mime_hdrs, $mime_get);
		}
		else
			return array("MAIL_ERROR" => true, "ERROR_MESSAGE" => "Non &eacute; stato passato il destinatario");
	}
	
	function mail_factory()
	{
		$crlf = "\r\n";
		$this->mime = new Mail_mime($crlf);
		if(isset($this->text))
			$this->mime->setTXTBody($this->text);
		if(isset($this->textHtml))
			$this->mime->setHTMLBody($this->textHtml);
		if(isset($this->attachment))
			$this->mime->addAttachment($this->attachment, 'text/plain');
	}
	
	function default_configure_mail()
	{
		$this->params["host"]  = "mail.sisoweb.it";
		$this->params["auth"]  = true;
		$this->params["username"]  = "admin@sisoweb.it";
		$this->params["password"]  = "siso!1406";
		
		$this->hdrs["Mailed-by"]  = MAILED_BY_HOST;
		$this->hdrs["Signed-by"]  = MAILED_BY_HOST;		
		$this->hdrs["From"]    = "admin@sisoweb.it";
		$this->hdrs["To"]      = "";
		$this->hdrs["Cc"]	   = "";
		$this->hdrs["Bcc"]	   = "";
		$this->hdrs["Subject"] = "";
		$this->hdrs["Date"] = date("r");
	}
	
	function setAttachment($f_attachment = null)
	{
		if(isset($f_attachment))
			$this->attachment = $f_attachment;
	}
	
	function setParams($value = null)
	{
		if(isset($value) && is_array($value))
		{
			foreach($value as $key => $val)
				$this->params[$key] = $val;
		}
	}

	function setHeaders($value = null)
	{
		if(isset($value) && is_array($value))
		{
			foreach($value as $key => $val)
				$this->hdrs[$key] = $val;
		}
	}

	function setHtmlText($html = null)
	{
		if(isset($html))
			$this->textHtml = $html;
	}

	function setText($txt = null)
	{
		if(isset($txt))
		{
			foreach($this->spl_char as $chr_to_replce => $chr)
				$txt = str_replace($chr, $chr_to_replce, $txt);

			$this->text = $txt;
		}
	}
}

class SmartyMailAction extends SmartyAction
{	
	function SmartyMailAction()
	{
		parent::SmartyAction();
		
		if(!isset($this->params) && !isset($this->params))
			$this->default_configure_mail();
		$this->mail_factory();
	}

	function sendMail($to = null)
	{
		if(isset($to))
		{
			$mime_get = $this->mime->get();
			$mime_hdrs = $this->mime->headers($this->hdrs);
			$mime_hdrs['To'] = $this->hdrs[To];
			$mail =& Mail::factory('smtp', $this->params);		
			return $mail->send($to, $mime_hdrs, $mime_get);
		}
		else
			return array("MAIL_ERROR" => true, "ERROR_MESSAGE" => "Non ? stato passato il destinatario");
	}
	
	function mail_factory()
	{
		$crlf = "\r\n";
		$this->mime = new Mail_mime($crlf);
		if(isset($this->text))
			$this->mime->setTXTBody($this->text);
		if(isset($this->textHtml))
			$this->mime->setHTMLBody($this->textHtml);
		if(isset($this->attachment))
			$this->mime->addAttachment($this->attachment, 'text/plain');
	}
	
	function default_configure_mail()
	{
		$this->params["host"]  = "smtp.sisoweb.it";
		//$this->params["port"] = 25;
		$this->params["auth"]  = true;
		$this->params["username"]  = "info@sisoweb.it";
		$this->params["password"]  = "siso051077";

		$this->hdrs["Mailed-by"]  = MAILED_BY_HOST;
		$this->hdrs["Signed-by"]  = MAILED_BY_HOST;
		$this->hdrs["From"]    = "info@sisoweb.it";
		$this->hdrs["To"]      = "";
		$this->hdrs["Cc"]	   = "";
		$this->hdrs["Bcc"]	   = "";
		$this->hdrs["Subject"] = "";
		$this->hdrs["Date"] = date("r");				
/*
$this->params["debug"] = true;
$this->params["localhost"] = "";
$this->params["timeout"] = 1200;
$this->params["verp"] = false;
$this->params["debug"] = false;
$this->params["persist"] = false;
*/		
	}
	
	function setAttachment($f_attachment = null)
	{
		if(isset($f_attachment))
			$this->attachment = $f_attachment;
	}
	
	function setParams($value = null)
	{
		if(isset($value) && is_array($value))
		{
			foreach($value as $key => $val)
				$this->params[$key] = $val;
		}
	}

	function setHeaders($value = null)
	{
		if(isset($value) && is_array($value))
		{
			foreach($value as $key => $val)
				$this->hdrs[$key] = $val;
		}
	}

	function setHtmlText($html = null)
	{
		if(isset($html))
			$this->textHtml = $html;
	}

	function setText($txt = null)
	{
		if(isset($txt))
		{
			foreach($this->spl_char as $chr_to_replce => $chr)
				$txt = str_replace($chr, $chr_to_replce, $txt);

			$this->text = $txt;
		}
	}
}

class DBSmartyMailAction extends DBSmartyAction
{
	function DBSmartyMailAction()
	{
		parent::DBSmartyAction();
		
		if(!isset($this->params) && !isset($this->params))
			$this->default_configure_mail();
		$this->mail_factory();
	}

	function sendMail($to = null)
	{
		if(isset($to))
		{
//			$this->hdrs['debug']=true; 
//			$this->hdrs['persist']=true; 			
			$mime_get = $this->mime->get();
			$mime_hdrs = $this->mime->headers($this->hdrs);
			$mime_hdrs['To'] = $this->hdrs[To];
			$mail =& Mail::factory('smtp', $this->params);
			return $mail->send($to, $mime_hdrs, $mime_get);
		}
		else
			return array("MAIL_ERROR" => true, "ERROR_MESSAGE" => "Non ? stato passato il destinatario");
	}
	
	function mail_factory()
	{
		$crlf = "\r\n";
		$this->mime = new Mail_mime($crlf);
		if(isset($this->text))
			$this->mime->setTXTBody($this->text);
		if(isset($this->textHtml))
			$this->mime->setHTMLBody($this->textHtml);
		if(isset($this->attachment))
			$this->mime->addAttachment($this->attachment, 'text/plain');
	}
	
	function default_configure_mail()
	{	
		/*
		VECCHI PRE ELDER
		$this->params["host"]  = "smtp.sisoweb.it";
		//$this->params["port"] = 25;
		$this->params["auth"]  = true;
		$this->params["username"]  = "info@sisoweb.it";
		$this->params["password"]  = "siso051077";
		
		$this->hdrs["From"]    = "info@sisoweb.it";
		$this->hdrs["To"]      = "siso77@libero.it";
		$this->hdrs["Cc"]	   = "";
		$this->hdrs["Bcc"]	   = "";
		$this->hdrs["Subject"] = "Richiesta Brano Musicale - Radio L'aquila";
		*/
		
			$this->params["host"]  = "webmail.sexyshopping.it";
			//$this->params["port"] = 25;
			$this->params["auth"]  = true;
			$this->params["username"]  = "info@sexyshopping.it";
			$this->params["password"]  = "deva2009";
			
			$this->hdrs["Mailed-by"]  = MAILED_BY_HOST;
			$this->hdrs["Signed-by"]  = MAILED_BY_HOST;				
			$this->hdrs["From"]    = "info@sexyshopping.it";
			$this->hdrs["To"]      = "info@sexyshopping.it";
			$this->hdrs["Cc"]	   = "";
			$this->hdrs["Bcc"]	   = "";
			$this->hdrs["Subject"] = "";
		
		$this->hdrs["Date"] = date("r");				
	}
	
	function setAttachment($f_attachment = null)
	{
		if(isset($f_attachment))
			$this->attachment = $f_attachment;
	}
	
	function setParams($value = null)
	{
		if(isset($value) && is_array($value))
		{
			foreach($value as $key => $val)
				$this->params[$key] = $val;
		}
	}

	function setHeaders($value = null)
	{
		if(isset($value) && is_array($value))
		{
			foreach($value as $key => $val)
				$this->hdrs[$key] = $val;
		}
	}

	function setHtmlText($html = null)
	{
		if(isset($html))
			$this->textHtml = $html;
	}

	function setText($txt = null)
	{
		if(isset($txt))
		{
			foreach($this->spl_char as $chr_to_replce => $chr)
				$txt = str_replace($chr, $chr_to_replce, $txt);

			$this->text = $txt;
		}
	}
}

class ListAction extends SmartyAction
{
	var $pager;

	function ListAction()
	{
		parent::SmartyAction();
	}

	function setPager($data)
	{
		$options = array
		(
			'itemData' => $data,
			'perPage' => 6,
			'delta' => 10,
			'append' => true,
			'separator' => ' | ',
			'clearIfVoid' => true,
			'urlVar' => 'entrant',
			'useSessions' => false,
			'closeSession' => false,
			//'mode'  => 'Sliding',
			'mode'  => 'Jumping'
		);
		
		$this->pager = &new Pager($options);
	}
}
?>