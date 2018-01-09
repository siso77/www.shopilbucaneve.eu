<?php
//ini_set('display_errors', 'On');
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/barcodes.php");
class Search extends DBSmartyAction
{
	var $className;
	var $prefixCacheKey = 'ecm_content_search_';

	var $limit;
	var $limit_start;
	var $limit_end;

	function Search()
	{
		parent::DBSmartyAction();
				
		$this->className = get_class($this);

		if(!empty($_REQUEST['reset']))
		{
			unset($_SESSION[$this->className]['key_search']);
			unset($_SESSION[$this->className]['order_by']);
			unset($_SESSION[$this->className]['order_type']);
			unset($_SESSION[$this->className]['result']);
			unset($_SESSION[$this->className]['colore']);
			unset($_SESSION[$this->className]['tipo_colore']);
			unset($_SESSION[$this->className]['gm']);
			unset($_SESSION[$this->className]['id_settore']);
			unset($_SESSION[$this->className]['id_reparto']);
			unset($_SESSION[$this->className]['famiglia']);
			unset($_SESSION[$this->className]['name']);
			unset($_SESSION[$this->className]['price_from']);
			unset($_SESSION[$this->className]['price_to']);
			unset($_SESSION[$this->className]['varieta']);
		}
		$this->assignSearchFields();

		$this->setKeySearchInSession();
		
		if(empty($_REQUEST['pageID']))
		{
			$this->limit_start = 0;
			$this->limit_end = $this->rowForPage;
			$this->limit = ' LIMIT '.$this->limit_start.','.$this->limit_end;
			$_REQUEST['pageID'] = 1;
		}
		else
		{
			$this->limit_start = ($this->rowForPage * $_REQUEST['pageID']) - $this->rowForPage;
			$this->limit_end = $this->rowForPage;
			$this->limit = ' LIMIT '.$this->limit_start.','.$this->limit_end;
		}

		if(!empty($_REQUEST['layout']))
			$_SESSION[$this->className]['layout'] = $_REQUEST['layout'];
		
		if(!empty($_REQUEST['reset']))
		{
			$_SESSION[$this->className]['order_by'] = null;
			$_SESSION[$this->className]['order_type'] = null;
			$_SESSION[$this->className]['result'] = null;
		}
		
		if(key_exists('display_prod_img', $_REQUEST))
		{
			if($_REQUEST['display_prod_img'] == 1)
				$_SESSION[$this->className]['display_prod_img'] = true;
			elseif($_REQUEST['display_prod_img'] == 0)
			$_SESSION[$this->className]['display_prod_img'] = false;
		}
		$this->tEngine->assign('display_prod_img', $_SESSION[$this->className]['display_prod_img']);
		
		if(!empty($_REQUEST['only_disp']))
		{
			$_SESSION[$this->className]['all_disp'] = false;
			$_SESSION[$this->className]['only_disp'] = true;
			
			$_SESSION[$this->className]['order_by'] = null;
			$_SESSION[$this->className]['order_type'] = null;
		}
		elseif($_REQUEST['all_disp'])
		{
			$_SESSION[$this->className]['only_disp'] = false;
			$_SESSION[$this->className]['all_disp'] = true;
			
			$_SESSION[$this->className]['order_by'] = null;
			$_SESSION[$this->className]['order_type'] = null;
		}

		if(!empty($_SESSION[$this->className]['only_disp']))
			$dataList = $this->getDispoData();
		elseif(!empty($_SESSION[$this->className]['all_disp']))
			$dataList = $this->getDefaultData();
		else
		{
			$_SESSION[$this->className]['only_disp'] = true;
			$_SESSION[$this->className]['all_disp'] = false;
			$dataList = $this->getDispoData();
		}
		$BeanContent = new content();

// 		$price_to = $BeanContent->dbFree($this->conn, "SELECT MAX(".$this->key_prezzo.") as MAX FROM content");
// 		$price_from = $BeanContent->dbFree($this->conn, "SELECT MIN(".$this->key_prezzo.") as MIN FROM content");
		if($_SESSION[$this->className]['only_disp'])
		{
			$price_to = $BeanContent->dbFree($this->conn, "SELECT MAX(prezzo_0) as MAX FROM giacenze");
			$price_from = $BeanContent->dbFree($this->conn, "SELECT MIN(prezzo_0) as MIN FROM giacenze");
		}
		else
		{
			$price_to = $BeanContent->dbFree($this->conn, "SELECT MAX(prezzo_0) as MAX FROM content");
			$price_from = $BeanContent->dbFree($this->conn, "SELECT MIN(prezzo_0) as MIN FROM content");
		}
		
		$this->tEngine->assign('default_price_from'  , (empty($price_from['MIN'])) ? 0 : round($price_from['MIN']));
		$this->tEngine->assign('default_price_to'  , round($price_to['MAX']));

		$p = new MyPager($dataList['content'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign('content', $p->getData());

		$this->tEngine->assign('tot_items'  , $dataList['num_contents']['num']);
		$this->tEngine->assign('last_page'  , ( round(($dataList['num_contents']['num'] / $this->rowForPage)) == 0)? 1 : round(($dataList['num_contents']['num'] / $this->rowForPage)));
		$p->pager->_currentPage = $_REQUEST['pageID'];

//		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
//		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);

		$this->tEngine->assign('key_search', $_SESSION[$this->className]['key_search']);
		
		if(!empty($_SESSION[$this->className]['all_disp']))
			$this->tEngine->assign('all_disp', true);
		if(!empty($_SESSION[$this->className]['only_disp']))
			$this->tEngine->assign('only_disp', true);
		
		if($this->IsMobileDevice)
		{
			$this->tEngine->assign('tpl_action', 'Search');
			$this->tEngine->display('Index');
		}
		elseif(empty($_SESSION[$this->className]['layout']) || empty($_REQUEST['is_ajax']))
		{
			if(empty($_SESSION[$this->className]['layout']))
			{
				$this->tEngine->assign('tpl_action', DEFAULT_LAYOUT_DISPLAY);
				$_SESSION[$this->className]['layout'] = DEFAULT_LAYOUT_DISPLAY_SESSION;
			}
			else
			{
				switch ($_SESSION[$this->className]['layout'])
				{
					case 'grid':
							$this->tEngine->assign('tpl_action', 'SearchListDetailed');
					break;
					case 'boxed':
						$this->tEngine->assign('tpl_action', 'SearchBoxed');
					break;
					case 'thumb':
						$this->tEngine->assign('tpl_action', 'SearchThumb');
					break;
				}
			}
			$this->tEngine->display('Index');
		}
		else 
		{
			switch ($_SESSION[$this->className]['layout'])
			{
				case 'grid':
					echo $this->tEngine->fetch('SearchListDetailed');
				break;
				case 'boxed':
					echo $this->tEngine->fetch('SearchBoxed');
				break;
				case 'thumb':
					echo $this->tEngine->fetch('SearchThumb');
				break;
			}
		}
	}
	
	function setKeySearchInSession()
	{
		if($_REQUEST['colore'] == '' && !empty($_REQUEST['go_search']))
			$_SESSION[$this->className]['colore'] = null;
		if($_REQUEST['tipo_colore'] == '' && !empty($_REQUEST['go_search']))
			$_SESSION[$this->className]['tipo_colore'] = null;
		if($_REQUEST['gm'] == '' && !empty($_REQUEST['go_search']))
			$_SESSION[$this->className]['gm'] = null;
		if($_REQUEST['famiglia'] == '' && !empty($_REQUEST['go_search']))
			$_SESSION[$this->className]['famiglia'] = null;
		if($_REQUEST['name'] == '' && !empty($_REQUEST['go_search']))
			$_SESSION[$this->className]['name'] = null;
		if($_REQUEST['price_from'] == '' && !empty($_REQUEST['go_search']))
			$_SESSION[$this->className]['price_from'] = null;
		if($_REQUEST['price_to'] == '' && !empty($_REQUEST['go_search']))
			$_SESSION[$this->className]['price_to'] = null;

		if($_REQUEST['id_settore'] == '' && !empty($_REQUEST['go_search']))
			$_SESSION[$this->className]['id_settore'] = null;
		if($_REQUEST['id_reparto'] == '' && !empty($_REQUEST['go_search']))
			$_SESSION[$this->className]['id_reparto'] = null;
		
		$is_empty = true;
		if(!empty($_SESSION[$this->className]['colore']))
		{
			$_REQUEST['colore'] = $_SESSION[$this->className]['colore'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['tipo_colore']))
		{
			$_REQUEST['tipo_colore'] = $_SESSION[$this->className]['tipo_colore'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['gm']))
		{
			$_REQUEST['gm'] = $_SESSION[$this->className]['gm'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['famiglia']))
		{
			$_REQUEST['famiglia'] = $_SESSION[$this->className]['famiglia'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['name']))
		{
			$_REQUEST['name'] = $_SESSION[$this->className]['name'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['price_from']))
		{
			$_REQUEST['price_from'] = $_SESSION[$this->className]['price_from'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['price_to']))
		{
			$_REQUEST['price_to'] = $_SESSION[$this->className]['price_to'];
			$is_empty = false;
		}
		
		if(!empty($_SESSION[$this->className]['id_settore']))
		{
			$_REQUEST['id_settore'] = $_SESSION[$this->className]['id_settore'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['id_reparto']))
		{
			$_REQUEST['id_reparto'] = $_SESSION[$this->className]['id_reparto'];
			$is_empty = false;
		}
		
		if(!$is_empty)
			$_SERVER['REQUEST_METHOD'] = 'POST';
	}
	
	function getDispoData()	
	{
		$BeanContent = new content();
// 		if( ($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['order_by'])) && empty($_REQUEST['is_ajax']) )
		if( $_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['order_by']) || !empty($_REQUEST['pageID']))
		{
			if($_REQUEST['key_search'] == 'Cerca...' || $_REQUEST['key_search'] == 'Cerca Varieta...')
			{
				$_REQUEST['key_search'] = null;
				$_SESSION[$this->className]['key_search'] = null;
			}
			if(!empty($_REQUEST['order_by']))
			{
				$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
				$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
				$_SESSION[$this->className]['result'] = null;
			}
			if(!empty($_REQUEST['key_search']))
				$_SESSION[$this->className]['key_search'] = $_REQUEST['key_search'];
			if(!empty($_SESSION[$this->className]['key_search']))
			{
				$BeanBarCodes = new barcodes();
				$barcodes = $BeanBarCodes->dbSearch($this->conn, " bar_code LIKE '".$_SESSION[$this->className]['key_search']."' OR codice_articolo LIKE '".$_SESSION[$this->className]['key_search']."'");
				foreach ($barcodes as $barcode)
					$barcodesToSearch .= $barcode['codice_articolo'].",";
				$barcodesToSearch = substr($barcodesToSearch, 0, -1);
				
// 				$where .= " (giacenze.descrizione LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " (giacenze.bar_code LIKE '%".$_SESSION[$this->className]['key_search']."%' OR";
				$where .= " content.descrizione_it LIKE '".$_SESSION[$this->className]['key_search']."%' OR";
				$where .= " giacenze.descrizione LIKE '".$_SESSION[$this->className]['key_search']."%' OR";
				$where .= " content.nome_it LIKE '%".$_SESSION[$this->className]['key_search']."%' ";
				
// 				if(!empty($barcodesToSearch))
// 					$where .= " OR content.vbn IN ('".$barcodesToSearch."') ";
				$where .= ") AND";
			}
			if(!empty($_SESSION[$this->className]['varieta']))
				$where .= " content.C1 LIKE '".$_SESSION[$this->className]['varieta']."%' OR";
			
			if(!empty($_REQUEST['price_from']) && !empty($_REQUEST['price_to']))
				$where .= " giacenze.prezzo_0 BETWEEN ".$_REQUEST['price_from']." AND ".$_REQUEST['price_to']." OR ";
			elseif(!empty($_REQUEST['price_from']) && empty($_REQUEST['price_to']))
				$where .= " giacenze.prezzo_0 > ".$_REQUEST['price_from']." OR ";
			elseif(empty($_REQUEST['price_from']) && !empty($_REQUEST['price_to']))
				$where .= " giacenze.prezzo_0 < ".$_REQUEST['price_to']." OR ";

// 			if(!empty($_REQUEST['gm']) && $_REQUEST['gm'] != 'empty')
// 				$where .= " content.id_gm = ".$_REQUEST['gm']." AND";
			
			if(!empty($_REQUEST['gm']) && $_REQUEST['gm'] != 'empty')
				$where .= " giacenze.id_gm = ".$_REQUEST['gm']." AND";
			
			if(!empty($_REQUEST['famiglia']) && $_REQUEST['famiglia'] != 'empty')
				$where .= " giacenze.id_famiglia = ".$_REQUEST['famiglia']." AND";
				
// 			if(!empty($_REQUEST['gm']) && $_REQUEST['gm'] != 'empty')
// 				$where .= " content.id_gm = ".$_REQUEST['gm']." AND";
				
//			if(!empty($_REQUEST['id_settore']) && $_REQUEST['id_settore'] != 'empty')
//				$where .= " content.id_settore = ".$_REQUEST['id_settore']." AND";

//			if(!empty($_REQUEST['id_reparto']) && $_REQUEST['id_reparto'] != 'empty')
//				$where .= " content.id_reparto = ".$_REQUEST['id_reparto']." AND";

			if(!empty($_REQUEST['colore']) && $_REQUEST['colore'] != 'empty')
				$where .= " C3 LIKE '%".$_REQUEST['colore']."%' AND";
			if(!empty($_REQUEST['tipo_colore']) && $_REQUEST['tipo_colore'] != 'empty')
				$where .= " tipo_colore LIKE '%".$_REQUEST['tipo_colore']."%' OR";

			if(!empty($_REQUEST['stato']) && $_REQUEST['stato'] != 'empty')
				$where .= " giacenze.stato = '".$_REQUEST['stato']."' AND";
			
			$where = substr($where, 0, -3);

			if(!empty($where))
			{
				$where = " AND (".$where;
				$where .= ")";
			}
			
			if($_SESSION[$this->className]['display_prod_img'])
				$where .= " AND giacenze.have_image = 1";
				
			if(!empty($_SESSION[$this->className]['order_by']))
				$order = ' ORDER BY '.$_SESSION[$this->className]['order_by'].' '.$_SESSION[$this->className]['order_type'];
			else
				$order = ' ORDER BY content.nome_it ASC';
				
			$configCacheKey = $this->prefixCacheKey.'_disp'.md5($where.$order.$this->limit);
			$content = Base_CacheCore::getInstance()->load($configCacheKey);
			if (empty($content)) 
			{
				$content = $BeanContent->dbSearchDisponibili($this->conn, $where.$order.$this->limit);
				if(!empty($content) && CACHE_PRODUCTS)
					Base_CacheCore::getInstance()->save($content, $configCacheKey);
			}			
		}
		else
		{
			if($_SESSION[$this->className]['display_prod_img'])
				$where .= " AND content.have_image = 1";
				
			if(!empty($_SESSION[$this->className]['order_by']))
				$order = ' ORDER BY '.$_SESSION[$this->className]['order_by'].' '.$_SESSION[$this->className]['order_type'];

			$configCacheKey = $this->prefixCacheKey.'_disp'.md5($this->limit);
			if (!$content = Base_CacheCore::getInstance()->load($configCacheKey)) 
			{
				$content = $BeanContent->dbSearchDisponibili($this->conn, $where.$order.$this->limit);
				if(!empty($content) && CACHE_PRODUCTS)
					Base_CacheCore::getInstance()->save($content, $configCacheKey);
			}
		}

		$configCacheKey = 'num_content_disp'.md5($where.$this->limit);
		if (!$num_contents = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			$num_contents = $BeanContent->dbSearchCountedDisponibili($this->conn, $where);
			if(!empty($content) && CACHE_PRODUCTS)
				Base_CacheCore::getInstance()->save($num_contents, $configCacheKey);
		}

		return array('content'=>$content, 'num_contents'=>$num_contents);		
	}
	
	function assignSearchFields()
	{
		if(!empty($_REQUEST['colore']) && $_REQUEST['colore'] != 'empty')
			$_SESSION[$this->className]['colore'] = $_REQUEST['colore'];
		elseif($_REQUEST['colore'] == 'empty')
			$_SESSION[$this->className]['colore'] = null;
		
		if(!empty($_REQUEST['tipo_colore']) && $_REQUEST['tipo_colore'] != 'empty')
			$_SESSION[$this->className]['tipo_colore'] = $_REQUEST['tipo_colore'];
		elseif($_REQUEST['tipo_colore'] == 'empty')
			$_SESSION[$this->className]['tipo_colore'] = null;
		
		if(!empty($_REQUEST['gm']) && $_REQUEST['gm'] != 'empty')
			$_SESSION[$this->className]['gm'] = $_REQUEST['gm'];
		elseif($_REQUEST['gm'] == 'empty')
			$_SESSION[$this->className]['gm'] = null;
		
		if(!empty($_REQUEST['famiglia']) && $_REQUEST['famiglia'] != 'empty')
			$_SESSION[$this->className]['famiglia'] = $_REQUEST['famiglia'];
		elseif($_REQUEST['famiglia'] == 'empty')
			$_SESSION[$this->className]['famiglia'] = null;
		
		if(!empty($_REQUEST['name']) && $_REQUEST['name'] != 'empty')
			$_SESSION[$this->className]['name'] = $_REQUEST['name'];
		elseif($_REQUEST['name'] == 'empty')
			$_SESSION[$this->className]['name'] = null;
		
		if(!empty($_REQUEST['price_from']))
			$_SESSION[$this->className]['price_from'] = $_REQUEST['price_from'];
		if(!empty($_REQUEST['price_to']))
			$_SESSION[$this->className]['price_to'] = $_REQUEST['price_to'];


		if(!empty($_REQUEST['id_settore']))
			$_SESSION[$this->className]['id_settore'] = $_REQUEST['id_settore'];
		if(!empty($_REQUEST['id_reparto']))
			$_SESSION[$this->className]['id_reparto'] = $_REQUEST['id_reparto'];
		
		if(!empty($_REQUEST['varieta']) && $_REQUEST['varieta'] != 'Cerca Varieta...')
			$_SESSION[$this->className]['varieta'] = $_REQUEST['varieta'];
		
		$assignSearchFields['varieta'] = $_SESSION[$this->className]['varieta'];
		
		$assignSearchFields['colore'] = $_SESSION[$this->className]['colore'];
		$assignSearchFields['tipo_colore'] = $_SESSION[$this->className]['tipo_colore'];
		$assignSearchFields['gm'] = $_SESSION[$this->className]['gm'];
		$assignSearchFields['famiglia'] = $_SESSION[$this->className]['famiglia'];
		$assignSearchFields['name'] = $_SESSION[$this->className]['name'];
		$assignSearchFields['price_from'] = $_SESSION[$this->className]['price_from'];
		$assignSearchFields['price_to'] = $_SESSION[$this->className]['price_to'];

		$assignSearchFields['id_settore'] = $_SESSION[$this->className]['id_settore'];
		$assignSearchFields['id_reparto'] = $_SESSION[$this->className]['id_reparto'];
		
		$this->tEngine->assign('menu_gm_selected', $_SESSION[$this->className]['gm']);
		$this->tEngine->assign('search', $assignSearchFields);		
	}
	
	function getDefaultData()
	{
		$BeanContent = new content();
		if( $_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['order_by']) || !empty($_REQUEST['pageID']) )
		{
			if($_REQUEST['key_search'] == 'Cerca...')
			{
				$_REQUEST['key_search'] = null;
				$_SESSION[$this->className]['key_search'] = null;
			}			
			if(!empty($_REQUEST['order_by']))
			{
				$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
				$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
				$_SESSION[$this->className]['result'] = null;
			}
			if(!empty($_REQUEST['key_search']))
				$_SESSION[$this->className]['key_search'] = $_REQUEST['key_search'];
			if(!empty($_SESSION[$this->className]['key_search']))
			{
				$where .= " (content.nome_it LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " content.descrizione_it LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " content.vbn LIKE '%".$_SESSION[$this->className]['key_search']."%' ) AND";
			}
			if(!empty($_REQUEST['price_from']) && !empty($_REQUEST['price_to']))
				$where .= " content.prezzo_0 BETWEEN ".$_REQUEST['price_from']." AND ".$_REQUEST['price_to']." OR ";
			elseif(!empty($_REQUEST['price_from']) && empty($_REQUEST['price_to']))
				$where .= " content.prezzo_0 > ".$_REQUEST['price_from']." OR ";
			elseif(empty($_REQUEST['price_from']) && !empty($_REQUEST['price_to']))
				$where .= " content.prezzo_0 < ".$_REQUEST['price_to']." OR ";
			
// 			if(!empty($_REQUEST['gm']) && $_REQUEST['gm'] != 'empty')
// 				$where .= " content.id_gm = ".$_REQUEST['gm']." AND";
			
			if(!empty($_REQUEST['gm']) && $_REQUEST['gm'] != 'empty')
				$where .= " (giacenze.id_gm = ".$_REQUEST['gm']." OR content.id_gm = ".$_REQUEST['gm'].") AND";
								
			if(!empty($_REQUEST['id_settore']) && $_REQUEST['id_settore'] != 'empty')
				$where .= " content.id_settore = ".$_REQUEST['id_settore']." AND";
			if(!empty($_REQUEST['id_reparto']) && $_REQUEST['id_reparto'] != 'empty')
				$where .= " content.id_reparto = ".$_REQUEST['id_reparto']." AND";
				
			
			if(!empty($_REQUEST['colore']) && $_REQUEST['colore'] != 'empty')
				$where .= " C3 LIKE '%".$_REQUEST['colore']."%' AND";
			if(!empty($_REQUEST['tipo_colore']) && $_REQUEST['tipo_colore'] != 'empty')
				$where .= " tipo_colore LIKE '%".$_REQUEST['tipo_colore']."%' OR";
				
			$where = substr($where, 0, -3);
			if(!empty($where))
			{
				$where = " AND (".$where;
				$where .= ")";
			}
			if($_SESSION[$this->className]['display_prod_img'])
				$where .= " AND content.have_image = 1";
			
			if(!empty($_SESSION[$this->className]['order_by']))
				$order = ' ORDER BY '.$_SESSION[$this->className]['order_by'].' '.$_SESSION[$this->className]['order_type'];

			$configCacheKey = $this->prefixCacheKey.'_default'.md5($where.$order.$this->limit);
			$content = Base_CacheCore::getInstance()->load($configCacheKey);
			if (empty($content))  
			{
				$content = $BeanContent->dbSearch($this->conn, $where.$order.$this->limit);
				
				if(!empty($content) && CACHE_PRODUCTS)
					Base_CacheCore::getInstance()->save($content, $configCacheKey);
			}
		}
		else
		{
			if($_SESSION[$this->className]['display_prod_img'])
				$where .= " AND content.have_image = 1";
				
			if(!empty($_SESSION[$this->className]['order_by']))
				$order = ' ORDER BY '.$_SESSION[$this->className]['order_by'].' '.$_SESSION[$this->className]['order_type'];
			
			$configCacheKey = $this->prefixCacheKey.'_default'.md5($this->limit);
			if (!$content = Base_CacheCore::getInstance()->load($configCacheKey)) 
			{
				$content = $BeanContent->dbSearch($this->conn, $where.$order.$this->limit);

				if(!empty($content) && CACHE_PRODUCTS)
					Base_CacheCore::getInstance()->save($content, $configCacheKey);
			}
		}

		
		$configCacheKey = 'num_content_all'.md5($where.$this->limit);
		if (!$num_contents = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			$num_contents = $BeanContent->dbSearchCounted($this->conn, $where);
			if(!empty($content) && CACHE_PRODUCTS)
				Base_CacheCore::getInstance()->save($num_contents, $configCacheKey);
		}
		
// 		$this->assignSearchFields();
		
		return array('content'=>$content, 'num_contents'=>$num_contents);
	}
}
?>