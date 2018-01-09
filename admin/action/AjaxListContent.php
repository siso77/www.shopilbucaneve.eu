<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/category.php");

class AjaxListContent extends DBSmartyAction
{
	var $className;
	
	function AjaxListContent()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		if($_REQUEST['reset'])
		{
			$_SESSION[$this->className]['key_search'] = null;
			$_SESSION[$this->className]['order_by'] = null;
			$_SESSION[$this->className]['order_type'] = null;
		}
		
		$BeanCategory = new category();
		$this->tEngine->assign('cmb_category', $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC'));

		if(!empty($_REQUEST['order_by']))
			$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
		if(!empty($_REQUEST['order_type']))
			$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
		if(!empty($_REQUEST['key_search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			$_SESSION[$this->className]['key_search'] = $_REQUEST['key_search'];
		if(!empty($_REQUEST['parent_id']))
			$_SESSION[$this->className]['parent_id'] = $_REQUEST['parent_id'];
			
		if(!empty($_SESSION[$this->className]['key_search']) && $_SESSION[$this->className]['key_search'] != 'Cerca la parola chiave')
		{
			$where = " AND (magazzino.bar_code LIKE '%".$_SESSION[$this->className]['key_search']."%'";
			$where .= " OR content.name_it LIKE '%".$_SESSION[$this->className]['key_search']."%'";
			$where .= " OR content.description_it LIKE '%".$_SESSION[$this->className]['key_search']."%'";
			$where .= " OR content.price_it LIKE '%".$_SESSION[$this->className]['key_search']."%'";
			$where .= " OR brands.name LIKE '%".$_SESSION[$this->className]['key_search']."%'";
			$where .= " OR category.name LIKE '%".$_SESSION[$this->className]['key_search']."%')";
			$where = " AND (content.name_it LIKE '%".$_SESSION[$this->className]['key_search']."%'";
			$where .= " OR content.description_it LIKE '%".$_SESSION[$this->className]['key_search']."%')";
			$this->tEngine->assign("key_search", $_SESSION[$this->className]['key_search']);
		}
		else
			$where = ' AND is_in_evidence = 0';
		
		if(!empty($_SESSION[$this->className]['order_by']))
			$where .= ' ORDER BY '.$_SESSION[$this->className]['order_by'];
		if(!empty($_SESSION[$this->className]['order_type']))
			$where .= ' '.$_SESSION[$this->className]['order_type'];

		$BeanContent = new content();
		$List = $BeanContent->dbSearch($this->conn, ' AND category.parent_id = '.$_SESSION[$this->className]['parent_id'].$where, new magazzino());
		
		$p = new MyPager($List, 10);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', 5);
		$this->tEngine->assign('key_search', $_SESSION[$this->className]['key_search']);
		$this->tEngine->assign('keys_searched', $_SESSION[$this->className]['key_searched']);
		$this->tEngine->assign('action_class_name', 'AjaxListContent');
		$this->tEngine->display('ListaContenutiShowcase');
		
//_dump($List);
//exit();
	}
}
?>