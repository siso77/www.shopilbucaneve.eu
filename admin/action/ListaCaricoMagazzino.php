<?php
include_once(APP_ROOT."/beans/carico_magazzino.php");
include_once(APP_ROOT."/beans/casa_editrice.php");
include_once(APP_ROOT."/beans/autori.php");
include_once(APP_ROOT."/beans/contenuti.php");
include_once(APP_ROOT."/beans/distributore.php");
include_once(APP_ROOT."/beans/tipo_presa_carico.php");

class ListaCaricoMagazzino extends DBSmartyAction
{
	function ListaCaricoMagazzino()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['export']))
			$this->exportExcel();
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanMagazzino = new carico_magazzino();
			$BeanMagazzino->dbDelete($this->conn,$_REQUEST['id'], true);
			$this->_redirect('act=ListaCaricoMagazzino&reset=true');
		}
				
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION['ListaCaricoMagazzino']['result'] = null;
				$_SESSION['ListaCaricoMagazzino']['key_search'] = $_REQUEST['key_search'];
				$where = " AND (contenuti.isbn LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.titolo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.descrizione LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.prezzo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR carico_magazzino.documento_carico LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR distributore.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR autori.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR casa_editrice.nome LIKE '%".$_REQUEST['key_search']."%')";
			}
			else 
			{
				$_SESSION['ListaCaricoMagazzino']['key_search'] = null;
				$_SESSION['ListaCaricoMagazzino']['result'] = null;
				$_SESSION['ListaCaricoMagazzino']['order_by'] = null;
				$_SESSION['ListaCaricoMagazzino']['order_type'] = null;
			}			
		}
		else
			$where = '';

		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION['ListaCaricoMagazzino']['order_by'] = $_REQUEST['order_by'];
			$_SESSION['ListaCaricoMagazzino']['order_type'] = $_REQUEST['order_type'];
			$_SESSION['ListaCaricoMagazzino']['result'] = null;
		}			

		if(!empty($_SESSION['ListaCaricoMagazzino']['order_by']))
			$where .= ' ORDER BY '.$_SESSION['ListaCaricoMagazzino']['order_by'].' '.$_SESSION['ListaCaricoMagazzino']['order_type'];
		else
			$where .= ' ORDER BY data_carico DESC';

		$BeanMagazzino = new carico_magazzino();
		$List = $BeanMagazzino->dbSearch($this->conn, ' AND carico_magazzino.quantita > 0 '.$where);
		
		$_SESSION['ListaCaricoMagazzino']['result'] = $List;

		$p = new MyPager($_SESSION['ListaCaricoMagazzino']['result'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION['ListaCaricoMagazzino']['key_search']);
		
		$this->tEngine->assign('tpl_action', 'ListaCaricoMagazzino');
		$this->tEngine->display('Index');
	}
	
	function exportExcel()
	{	
		foreach($_SESSION['ListaCaricoMagazzino']['result'][0] as $key => $val)
			$fieldToDisplay[strtoupper($key)] = $key;
		$this->exportExcelData($_SESSION['ListaCaricoMagazzino']['result'], $fieldToDisplay, 'lista_magazzino_'.date('d_m_Y'));
	}
}
?>