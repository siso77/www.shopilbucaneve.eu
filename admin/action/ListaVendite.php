<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/vendite.php");

class ListaVendite extends DBSmartyAction
{
	var $className;
	
	function ListaVendite()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
	
		if(!empty($_REQUEST['export']))
		{
			$this->exportExcel();
		}
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanVendite = new vendite();
			$BeanVendite->dbDelete($this->conn,array($_REQUEST['id']), true);
			$this->_redirect('?act=ListaVendite&reset=1');
		}
				
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION['ListaVendite']['result'] = null;
				$_SESSION['ListaVendite']['key_search'] = $_REQUEST['key_search'];
				$where .= " AND (content.name_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.description_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.price_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.price_discounted_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR vendite.data_vendita LIKE '%".$_REQUEST['key_search']."%'";	
				$where .= " OR customer.nome LIKE '%".$_REQUEST['key_search']."%'";	
				$where .= " OR customer.cognome LIKE '%".$_REQUEST['key_search']."%')";	
			}
			else 
			{
				$_SESSION['ListaVendite']['key_search'] = null;
				$_SESSION['ListaVendite']['result'] = null;
				$_SESSION['ListaVendite']['order_by'] = ' vendite.data_vendita ';
				$_SESSION['ListaVendite']['order_type'] = ' DESC ';
			}			
		}
		else
			$where = '';

		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION['ListaVendite']['order_by'] = $_REQUEST['order_by'];
			$_SESSION['ListaVendite']['order_type'] = $_REQUEST['order_type'];
			$_SESSION['ListaVendite']['result'] = null;
		}			

		if(!empty($_SESSION['ListaVendite']['order_by']))
			$where .= ' ORDER BY '.$_SESSION['ListaVendite']['order_by'].' '.$_SESSION['ListaVendite']['order_type'];
		else
			$where .= ' ORDER BY vendite.data_vendita DESC';
			
		$BeanVendite = new vendite();
		$List = $BeanVendite->dbSearchListVendite($this->conn, ' AND vendite.is_active = 1 '.$where);

		$p = new MyPager($List, $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION['ListaVendite']['key_search']);
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', 'ListaVendite');
		$this->tEngine->display('Index');
	}
	
	function exportExcel()
	{	
		foreach($_SESSION[$this->className]['result'][0] as $key => $val)
			$fieldToDisplay[strtoupper($key)] = $key;
		$this->exportExcelData($_SESSION[$this->className]['result'], $fieldToDisplay, $this->className.'_'.date('d_m_Y'));
	}
}
?>