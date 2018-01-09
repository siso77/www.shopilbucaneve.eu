<?php
include_once(APP_ROOT."/beans/ecm_ordini.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_fornitori.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_forn_de.php");
include_once(APP_ROOT."/beans/giacenze_fornitori.php");
include_once(APP_ROOT."/beans/giacenze_forn_gasa.php");

class Orders extends DBSmartyAction
{
	var $className;
	
	function Orders()
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
		$data = $BeanEcmOrdini->dbGetAll($this->conn, $_SESSION[$this->className]['order_by'], $_SESSION[$this->className]['order_type'], ' AND spedito = 0', new users(), new users_anag(), new ecm_ordini_magazzino());

		foreach($data as $key => $value)
		{
			$BeanEcmOrdiniFornNl = new ecm_ordini_magazzino_fornitori();
			$data[$key]['ordine_magazzino_den_dekker'] = $BeanEcmOrdiniFornNl->dbGetAllByIdOrdine($this->conn, $value['id']);			
			foreach ($data[$key]['ordine_magazzino_den_dekker'] as $k => $val)
			{
				$BeanGiacenzeFornitori = new giacenze_fornitori($this->conn, $val['id_magazzino']);
				$data[$key]['ordine_magazzino_den_dekker'][$k]['giacenza'] = $BeanGiacenzeFornitori->vars();
			}
			$BeanEcmOrdiniFornDe = new ecm_ordini_magazzino_forn_de();
			$data[$key]['ordine_magazzino_gasa'] = $BeanEcmOrdiniFornDe->dbGetAllByIdOrdine($this->conn, $value['id']);
			foreach ($data[$key]['ordine_magazzino_gasa'] as $k => $val)
			{
				$BeanGiacenzeFornitoriGasa = new giacenze_forn_gasa($this->conn, $val['id_magazzino']);
				$data[$key]['ordine_magazzino_gasa'][$k]['giacenza'] = $BeanGiacenzeFornitoriGasa->vars();
			}
		}

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