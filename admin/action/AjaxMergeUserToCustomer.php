<?php
include_once(APP_ROOT."/beans/customer.php");
include_once(APP_ROOT."/beans/users.php");

class AjaxMergeUserToCustomer extends DBSmartyAction
{
	var $className;
	
	function AjaxMergeUserToCustomer()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);

		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			if(!empty($_REQUEST['merge']))
			{
				$BeanUsers = new users($this->conn, $_REQUEST['id_user']);
				$BeanUsers->setId_customer($_REQUEST['id_customer']);
				$BeanUsers->dbStore($this->conn);
				echo '<script type="text/javascript">$.fancybox.hide();</script>';
				exit();
			}
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['key_search'] = $_REQUEST['key_search'];
				$where = " AND (customer.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.cognome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.indirizzo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.cellulare LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.ragione_sociale LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.email LIKE '%".$_REQUEST['key_search']."%')";
			}
			else 
			{
				$_SESSION[$this->className]['key_search'] = null;
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['order_by'] = null;
				$_SESSION[$this->className]['order_type'] = null;
			}			
		}
		else
			$where = '';
			
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
			$BeanCustomer = new customer();
			$_SESSION[$this->className]['result'] = $BeanCustomer->dbSearch($this->conn, $where);
			$_SESSION[$this->className]['header_list'][0] = $_SESSION[$this->className]['result'][0];
		}
		
		$this->tEngine->assign('header_list', $_SESSION[$this->className]['header_list']);

		$p = new MyPager($_SESSION[$this->className]['result'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION[$this->className]['key_search']);
		
		$BeanUsers = new users($this->conn, $_REQUEST['id_user']);
		$BeanCustomer = new customer($this->conn, $BeanUsers->id_customer);		
		$this->tEngine->assign('current_customer', $BeanCustomer->vars());
		
		$this->tEngine->assign('id_user', $_REQUEST['id_user']);
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->display($this->className);
	}
}
?>