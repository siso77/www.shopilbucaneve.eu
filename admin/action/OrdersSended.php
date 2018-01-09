<?php
include_once(APP_ROOT."/beans/ecm_ordini.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");

class OrdersSended extends DBSmartyAction
{
	var $className;
	
	function OrdersSended()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
		
		if(!empty($_REQUEST['export']))
			$this->exportExcel();
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanEcmOrdini = new ecm_ordini();
			$BeanEcmOrdini->dbDelete($this->conn,array($_REQUEST['id']), true);
			$this->_redirect('?act=Orders&reset=true');
		}
					
		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
			$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
		}
		
		$BeanEcmOrdini = new ecm_ordini();
		$data = $BeanEcmOrdini->dbGetAll($this->conn, $_SESSION[$this->className]['order_by'], $_SESSION[$this->className]['order_type'], ' AND spedito = 1 ', new users(), new users_anag());
//_dump($data);
//exit();
		$p = new MyPager($data, $this->rowForPage);
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