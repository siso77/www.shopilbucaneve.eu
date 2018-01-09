<?php
//ini_set('display_errors', 'On');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/giacenze_forn_gasa.php");

class SearchFornitoriDe extends DBSmartyAction
{
	var $className;
	var $prefixCacheKey = 'ecm_content_search_forn_gasa_';

	var $limit;
	var $limit_start;
	var $limit_end;

	function SearchFornitoriDe()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(empty($_REQUEST['act']))
			$_REQUEST['act'] = $this->className;
		
		if(!empty($_REQUEST['reset']))
		{
			unset($_SESSION[$this->className]['key_search']);
			unset($_SESSION[$this->className]['order_by']);
			unset($_SESSION[$this->className]['order_type']);
			unset($_SESSION[$this->className]['result']);
			unset($_SESSION[$this->className]['colore']);
			unset($_SESSION[$this->className]['tipo_colore']);
			unset($_SESSION[$this->className]['gm']);
			unset($_SESSION[$this->className]['famiglia']);
			unset($_SESSION[$this->className]['name']);
			unset($_SESSION[$this->className]['price_from']);
			unset($_SESSION[$this->className]['price_to']);
			unset($_SESSION[$this->className]['stato']);
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

		$dataList = $this->getDefaultData();

		$BeanContent = new content();
		$price_to = $BeanContent->dbFree($this->conn, "SELECT MAX(prezzo_sc) as MAX FROM giacenze_forn_gasa");
		$price_from = $BeanContent->dbFree($this->conn, "SELECT MIN(prezzo_sc) as MIN FROM giacenze_forn_gasa");
		
		$this->tEngine->assign('default_price_from'  , (empty($price_from['MIN'])) ? 0 : round($price_from['MIN']));
		$this->tEngine->assign('default_price_to'  , round($price_to['MAX']));

		$p = new MyPager($dataList['content'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign('content', $p->getData());
		
		$this->tEngine->assign('tot_items'  , $dataList['num_contents']['num']);
		$this->tEngine->assign('last_page'  , ( round(($dataList['num_contents']['num'] / $this->rowForPage)) == 0)? 1 : round(($dataList['num_contents']['num'] / $this->rowForPage)));
		$p->pager->_currentPage = $_REQUEST['pageID'];

		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('numViewPage', $this->numViewPage);

		$this->tEngine->assign('key_search', $_SESSION[$this->className]['key_search']);
		
		$BeanContent = new giacenze_forn_gasa();
		$this->tEngine->assign('cmb_gm_fornitori', $BeanContent->dbGetGm($this->conn, " AND fornitore = 'gasa'"));
		
		if(!empty($_SESSION[$this->className]['all_disp']))
			$this->tEngine->assign('all_disp', true);
		if(!empty($_SESSION[$this->className]['only_disp']))
			$this->tEngine->assign('only_disp', true);

		if($this->IsMobileDevice)
		{
			$this->tEngine->assign('tpl_action', 'SearchFornitoriDe');
			$this->tEngine->display('Index');
		}
		elseif(empty($_SESSION[$this->className]['layout']) || empty($_REQUEST['is_ajax']))
		{
			if(empty($_SESSION[$this->className]['layout']))
			{
				$this->tEngine->assign('tpl_action', 'SearchFornitoriDe/'.DEFAULT_LAYOUT_DISPLAY);
				$_SESSION[$this->className]['layout'] = DEFAULT_LAYOUT_DISPLAY_SESSION;
			}
			else
			{
				switch ($_SESSION[$this->className]['layout'])
				{
					case 'grid':
						$this->tEngine->assign('tpl_action', 'SearchFornitoriDe/SearchListDetailed');
					break;
					case 'boxed':
						$this->tEngine->assign('tpl_action', 'SearchFornitoriDe/SearchBoxed');
					break;
					case 'thumb':
						$this->tEngine->assign('tpl_action', 'SearchFornitoriDe/SearchThumb');
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
					echo $this->tEngine->fetch('SearchFornitoriDe/SearchListDetailed');
				break;
				case 'boxed':
					echo $this->tEngine->fetch('SearchFornitoriDe/SearchBoxed');
				break;
				case 'thumb':
					echo $this->tEngine->fetch('SearchFornitoriDe/SearchThumb');
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
		if(!$is_empty)
			$_SERVER['REQUEST_METHOD'] = 'POST';
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

		if(!empty($_REQUEST['stato']) && $_REQUEST['stato'] != 'empty')
			$_SESSION[$this->className]['stato'] = $_REQUEST['stato'];
		elseif($_REQUEST['stato'] == 'empty')
			$_SESSION[$this->className]['stato'] = null;
		
		$assignSearchFields['colore'] = $_SESSION[$this->className]['colore'];
		$assignSearchFields['tipo_colore'] = $_SESSION[$this->className]['tipo_colore'];
		$assignSearchFields['gm'] = $_SESSION[$this->className]['gm'];
		$assignSearchFields['famiglia'] = $_SESSION[$this->className]['famiglia'];
		$assignSearchFields['name'] = $_SESSION[$this->className]['name'];
		$assignSearchFields['price_from'] = $_SESSION[$this->className]['price_from'];
		$assignSearchFields['price_to'] = $_SESSION[$this->className]['price_to'];
		$assignSearchFields['stato'] = $_SESSION[$this->className]['stato'];
		
		$this->tEngine->assign('search', $assignSearchFields);		
	}
	
	function getDefaultData()
	{
		$BeanContent = new giacenze_forn_gasa();
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
				$where .= " (giacenze_forn_gasa.codice LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " giacenze_forn_gasa.bar_code LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " giacenze_forn_gasa.descrizione LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " giacenze_forn_gasa.altezza_pianta LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " giacenze_forn_gasa.diametro_vaso LIKE '%".$_SESSION[$this->className]['key_search']."%' ) AND";
			}
			if(!empty($_REQUEST['price_from']) && !empty($_REQUEST['price_to']))
				$where .= " giacenze_forn_gasa.prezzo_sc BETWEEN ".$_REQUEST['price_from']." AND ".$_REQUEST['price_to']." OR ";
			elseif(!empty($_REQUEST['price_from']) && empty($_REQUEST['price_to']))
				$where .= " giacenze_forn_gasa.prezzo_sc > ".$_REQUEST['price_from']." OR ";
			elseif(empty($_REQUEST['price_from']) && !empty($_REQUEST['price_to']))
				$where .= " giacenze_forn_gasa.prezzo_sc < ".$_REQUEST['price_to']." OR ";
				
			if(!empty($_SESSION[$this->className]['stato']) && $_SESSION[$this->className]['stato'] != 'empty')
				$where .= " giacenze_forn_gasa.stato = '".$_SESSION[$this->className]['stato']."' OR ";
				
			$where = substr($where, 0, -3);
			if(!empty($where))
			{
				$where = " AND (".$where;
				$where .= ")";
			}
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

		
		$configCacheKey = 'num_dendekker_all'.md5($where.$this->limit);
		if (!$num_contents = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			$num_contents = $BeanContent->dbSearchCounted($this->conn, $where);
			if(!empty($content) && CACHE_PRODUCTS)
				Base_CacheCore::getInstance()->save($num_contents, $configCacheKey);
		}

		return array('content'=>$content, 'num_contents'=>$num_contents);
	}
}
?>