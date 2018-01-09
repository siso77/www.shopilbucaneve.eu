<?php
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);

if(isset($_SERVER['HTTP_SHOW_ERROR']))
	ini_set('display_errors', 'On');
else
	ini_set('display_errors', 'Off');

include_once(APP_ROOT."/libs/ext/pear/Mail.php");
include_once(APP_ROOT."/libs/ext/pear/Mail/mime.php");

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

	protected function getTemplateSettingsByCustomKey($key)
	{
		$ini = parse_ini_file(APP_ROOT.'/style/template/config/templateWeb.ini', true);
		$this->templateSettings = $ini[$key];
		
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


		$this->tEngine->assign('external_css'				, EXTERNAL_CSS);
		$this->tEngine->assign('USER_FULL_NAME'				, $_SESSION['USER_FULL_NAME']);
		$this->tEngine->assign('APP_ROOT'					, APP_ROOT);
		$this->tEngine->assign('PREFIX_META_TITLE'			, PREFIX_META_TITLE);
		$this->tEngine->assign('JS_DIR'						, JS_DIR);
		$this->tEngine->assign('CSS_DIR'					, CSS_DIR);
		$this->tEngine->assign('WWW_ROOT'					, WWW_ROOT);
		$this->tEngine->assign('WWW_INDEX'					, WWW_INDEX);
		$this->tEngine->assign('CHARSET'					, CHARSET);
		$this->tEngine->assign('NEWS_HOME_TRUNCATE'			, NEWS_HOME_TRUNCATE);
		$this->tEngine->assign('IVA_INTEGRATORI'			, IVA_INTEGRATORI);
		$this->tEngine->assign('theme'						, THEME);
		

		$this->tEngine->assign('INCOICE_STRLEN_1_FILED'		, INCOICE_STRLEN_1_FILED);
		$this->tEngine->assign('INCOICE_STRLEN_2_FILED'		, INCOICE_STRLEN_2_FILED);
		$this->tEngine->assign('INCOICE_STRLEN_3_FILED'		, INCOICE_STRLEN_3_FILED);
		$this->tEngine->assign('INCOICE_HTML_STRLEN_1_FILED', INCOICE_HTML_STRLEN_1_FILED);
		$this->tEngine->assign('INCOICE_HTML_STRLEN_2_FILED', INCOICE_HTML_STRLEN_2_FILED);
		$this->tEngine->assign('INCOICE_HTML_STRLEN_3_FILED', INCOICE_HTML_STRLEN_3_FILED);
		
		$this->tEngine->assign('CURRENT_DATE'				, date('d-m-Y H:i:s'));
		
		$this->tEngine->assign('LISTA_MAGAZZINO_TRUNCATE_NAME'			, LISTA_MAGAZZINO_TRUNCATE_NAME);
		$this->tEngine->assign('LISTA_MAGAZZINO_TRUNCATE_DESCRIPTION'	, LISTA_MAGAZZINO_TRUNCATE_DESCRIPTION);
		$this->tEngine->assign('SHOP_TRUNCATE_TEXT'						, SHOP_TRUNCATE_TEXT);
		
		/**
		 * Combo da DB
		 */
		include_once(APP_ROOT."/beans/users_type.php");
		$BeanUserType = new users_type();
		$this->tEngine->assign('cmb_user_type', $BeanUserType->dbGetAll(MyDB::connect()));

		include_once(APP_ROOT."/beans/brands.php");
		$BeanBrand = new brands();
		$this->tEngine->assign('cmb_brand', $BeanBrand->dbGetAll(MyDB::connect(), ' name ', ' ASC '));
		
		include_once(APP_ROOT."/beans/fornitore.php");
		$BeanFornitoree = new fornitore();
		$this->tEngine->assign('cmb_dhtmlx_fornitore', $BeanFornitoree->dbGetAllCustomField(MyDB::connect(), 'nome'));

		include_once(APP_ROOT."/beans/size_type.php");
		$BeanSizeType = new size_type();
		$this->tEngine->assign('cmb_size_type', $BeanSizeType->dbGetAll(MyDB::connect(), ' size ', ' ASC '));
		
		include_once(APP_ROOT.'/beans/ApplicationSetup.php');
		$BeanApplicationSetup = new ApplicationSetup();
		$percentSale = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'percentuale_sconto');
		sort($percentSale);
		foreach ($percentSale as $val)
		{
			if($val['name'] == '-')
				$discount[] = array('nome' => $val['name'].'', 'valore'  => $val['name']);
			else
				$discount[] = array('nome' => $val['name'].'%', 'valore'  => $val['name']);
		}
		$this->tEngine->assign('cmb_percentuali_sconto', $discount);

		$paymentType = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'payment_type');
		$this->tEngine->assign('cmb_payment_type', $paymentType);
		
		$speseSpedizione = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'spese_spedizione');
		$this->tEngine->assign('spese_spedizione', $speseSpedizione);
		
		$iva = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'iva');

		$this->tEngine->assign('cmb_iva', $iva);
		
		define('FATTURA_TAX_IVA', floatval('1.'.$iva[0]['name']));
		define('IVA', $iva[0]['name']);

		/**
		 * Combo da DB
		 */
		
		if(date('m') == 01)
		{
			include_once(APP_ROOT."/beans/index_fattura.php");
			$BeanIndexFattura = new index_fattura();
			$fattura = $BeanIndexFattura->dbGetAll(MyDB::connect());
			if($fattura[0]['year'] < date('Y'))
				$BeanIndexFattura->reset(MyDB::connect());
				
			include_once(APP_ROOT."/beans/index_ddt.php");
			$BeanIndexDDT = new index_ddt();
			$ddt = $BeanIndexDDT->dbGetAll(MyDB::connect());
			if($ddt[0]['year'] < date('Y'))
				$BeanIndexDDT->reset(MyDB::connect());
		}
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
			$exp_price = explode(",", $str);
		
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
	
	function getComboYears()
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
	
	/**
	 * Esportazione dati
	 * Excel, doc, pdf
	 */
	function exportExcelData($data, $fieldToDisplay, $prefixFileName, $itemToSubIterate = array())
	{
		include_once APP_ROOT.'/libs/PHPExcelImplement.php';
		new PHPExcelImplement('Europe/Rome', $_REQUEST['type'], $data, $fieldToDisplay, $itemToSubIterate, $prefixFileName, $_SESSION['encrypt_key']);
	}
	
	function createMsWord($data, $pathMsWord, $fileNameMsWord, $saveToFile)
	{
		if(!is_dir($pathMsWord))
			mkdir($pathMsWord, 0777);
		
		$htmltodoc = new HTML_TO_DOC();
		$htmltodoc->createDoc($data,$pathMsWord.$fileNameMsWord,$saveToFile);
	}

	function createPdfInvoice($pathPdf = null, $includeTextIva, $fattura, $customer, $data, $imponibile, $rif_scontrino, $prezzoScontato, $totale)
	{
		include_once(APP_ROOT.'/libs/ext/FPDF/fpdf.php');
		include_once(APP_ROOT.'/libs/TemplateClass/Template_PDF.php');
		include_once(APP_ROOT.'/libs/TemplateClass/Template_Invoice_PDF.php');		
		
//		header('Content-type: application/pdf');

		Template_Invoice_PDF::$includeTextIva 		 = $includeTextIva;

		Template_Invoice_PDF::$imageHeaderX 		 = 10;
		Template_Invoice_PDF::$imageHeaderY 		 = 6;
		Template_Invoice_PDF::$imageHeaderWidth 	 = 30;
		Template_Invoice_PDF::$textHeaderRightHeight = 4;
		Template_Invoice_PDF::$textHeaderRightWidth	 = 50;
		Template_Invoice_PDF::$textHeaderRightX 	 = 155;
		Template_Invoice_PDF::$textHeaderRightBorder = 0;
		Template_Invoice_PDF::$textHeaderRightLn 	 = null;
		Template_Invoice_PDF::$textHeaderRightAlign	 = 'R';
		Template_Invoice_PDF::$textHeaderRightFill 	 = null;
		Template_Invoice_PDF::$textHeaderRightLink 	 = null;

		$pdf = new Template_Invoice_PDF();
		$pdf->SetAuthor('Pro-Bike');
		$pdf->AddPage();

		$pdf->WriteBody($fattura, $customer, $data, $imponibile, $rif_scontrino, $prezzoScontato, $totale);
		if(!empty($pathPdf))
		{
			$pdf->Output($pathPdf, 'F');
//			echo $pdf->Output($fattura, 'I');
		}			
//		exit();			
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
//		include_once(APP_ROOT."/beans/carosell.php");

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
			
		$dateExpire = '0000-00-00 '.'00:00:00';
		
//		$BeanWoraFiles = new carosell();
//		$BeanWoraFiles->setLocal_path($pathFName);
//		$BeanWoraFiles->setWww_path($wwwPath);
//		$BeanWoraFiles->setFile_name($fName);
//		$BeanWoraFiles->setExpire($dateExpire);
//		$BeanWoraFiles->setType($_FILES['attach']['type']);
//		
//		$BeanWoraFiles->setPermission('public');
//		$BeanWoraFiles->setNote($_POST['note']);
//		$BeanWoraFiles->setLink($_POST['link']);
//		$BeanWoraFiles->dbStore($this->conn);
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
		$configCacheKey = str_replace('/', '',str_replace('.', '',APPLICATION_CONFIG_FILENAME));
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
		$configCacheKey = str_replace('/', '',str_replace('.', '',APPLICATION_CONFIG_FILENAME));
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
		$this->params["host"]  = "pro-bike.it";
		$this->params["auth"]  = true;
		$this->params["username"]  = "ecm@pro-bike.it";
		$this->params["password"]  = "siso";
		
		$this->hdrs["From"]    = "ecm@pro-bike.it";
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
		$this->params["host"]  = "smtp.pro-bike.it";
		//$this->params["port"] = 25;
		$this->params["auth"]  = true;
		$this->params["username"]  = "ecm@pro-bike.it";
		$this->params["password"]  = "siso";
		
		$this->hdrs["From"]    = "ecm@pro-bike.it";
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
			$mime_get = $this->mime->get();
			$mime_hdrs = $this->mime->headers($this->hdrs);
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
		$this->params["host"]  = "smtp.pro-bike.it";
		//$this->params["port"] = 25;
		$this->params["auth"]  = true;
		$this->params["username"]  = "ecm@pro-bike.it";
		$this->params["password"]  = "siso";
		
		$this->hdrs["From"]    = "ecm@pro-bike.it";
		$this->hdrs["To"]      = "";
		$this->hdrs["Cc"]	   = "";
		$this->hdrs["Bcc"]	   = "";
		$this->hdrs["Subject"] = "Richiesta Brano Musicale - Radio L'aquila";
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