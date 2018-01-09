<?php
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/agenti.php");

class ListaUtenti extends DBSmartyAction
{
	var $className;
	
	function ListaUtenti()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);

		if(!empty($_REQUEST['reset']))
			$_SESSION[$this->className] = null;

		if(!empty($_REQUEST['delete']))
		{
			$BeanUtenti = new users();
			$BeanUtenti->dbDelete($this->conn,array($_REQUEST['id']), true);
		}
				
		$BeanAgenti = new agenti();
		$agenti = $BeanAgenti->dbGetAll($this->conn, 'nominativo', 'ASC');
		$this->tEngine->assign('agenti', $agenti);
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['key_search'] = $_REQUEST['key_search'];
				$where = " AND (users_anag.name LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR users_anag.surname LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR users_anag.email LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR users.username LIKE '%".$_REQUEST['key_search']."%')";
			}
			else 
			{
				$_SESSION[$this->className]['key_search'] = null;
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['order_by'] = null;
				$_SESSION[$this->className]['order_type'] = null;
			}			
		}

		if(isset($_REQUEST['order_by']))
		{
			$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
			$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
			$_SESSION[$this->className]['result'] = null;
		}			

		if(!empty($_SESSION[$this->className]['order_by']))
			$where .= ' ORDER BY '.$_SESSION[$this->className]['order_by'].' '.$_SESSION[$this->className]['order_type'];

		if(empty($_SESSION[$this->className]['result']))
		{
			$BeanUtenti = new users();
			$_SESSION[$this->className]['result'] = $BeanUtenti->dbSearch($this->conn, $where);
			if(empty($_SESSION[$this->className]['header_list']))
				$_SESSION[$this->className]['header_list'] = $_SESSION[$this->className]['result'][0];
		}

		$p = new MyPager($_SESSION[$this->className]['result'], $this->rowForPage);
		$links = $p->getLinks();
		$data = $p->getData();

		$this->tEngine->assign('header_list', array($_SESSION[$this->className]['header_list']));
		$this->tEngine->assign('list'	    , $data);
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION[$this->className]['key_search']);
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
}
?>