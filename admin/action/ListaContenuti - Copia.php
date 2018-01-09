<?php
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/casa_editrice.php");
include_once(APP_ROOT."/beans/autori.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/distributore.php");
include_once(APP_ROOT."/beans/tipo_presa_carico.php");
include_once(APP_ROOT."/beans/category.php");
include_once(APP_ROOT."/beans/brands.php");

class ListaContenuti extends DBSmartyAction
{
	var $className;

	function setSearchKeys($request)
	{
		unset($request['act']);
		unset($request['search']);
		if(!empty($request))
			$_SESSION[$this->className]['key_searched'] = $request;
	}

	function ListaContenuti()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);

		$this->setSearchKeys($_REQUEST);
		
		$BeanCategory = new category();
		$this->tEngine->assign('cmb_category', $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC'));
		
		if(!empty($_REQUEST['export']))
			$this->exportExcel();
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanContent = new content();
			$BeanContent->dbDelete($this->conn,array($_REQUEST['id']), true);
		}
		
		$BeanCategory = new category();
		$Categories = $BeanCategory->dbGetCategoryTree($this->conn);
		$this->tEngine->assign('categories', $Categories);
		
		$BeanBrand = new brands();
		$Brands = $BeanBrand->dbGetAll($this->conn, 'name', 'ASC');
		$this->tEngine->assign('brands', $Brands);
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['key_search'] = $_REQUEST['key_search'];
				$where = " AND (magazzino.bar_code LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.name_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.description_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.price_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR brands.name LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR category.name LIKE '%".$_REQUEST['key_search']."%')";
				$where = " AND (content.name_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.description_it LIKE '%".$_REQUEST['key_search']."%')";
			}
			else 
			{
				$_SESSION[$this->className]['key_search'] = null;
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['order_by'] = null;
				$_SESSION[$this->className]['order_type'] = null;
			}			
		}
		elseif(!empty($_SESSION[$this->className]['key_searched']))
		{
			$_SESSION[$this->className]['result'] = null;
			$_SESSION[$this->className]['key_search'] = $_SESSION[$this->className]['key_searched']['key_search'];
			$where = " AND (magazzino.bar_code LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
			$where .= " OR content.name_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
			$where .= " OR content.description_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
			$where .= " OR content.price_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
			$where .= " OR brands.name LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
			$where .= " OR category.name LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%')";
			$where = " AND (content.name_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
			$where .= " OR content.description_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%')";
		}
		else
			$where = '';

		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
			$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
			$_SESSION[$this->className]['result'] = null;
		}

		if(!empty($_REQUEST['id_brand']))
			$id_brand = $_REQUEST['id_brand'];
		elseif(!empty($_SESSION[$this->className]['key_searched']['id_brand']))
			$id_brand = $_SESSION[$this->className]['key_searched']['id_brand'];
		
		if(!empty($id_brand))
		{
			$where .= " AND content.id_brand = ".$id_brand."";
			$keysSearchedBrand = array('id_brand'=>$id_brand);
			$this->tEngine->assign('id_brand', $id_brand);
		}
		
		if(!empty($_REQUEST['id_category']))
			$id_category = $_REQUEST['id_category'];
		if(!empty($_SESSION[$this->className]['key_searched']['id_category']))
			$id_category = $_SESSION[$this->className]['key_searched']['id_category'];

		if(!empty($id_category))
		{
			$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $id_category);
			if(is_array($ListCategory) && $ListCategory != array())
				$where .= " AND category.id IN(".implode(", ", $ListCategory).", ".$id_category.")";
			else
				$where .= " AND category.id = ".$id_category."";

			$keysSearchedCategory = array('id_category'=>$id_category);
			$this->tEngine->assign('id_category', $id_category);
		}
		
		if(is_array($keysSearchedBrand) && is_array($keysSearchedCategory))
			$keysSearched = array_merge($keysSearchedCategory, $keysSearchedBrand);
		elseif(!empty($keysSearchedBrand))
			$keysSearched = $keysSearchedBrand;
		elseif(!empty($keysSearchedCategory))
			$keysSearched = $keysSearchedCategory;
			
		$this->tEngine->assign("contenuto_precaricato", $keysSearched);
		
		if(!empty($_SESSION[$this->className]['order_by']))
			$where .= ' ORDER BY '.$_SESSION[$this->className]['order_by'].' '.$_SESSION[$this->className]['order_type'];
		else
			$where .= ' ORDER BY content.data_inserimento_riga DESC';

		//if(empty($_SESSION[$this->className]['result']))
		$BeanContent = new content();
		$List = $BeanContent->dbSearch($this->conn, $where);
		$_SESSION[$this->className]['result'] = $List;
		
		$p = new MyPager($_SESSION[$this->className]['result'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION[$this->className]['key_search']);
		$this->tEngine->assign('keys_searched', $_SESSION[$this->className]['key_searched']);
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function exportExcel()
	{	
		foreach($_SESSION[$this->className]['result'][0] as $key => $val)
			$fieldToDisplay[strtoupper($key)] = $key;
		$this->exportExcelData($_SESSION[$this->className]['result'], $fieldToDisplay, 'lista_content_'.date('d_m_Y'));
	}
}
?>