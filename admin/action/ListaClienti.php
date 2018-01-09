<?php
include_once(APP_ROOT."/beans/customer.php");
include_once(APP_ROOT."/beans/users.php");

class ListaClienti extends DBSmartyAction
{
	var $className;
	
	function ListaClienti()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
		
		if(!empty($_REQUEST['get_ricarichi']) || !empty($_REQUEST['export']))
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['key_search'] = $_REQUEST['key_search'];
				$where = " AND (customer.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.cognome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.indirizzo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.cellulare LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.ragione_sociale LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.p_iva LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.email LIKE '%".$_REQUEST['key_search']."%')";
			}
			else
			{
				$_SESSION[$this->className]['key_search'] = null;
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['order_by'] = null;
				$_SESSION[$this->className]['order_type'] = null;
			}
				
			$BeanCustomer = new customer();
			$query = "SELECT users.id, 
						users.id_type, 
						users.id_anag, 
						users.id_customer, 
						users.sconto_fornitori_nl, 
						users.sconto_fornitori_de, 
						users.username, 
						users.`password`, 
						users.last_access, 
						users.is_newsletter_subscribed, 
						users.is_t_c_accepted, 
						users.is_active, 
						customer.customer_code, 
						customer.nome, 
						customer.cognome, 
						customer.codice_fiscale, 
						customer.ragione_sociale, 
						customer.p_iva, 
						customer.indirizzo, 
						customer.provincia, 
						customer.stato, 
						customer.citta, 
						customer.cap, 
						customer.cellulare, 
						customer.fisso, 
						customer.fax, 
						customer.email,
						customer.listino
			FROM users INNER JOIN customer ON users.id_customer = customer.id ".$where;

			$data = $BeanCustomer->dbFree($this->conn, $query);

			if(!empty($_REQUEST['export']))
				$this->exportExcel($data);
				
			$p = new MyPager($data, $this->rowForPage);
			$links = $p->getLinks();
			$this->tEngine->assign("list"	    , $p->getData());
			$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
			$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
			$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
			$this->tEngine->assign('numViewPage', $this->numViewPage);
			$this->tEngine->assign('key_search', $_SESSION[$this->className]['key_search']);
			
			$this->tEngine->assign('get_ricarichi', true);
			$this->tEngine->assign('action_class_name', $this->className);
			$this->tEngine->assign('tpl_action', 'ListaRicarichiClienti');
			$this->tEngine->display('Index');
			exit();
		}
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanCustomer = new customer();
			$BeanCustomer->dbDelete($this->conn,array($_REQUEST['id']), true);
			$this->_redirect('?act=ListaClienti&reset=1');
		}
				
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['key_search'] = $_REQUEST['key_search'];
				$where = " AND (customer.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.cognome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.indirizzo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.cellulare LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.ragione_sociale LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR customer.p_iva LIKE '%".$_REQUEST['key_search']."%'";
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

		$BeanCustomer = new customer();
		$data = $BeanCustomer->dbSearch($this->conn, $where);
		
		$HeaderList[0] = $data[0];
		$this->tEngine->assign('header_list', $HeaderList);

		$p = new MyPager($data, $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION[$this->className]['key_search']);
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function exportExcel($data)
	{
		$fieldToDisplay['customer_code'] = 'customer_code';
		$fieldToDisplay['ragione_sociale'] = 'ragione_sociale';
		$fieldToDisplay['p_iva'] = 'p_iva';
		$fieldToDisplay['indirizzo'] = 'indirizzo';
		$fieldToDisplay['provincia'] = 'provincia';
		$fieldToDisplay['citta'] = 'citta';
		$fieldToDisplay['cap'] = 'cap';
		$fieldToDisplay['email'] = 'email';
		$fieldToDisplay['listino'] = 'listino';
		$fieldToDisplay['ricarico_nl'] = 'ricarico_nl';
		$fieldToDisplay['ricarico_de'] = 'ricarico_de';

		foreach($data as $key => $val)
		{
			$BeanUser = new users($this->conn, $val['id']);
			$data[$key]['ricarico_nl'] = $BeanUser->sconto_fornitori_nl;
			$data[$key]['ricarico_de'] = $BeanUser->sconto_fornitori_de;
		}
		$this->exportExcelData($data, $fieldToDisplay, 'lista_clienti_'.date('d_m_Y'));
	}	
}
?>