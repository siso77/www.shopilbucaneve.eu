<?php
ini_set('error_reporting', E_ERROR);
error_reporting(E_ERROR);

if(isset($_SERVER['HTTP_SHOW_ERROR']))
	ini_set('display_errors', true);
else
	ini_set('display_errors', false);
ini_set('display_errors', true);

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
	var $IsTabletDevice = false;
	var $templateSettings = false;
	var $rowForPage;
	var $numViewPage;
	var $key_prezzo;
	var $volume_carrello;
	var $commissioneScatolaDenDekker;
	
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

		include_once(getcwd()."/libs/ext/nusoap-0.9.5/lib/nusoap.php");
		$this->UserAgent = $_SERVER['HTTP_USER_AGENT'];
		if(empty($_SESSION['ua_capabilities']))
		{
			$client = new nusoap_client('http://www.sisoweb.it/soap-server/ServerSoapWurfl.php?wsdl', true);
			$_SESSION['ua_capabilities'] = unserialize($client->call("dispach", array('user' => 'soap_user','pwd' => '#s040p4ssw0rd!', 'className' => 'WurflImplements', 'methothName' => 'wurflFactory', 'args' =>  $this->UserAgent)));
		}
		if($_SESSION['ua_capabilities']['device_type'] == 'mobile_device')
			$this->IsMobileDevice = true;
		if($_SESSION['ua_capabilities']['device_type'] == 'mobile_phone')
			$this->IsMobileDevice = true;
		if($_SESSION['ua_capabilities']['device_type'] == 'tablet')
			$this->IsTabletDevice = true;

		$this->IsMobileDevice = false;
		$this->IsTabletDevice = false;
		if(!empty($_SESSION['LoggedUser']['listino']))
			$this->key_prezzo = 'prezzo_'.$_SESSION['LoggedUser']['listino'];
		else
			$this->key_prezzo = 'prezzo_0';
				
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
	
	protected function assignTemplateConfig()
	{
		$ini = parse_ini_file(APP_ROOT.'/style/template/config/'.$this->act.'.ini', true);
		$this->tEngine->assign('template_config', $ini);
	}
	
	function getActiveBasket()
	{
		$basketFornitori 	= $_SESSION[session_id()]['basket_fornitori'];
		$basketFornitoriDe 	= $_SESSION[session_id()]['basket_fornitori_de'];
		$basket 			= $_SESSION[session_id()]['basket'];
		
		unset($basketFornitori['n_carrelli']);
		unset($basketFornitori['perc_occupazione']);
		unset($basketFornitoriDe['n_carrelli']);
		unset($basketFornitoriDe['perc_occupazione']);

// 		if(empty($basketFornitori) && empty($basketFornitoriDe) && empty($basket))
		if(empty($basket))
		{
			include_once(APP_ROOT."/beans/ecm_basket.php");
			include_once(APP_ROOT."/beans/ecm_basket_magazzino_fornitori.php");
			include_once(APP_ROOT."/beans/ecm_basket_magazzino_forn_de.php");
			include_once(APP_ROOT."/beans/ecm_basket_magazzino.php");
			include_once(APP_ROOT."/beans/giacenze_fornitori.php");
			include_once(APP_ROOT."/beans/giacenze_forn_gasa.php");
			include_once(APP_ROOT."/beans/giacenze.php");
			include_once(APP_ROOT."/beans/content.php");
			
			$beanBasket = new ecm_basket();
			$basket = $beanBasket->dbGetOneByCustomerAndDate(MyDB::connect(), $_SESSION['LoggedUser']['id']);

			$beanBasketMagazzinoFornitori = new ecm_basket_magazzino_fornitori();
			$dataF = $beanBasketMagazzinoFornitori->dbGetAllByIdBasket($this->conn, $basket['id']);
			$k = 1;
			foreach($dataF as $val)
			{
				$BeanGiacenze = new giacenze_fornitori(MyDB::connect(), $val['id_magazzino']);
				$_SESSION[session_id()]['basket_fornitori'][$k]['giacenza'] = $BeanGiacenze->vars();
				if(!empty($val['quantita']))
				{
					$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['quantita'] = $val['quantita'];
					$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita'] = $val['quantita'];
					$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita_pianale'] = null;
				}
				elseif(!empty($val['quantita_pianale']))
				{
					$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['quantita'] = $val['quantita'];
					$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita'] = null;
					$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita_pianale'] = $val['quantita_pianale'];
				}
				if(!empty($BeanGiacenze->qta_pianale))
					$exp = explode(' x ', $BeanGiacenze->qta_pianale);
				else
					$exp = explode(' x ', $BeanGiacenze->qta_scatola);

				$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['qty_scatola'] = $exp[1];
				$_SESSION[session_id()]['basket_fornitori'][$k]['price_it_qty'] = (str_replace(',', '.', $BeanGiacenze->prezzo_sc) * $exp[1] * $val['quantita'] );
				$k++;
			}

			$beanBasketMagazzinoFornitoriDe = new ecm_basket_magazzino_forn_de();
			$dataFDe = $beanBasketMagazzinoFornitoriDe->dbGetAllByIdBasket($this->conn, $basket['id']);
			$k = 1;
			foreach($dataFDe as $val)
			{
				$BeanGiacenze = new giacenze_forn_gasa(MyDB::connect(), $val['id_magazzino']);

				$_SESSION[session_id()]['basket_fornitori_de'][$k]['giacenza'] = $BeanGiacenze->vars();
				if(!empty($val['quantita']))
				{
					$_SESSION[session_id()]['basket_fornitori_de'][$k]['basket_qty']['quantita'] = $val['quantita'];
					$_SESSION[session_id()]['basket_fornitori_de'][$k]['basket_qty']['sel_quantita'] = $val['quantita'];
					$_SESSION[session_id()]['basket_fornitori_de'][$k]['basket_qty']['sel_quantita_pianale'] = null;
				}
				elseif(!empty($val['quantita_pianale']))
				{
					$_SESSION[session_id()]['basket_fornitori_de'][$k]['basket_qty']['quantita'] = $val['quantita'];
					$_SESSION[session_id()]['basket_fornitori_de'][$k]['basket_qty']['sel_quantita'] = null;
					$_SESSION[session_id()]['basket_fornitori_de'][$k]['basket_qty']['sel_quantita_pianale'] = $val['quantita_pianale'];
				}
			
				$_SESSION[session_id()]['basket_fornitori_de'][$k]['basket_qty']['qty_scatola'] = $exp[1];
				$_SESSION[session_id()]['basket_fornitori_de'][$k]['price_it_qty'] = (str_replace(',', '.', $BeanGiacenze->prezzo_sc) * $BeanGiacenze->qta_scatola * $val['quantita'] );
				$k++;
			}
			
			$beanBasketMagazzino = new ecm_basket_magazzino();
			$data = $beanBasketMagazzino->dbGetAllByIdBasket($this->conn, $basket['id']);
			if(!empty($data))
				unset($_SESSION[session_id()]['basket']);
				
			$k = 1;
			foreach($data as $val)
			{
				$BeanGiacenza = new giacenze($this->conn, $val['id_magazzino']);
				$BeanContent = new content($this->conn, $BeanGiacenza->id_content);
				$contenuto = $BeanContent->vars();
				$giacenza = $BeanGiacenza->vars();

				$_SESSION[session_id()]['basket'][$k]['giacenza'] = $BeanGiacenza->vars();
				$_SESSION[session_id()]['basket'][$k]['contenuto'] = $BeanContent->vars();
				
				$_SESSION[session_id()]['basket'][$k]['basket_qty']['quantita'] = $val['quantita']*$BeanGiacenza->quantita;
				$_SESSION[session_id()]['basket'][$k]['basket_qty']['sel_quantita'] = $val['quantita'];
				$_SESSION[session_id()]['basket'][$k]['price_it_qty'] = (str_replace(',', '.', $giacenza[$this->key_prezzo]) * $val['quantita'] )*$BeanGiacenza->quantita;
				
				if(!empty($val['colore']))
					$_SESSION[session_id()]['basket'][$k]['selected_color'] = $val['colore'];
				$k++;
			}
		}
	}
	
	function default_assignment()
	{
		$this->assignTemplateConfig();
		
		if(!empty($_SESSION['LoggedUser']['id']))
			$this->getActiveBasket();
		
		if($this->IsMobileDevice)
			$this->tEngine->assign('IMG_DIR'		, IMG_DIR.'/wap');
		else
			$this->tEngine->assign('IMG_DIR'		, IMG_DIR.'/web');

		$this->tEngine->assign('CURRENT_ACTION'		, $this->act);

		$this->tEngine->assign('is_tablet_device'	, $this->IsTabletDevice);
		$this->tEngine->assign('key_prezzo'			, $this->key_prezzo);
		$this->tEngine->assign('ECM_OPERATORE'		, 'ecommerce');
		$this->tEngine->assign('LoggedUser'			, $_SESSION['LoggedUser']);
		$this->tEngine->assign('external_css'		, EXTERNAL_CSS);
		$this->tEngine->assign('wp_external_root'	, WP_EXTERNAL_ROOT);
		
		$this->tEngine->assign('USER_FULL_NAME'		, $_SESSION['USER_FULL_NAME']);
		$this->tEngine->assign('APP_ROOT'			, APP_ROOT);
		$this->tEngine->assign('PREFIX_META_TITLE'	, PREFIX_META_TITLE);
		$this->tEngine->assign('JS_DIR'				, JS_DIR);
		$this->tEngine->assign('CSS_DIR'			, CSS_DIR);
		$this->tEngine->assign('WWW_ROOT'			, WWW_ROOT);
		$this->tEngine->assign('WWW_INDEX'			, WWW_INDEX);
		$this->tEngine->assign('CHARSET'			, CHARSET);
		$this->tEngine->assign('NEWS_HOME_TRUNCATE'	, NEWS_HOME_TRUNCATE);
		$this->tEngine->assign('theme'	, THEME);
		
		
		$this->tEngine->assign('INCOICE_STRLEN_1_FILED'	, INCOICE_STRLEN_1_FILED);
		$this->tEngine->assign('INCOICE_STRLEN_2_FILED'	, INCOICE_STRLEN_2_FILED);
		$this->tEngine->assign('INCOICE_STRLEN_3_FILED'	, INCOICE_STRLEN_3_FILED);
		$this->tEngine->assign('INCOICE_HTML_STRLEN_1_FILED'	, INCOICE_HTML_STRLEN_1_FILED);
		$this->tEngine->assign('INCOICE_HTML_STRLEN_2_FILED'	, INCOICE_HTML_STRLEN_2_FILED);
		$this->tEngine->assign('INCOICE_HTML_STRLEN_3_FILED'	, INCOICE_HTML_STRLEN_3_FILED);
		
		$this->tEngine->assign('CURRENT_DATE'			, date('d-m-Y H:i:s'));
		
		$this->tEngine->assign('LISTA_MAGAZZINO_TRUNCATE_NAME'			, LISTA_MAGAZZINO_TRUNCATE_NAME);
		$this->tEngine->assign('LISTA_MAGAZZINO_TRUNCATE_DESCRIPTION'	, LISTA_MAGAZZINO_TRUNCATE_DESCRIPTION);
		$this->tEngine->assign('SHOP_TRUNCATE_TEXT'						, SHOP_TRUNCATE_TEXT);
		
		$mapCorriere = array(
								'Bartolini' => 'http://www.bartolini.it',
								'DHL International' => 'http://www.dhl.it/',
								'FEDEX Express Italia' => 'http://www.fedex.com/it/',
								'PostaCelere' => 'http://poste.it/postali/online/index.shtml',
								'SDA Express Courier' => 'http://www.sda.it/',
								'TNT Italia' => 'http://www.tnttraco.it/',
								'UPS ITALIA' => 'http://www.ups.com/',
								'UNEX (United Express)' => 'http://www.unex.it/',
								'E-boost' => 'http://www.eboost.it/'
							);
		$this->tEngine->assign('mapCorriere', $mapCorriere);
		
// 		$mapPaymentType['CC'] = 'Carta di Credito';
// 		$mapPaymentType['vaglia'] = 'Vaglia Postale';
// 		$mapPaymentType['bonifico'] = 'Bonifico';
// 		$this->tEngine->assign('mapPaymentType', $mapPaymentType);
		/**
		 * Combo da DB
		 */
		
		
		$configCacheKey = 'fornitore_1_nome';
		if (!$fornitore = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT."/beans/giacenze_fornitori.php");
			$BeanContent = new giacenze_fornitori();
			$fornitore = $BeanContent->dbGetNomeFornitore(MyDB::connect());
			if(!empty($fornitore) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($fornitore, $configCacheKey);
		}
		$this->tEngine->assign('cmb_fornitore_nome', $fornitore);
		
		$configCacheKey = 'famiglie';
		if (!$famiglie = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			include_once(APP_ROOT."/beans/famiglie.php");
			$BeanFamiglie = new famiglie();
			$famiglie = $BeanFamiglie->dbGetAll($this->conn);
			if(!empty($famiglie))
				Base_CacheCore::getInstance()->save($famiglie, $configCacheKey);
		}		
		$this->tEngine->assign('cmb_famiglie', $famiglie);
		
		$configCacheKey = 'menu_categorie';
		if (!$menu = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT."/beans/gruppi_merceologici.php");
			$BeanGm = new gruppi_merceologici();
			$menu = $BeanGm->dbGetForCombo($this->conn, ' ORDER BY gruppo ASC');
			if(!empty($menu))
				Base_CacheCore::getInstance()->save($menu, $configCacheKey);
		}
		$this->tEngine->assign('menu', $menu);

		
		
		
		$configCacheKey = 'gruppi_merceologici';
		if (!$gm = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			include_once(APP_ROOT."/beans/gruppi_merceologici.php");
			$BeanGm = new gruppi_merceologici();
			$gm = $BeanGm->dbGetAll($this->conn, ' ORDER BY gruppo ASC');
			if(!empty($gm) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($gm, $configCacheKey);
		}
		$this->tEngine->assign('cmb_gm', $gm);
		
		$configCacheKey = 'colori';
		if (!$colori = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			include_once(APP_ROOT."/beans/content.php");
			$BeanContent = new content();
			$colori = $BeanContent->dbGetAllCaratteristiche($this->conn, 'C3');

			if(!empty($colori) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($colori, $configCacheKey);
		}
		$this->tEngine->assign('cmb_colori', $colori);
		
		$configCacheKey = 'tipo_colori';
		if (!$tipo_colore = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT."/beans/content.php");
			$BeanContent = new content();
			$tipo_colore = $BeanContent->dbGetAllCaratteristiche($this->conn, 'tipo_colore');

			if(!empty($tipo_colore) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($tipo_colore, $configCacheKey);
		}
		$this->tEngine->assign('cmb_tipo_colori', $tipo_colore);

		
		$configCacheKey = 'settori';
		if (!$settori = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT."/beans/settori_merceologici.php");
			$Bean = new settori_merceologici();
			$settori = $Bean->dbGetAll($this->conn, ' ORDER BY settore');
		
			if(!empty($settori) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($settori, $configCacheKey);
		}
		$this->tEngine->assign('cmb_settori', $settori);
		
		$configCacheKey = 'reparti';
		if (!$reparti = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT."/beans/reparti_merceologici.php");
			$Bean = new reparti_merceologici();
			$reparti = $Bean->dbGetAll($this->conn);
		
			if(!empty($reparti) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($reparti, $configCacheKey);
		}
		$this->tEngine->assign('cmb_reparti', $reparti);
		
		include_once(APP_ROOT."/beans/users_type.php");
		$BeanUserType = new users_type();
		$this->tEngine->assign('cmb_user_type', $BeanUserType->dbGetAll(MyDB::connect()));
		
		include_once(APP_ROOT."/beans/fornitore.php");
		$BeanFornitoree = new fornitore();
		$this->tEngine->assign('cmb_dhtmlx_fornitore', $BeanFornitoree->dbGetAllCustomField(MyDB::connect(), 'nome'));
		
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
		
		define('FATTURA_TAX_IVA', floatval('0.'.$iva[0]['name']));
		define('IVA', $iva[0]['name']);
		
		include_once(APP_ROOT.'/beans/banner.php');
		$BeanBanners = new banner();
		if(empty($_REQUEST['id_category']))
			$id_category = 0;
		else
			$id_category = $_REQUEST['id_category'];

		$data = $BeanBanners->dbGetAllByIdCategory($this->conn, $id_category);
		if($banners == array() || empty($data))
			$banners = $BeanBanners->dbGetAllByIdCategory($this->conn, 0);

		$this->tEngine->assign('banners', $banners);

		/**
		 * Combo da DB
		 */

		/**
		 * ASSEGNAZIONE PERCENTUALI DI RICARICO
		 */
		$configCacheKey = 'commissione_imballi_dendekker';
		if (!$commissione_imballi_dendekker = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT.'/beans/ApplicationSetup.php');
			$BeanApplicationSetup = new ApplicationSetup();
			$commissione_imballi_dendekker = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), $configCacheKey);
			if(!empty($altezzaCarrello) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($commissione_imballi_dendekker, $configCacheKey);
		}
		$this->tEngine->assign('commissione_imballi_dendekker', $commissione_imballi_dendekker[0]['name']);

		$configCacheKey = 'commissione_fissa_dendekker';
		if (!$commissione_fissa_dendekker = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT.'/beans/ApplicationSetup.php');
			$BeanApplicationSetup = new ApplicationSetup();
			$commissione_fissa_dendekker = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), $configCacheKey);
			if(!empty($altezzaCarrello) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($commissione_fissa_dendekker, $configCacheKey);
		}
		$this->tEngine->assign($configCacheKey, $commissione_fissa_dendekker[0]['name']);
		
		$configCacheKey = 'commissione_imballi_gasa';
		if (!$commissione_imballi_gasa = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT.'/beans/ApplicationSetup.php');
			$BeanApplicationSetup = new ApplicationSetup();
			$commissione_imballi_gasa = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), $configCacheKey);
			if(!empty($altezzaCarrello) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($commissione_imballi_gasa, $configCacheKey);
		}
		$this->tEngine->assign($configCacheKey, $commissione_imballi_gasa[0]['name']);
		
		$configCacheKey = 'commissione_fissa_gasa';
		if (!$commissione_fissa_gasa = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT.'/beans/ApplicationSetup.php');
			$BeanApplicationSetup = new ApplicationSetup();
			$commissione_fissa_gasa = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), $configCacheKey);
			if(!empty($altezzaCarrello) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($commissione_fissa_gasa, $configCacheKey);
		}
		$this->tEngine->assign($configCacheKey, $commissione_fissa_gasa[0]['name']);

		/**
		 * ASSEGNAZIONE PERCENTUALI DI RICARICO
		 */
		
		/**
		 * ASSEGNAZIONE DEI VOLUMI PER IL MAGAZZINO FORNITORI
		 */
		
		include_once(APP_ROOT.'/beans/ApplicationSetup.php');
		$BeanApplicationSetup = new ApplicationSetup();
		$this->commissioneScatolaDenDekker = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'commissione_scatola');
		$this->tEngine->assign('commissione_scatola_den_dekker', $this->commissioneScatolaDenDekker[0]['name']);
		
		$configCacheKey = 'altezza_carrello';
		if (!$altezzaCarrello = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT.'/beans/ApplicationSetup.php');
			$BeanApplicationSetup = new ApplicationSetup();
			$altezzaCarrello = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), $configCacheKey);
			if(!empty($altezzaCarrello) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($altezzaCarrello, $configCacheKey);
		}
		$configCacheKey = 'larghezza_carrello';
		if (!$larghezzaCarrello = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT.'/beans/ApplicationSetup.php');
			$BeanApplicationSetup = new ApplicationSetup();
			$larghezzaCarrello = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), $configCacheKey);
			if(!empty($larghezzaCarrello) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($larghezzaCarrello, $configCacheKey);
		}
		$configCacheKey = 'profondita_carrello';
		if (!$profonditaCarrello = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			include_once(APP_ROOT.'/beans/ApplicationSetup.php');
			$BeanApplicationSetup = new ApplicationSetup();
			$profonditaCarrello = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), $configCacheKey);
			if(!empty($profonditaCarrello) && USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($profonditaCarrello, $configCacheKey);
		}
		$this->volume_carrello = $altezzaCarrello[0]['name'] * $larghezzaCarrello[0]['name'] * $profonditaCarrello[0]['name'];
		$this->tEngine->assign('volume_carrello', $this->volume_carrello);
		
		$this->calcolaVolumiCarrello();
		$this->calcolaVolumiCarrelloNl();
		$this->calcolaVolumiCarrelloDe();
		
		/**
		 * ASSEGNAZIONE DEI VOLUMI PER IL MAGAZZINO FORNITORI
		 */

		include_once(APP_ROOT."/beans/content.php");
		include_once(APP_ROOT."/beans/giacenze.php");
		$configCacheKey = 'giacenze';
		if (!$giacenze = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			$BeanGiacenze = new giacenze();
			$giacenze = $BeanGiacenze->dbGetAllIdKey(MyDB::connect());
		
			if(!empty($giacenze) && CACHE_PRODUCTS)
				Base_CacheCore::getInstance()->save($giacenze, $configCacheKey);
		}
		
		$configCacheKey = 'content';
		if (!$contenuti = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			$BeanContent = new content();
			$contenuti = $BeanContent->dbGetAllIdKey(MyDB::connect());
		
			if(!empty($contenuti) && CACHE_PRODUCTS)
				Base_CacheCore::getInstance()->save($contenuti, $configCacheKey);
		}
		
		if(date('m') == 01)
		{
			include_once(APP_ROOT."/beans/index_fattura.php");
			$BeanIndexFattura = new index_fattura();
			$fattura = $BeanIndexFattura->dbGetAll(MyDB::connect());
			if($fattura['year'] < date('Y'))
				$BeanIndexFattura->reset(MyDB::connect());
		}
		
		if(!empty($_REQUEST['set_options']))
		{
			$this->tEngine->display('shared/OptionsTouch');
			exit();
		}
	}
	
	function calcolaVolumiCarrello()
	{
		$_SESSION[session_id()]['basket']['n_carrelli'] = 1;
		$volume_occupato = 0;
		foreach($_SESSION[session_id()]['basket'] as $vol)
		{
			if(!empty($vol['volume']))
			{
				$qty_sc = ($vol['giacenza']['quantita'])*$vol['basket_qty']['sel_quantita'];
				$qty_ca = $vol['giacenza']['quantita']*$vol['giacenza']['carrello'];
				$volume_occupato += ($qty_sc / $qty_ca)*100;

				if($volume_occupato > 100)
					$volume_occupato = $this->getParziale($volume_occupato);
			}
		}

		$perc_occupazione = round($volume_occupato);
		
		$perc_occupazione += round( ( $volume_occupato * 100 ) / $this->volume_carrello );
		$_SESSION[session_id()]['basket']['perc_occupazione'] = $perc_occupazione;
		$this->tEngine->assign('perc_occupazione', $_SESSION[session_id()]['basket']['perc_occupazione']);
	}
	
	function calcolaVolumiCarrelloNl()
	{
		$_SESSION[session_id()]['basket_fornitori']['n_carrelli'] = 1;
		$volume_occupato = 0;
		foreach($_SESSION[session_id()]['basket_fornitori'] as $vol)
		{
			if(!empty($vol['volume']))
			{
				if(!empty($vol['basket_qty']['sel_quantita_pianale']))
				{
					$qty_pi = $vol['giacenza']['qta_pianale']*$vol['basket_qty']['sel_quantita_pianale'];
					$qty_ca = $vol['giacenza']['carrello'];
					$volume_occupato += ($qty_pi / $qty_ca)*100;
				}
				if(!empty($vol['basket_qty']['sel_quantita']))
				{
					$qty_sc = ($vol['giacenza']['qta_scatola'])*$vol['basket_qty']['sel_quantita'];
					$qty_ca = $vol['giacenza']['qta_pianale']*$vol['giacenza']['qta_scatola']*$vol['giacenza']['carrello'];
	
					$volume_occupato += ($qty_sc / $qty_ca)*100;
					if($vol['giacenza']['altezza_pianta'] > 70)
						$flag = true;
				}
				
				if($volume_occupato > 100)
					$volume_occupato = $this->getParzialeNl($volume_occupato);
			}
		}
	
		if($flag)
			$volume_occupato = $volume_occupato - ($volume_occupato * 5 ) / 100;
	
		$perc_occupazione = round($volume_occupato);
		$_SESSION[session_id()]['basket_fornitori']['perc_occupazione'] = $perc_occupazione;
		$this->tEngine->assign('perc_occupazione', $_SESSION[session_id()]['basket_fornitori']['perc_occupazione']);
	}

	function calcolaVolumiCarrelloDe()
	{
		$_SESSION[session_id()]['basket_fornitori_de']['n_carrelli'] = 1;
		$volume_occupato = 0;
		foreach($_SESSION[session_id()]['basket_fornitori_de'] as $vol)
		{
			if(!empty($vol['volume']))
			{
				// 				$volume_occupato += $vol['volume']*$vol['basket_qty']['sel_quantita'];
				$volume_occupato += ($vol['volume']*$vol['basket_qty']['sel_quantita'])*$vol['basket_qty']['qty_scatola'];
				if($volume_occupato > $this->volume_carrello)
				{
					$volume_occupato = $this->getVolumeDe($volume_occupato);
					$perc_occupazione = 0;
					if($volume_occupato < 0)
						$volume_occupato = 0;
				}
			}
		}
	
		$perc_occupazione += round( ( $volume_occupato * 100 ) / $this->volume_carrello );
		$_SESSION[session_id()]['basket_fornitori_de']['perc_occupazione'] = $perc_occupazione;
		$this->tEngine->assign('perc_occupazione_de', $_SESSION[session_id()]['basket_fornitori_de']['perc_occupazione']);
	}
	
	function getParziale($volume_occupato)
	{
		if($volume_occupato > 100)
		{
			$volume_occupato = $volume_occupato - 100;
			$_SESSION[session_id()]['basket']['n_carrelli'] = $_SESSION[session_id()]['basket']['n_carrelli'] + 1;
			return $this->getVolume($volume_occupato);
		}
		return $volume_occupato;
	}

	function getParzialeNl($volume_occupato)
	{
		if($volume_occupato > 100)
		{
			$volume_occupato = $volume_occupato - 100;
			$_SESSION[session_id()]['basket_fornitori']['n_carrelli'] = $_SESSION[session_id()]['basket_fornitori']['n_carrelli'] + 1;
			return $this->getVolumeNl($volume_occupato);
		}
		return $volume_occupato;
	}	
	function getVolume($volume_occupato)
	{
		if($volume_occupato > $this->volume_carrello)
		{
			$volume_occupato = $volume_occupato - $this->volume_carrello;
			$_SESSION[session_id()]['basket']['n_carrelli'] = $_SESSION[session_id()]['basket']['n_carrelli'] + 1;
			return $this->getVolume($volume_occupato);
		}
		return $volume_occupato;
	}
	
	function getVolumeNl($volume_occupato)
	{
		if($volume_occupato > $this->volume_carrello)
		{
			$volume_occupato = $volume_occupato - $this->volume_carrello;
			$_SESSION[session_id()]['basket_fornitori']['n_carrelli'] = $_SESSION[session_id()]['basket_fornitori']['n_carrelli'] + 1;
			return $this->getVolume($volume_occupato);
		}
		return $volume_occupato;
	}
	
	function getVolumeDe($volume_occupato)
	{
		if($volume_occupato > $this->volume_carrello)
		{
			$volume_occupato = $volume_occupato - $this->volume_carrello;
			$_SESSION[session_id()]['basket_fornitori_de']['n_carrelli'] = $_SESSION[session_id()]['basket_fornitori_de']['n_carrelli'] + 1;
			return $this->getVolume($volume_occupato);
		}
		return $volume_occupato;
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
	
	function getPriceByQty($giacenza, $quantita)
	{
		return Currency::getPriceByQty($giacenza, $quantita);
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

	function createPdfInvoice($pathPdf = null, $includeTextIva, $fattura, $customer, $data, $imponibile, $percentSale, $prezzoScontato, $totale)
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
		$pdf->SetAuthor('Mediaedit');
		$pdf->AddPage();
		
		$pdf->WriteBody($fattura, $customer, $data, $imponibile, $percentSale, $prezzoScontato, $totale);
		
		if(!empty($pathPdf))
		{
			$pdf->Output($pathPdf, 'F');
//			echo $pdf->Output($fattura.'.pdf', 'S');
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
			$this->tEngine->compile_dir .= '/touch';
			$this->tEngine->template_dir .= '/touch';
		}
		elseif($this->IsTabletDevice)
		{
			$this->tEngine->compile_dir .= '/tablet';
			$this->tEngine->template_dir .= '/tablet';
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
			$this->tEngine->compile_dir .= '/touch';
			$this->tEngine->template_dir .= '/touch';
		}
		elseif($this->IsTabletDevice)
		{
			$this->tEngine->compile_dir .= '/tablet';
			$this->tEngine->template_dir .= '/tablet';
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
		
		if(!isset($this->params))
			$this->default_configure_mail();
//		$this->mail_factory();
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
		$this->params["host"]  = EMAIL_ADMIN_HOST;
		//$this->params["port"] = 25;
		$this->params["auth"]  = true;
		$this->params["username"]  = EMAIL_ADMIN_USERNAME;
		$this->params["password"]  = EMAIL_ADMIN_PASSWORD;
		
		$this->hdrs["Mailed-by"]  = EMAIL_ADMIN_HOST;
		$this->hdrs["Signed-by"]  = EMAIL_ADMIN_HOST;		
		$this->hdrs["From"]    = EMAIL_ADMIN_FROM;
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
		
		if(!isset($this->params))
			$this->default_configure_mail();
//		$this->mail_factory();
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
		$this->params["host"]  = EMAIL_ADMIN_HOST;
		//$this->params["port"] = 25;
		$this->params["auth"]  = true;
		$this->params["username"]  = EMAIL_ADMIN_USERNAME;
		$this->params["password"]  = EMAIL_ADMIN_PASSWORD;
		
		$this->hdrs["Mailed-by"]  = EMAIL_ADMIN_HOST;
		$this->hdrs["Signed-by"]  = EMAIL_ADMIN_HOST;		
		$this->hdrs["From"]    = EMAIL_ADMIN_FROM;
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
		
		if(!isset($this->params))
			$this->default_configure_mail();
//		$this->mail_factory();
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
		$this->params["host"]  = EMAIL_ADMIN_HOST;
		//$this->params["port"] = 25;
		$this->params["auth"]  = true;
		$this->params["username"]  = EMAIL_ADMIN_USERNAME;
		$this->params["password"]  = EMAIL_ADMIN_PASSWORD;
		
		$this->hdrs["Mailed-by"]  = EMAIL_ADMIN_HOST;
		$this->hdrs["Signed-by"]  = EMAIL_ADMIN_HOST;		
		$this->hdrs["From"]    = EMAIL_ADMIN_FROM;
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