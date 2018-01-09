<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/casa_editrice.php");
include_once(APP_ROOT."/beans/autori.php");
include_once(APP_ROOT."/beans/contenuti.php");
include_once(APP_ROOT."/beans/distributore.php");
include_once(APP_ROOT."/beans/tipo_presa_carico.php");
include_once(APP_ROOT."/beans/vendite.php");

class ListaAssegnazionePostVendita extends DBSmartyAction
{
	function ListaAssegnazionePostVendita()
	{
		parent::DBSmartyAction();
	
		if(!empty($_REQUEST['export']))
		{
			$this->exportExcel();
		}
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanInVisione = new vendite();
			$BeanInVisione->dbDelete($this->conn,array($_REQUEST['id']), true);
			$this->_redirect('?act=ListaAssegnazionePostVendita&reset=1');
		}
				
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION['ListaAssegnazionePostVendita']['result'] = null;
				$_SESSION['ListaAssegnazionePostVendita']['key_search'] = $_REQUEST['key_search'];
				$where = " AND is_post_assigned = 0 AND (contenuti.isbn LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.titolo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.descrizione LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.prezzo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR magazzino.documento_carico LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR vendite.data_vendita LIKE '%".$_REQUEST['key_search']."%'";				
				$where .= " OR distributore.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR autori.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR casa_editrice.nome LIKE '%".$_REQUEST['key_search']."%')";
			}
			else 
			{
				$_SESSION['ListaAssegnazionePostVendita']['key_search'] = null;
				$_SESSION['ListaAssegnazionePostVendita']['result'] = null;
				$_SESSION['ListaAssegnazionePostVendita']['order_by'] = null;
				$_SESSION['ListaAssegnazionePostVendita']['order_type'] = null;
			}			
		}
		else
			$where = '';

		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION['ListaAssegnazionePostVendita']['order_by'] = $_REQUEST['order_by'];
			$_SESSION['ListaAssegnazionePostVendita']['order_type'] = $_REQUEST['order_type'];
			$_SESSION['ListaAssegnazionePostVendita']['result'] = null;
		}			

		if(!empty($_SESSION['ListaAssegnazionePostVendita']['order_by']))
			$where .= ' ORDER BY '.$_SESSION['ListaAssegnazionePostVendita']['order_by'].' '.$_SESSION['ListaAssegnazionePostVendita']['order_type'];
		else
			$where .= ' ORDER BY data_carico DESC';
			
		//if(empty($_SESSION['ListaAssegnazionePostVendita']['result']))
		if(true)
		{
			$BeanInVisione = new vendite();
			$List = $BeanInVisione->dbSearch($this->conn, $where);

			$_SESSION['ListaAssegnazionePostVendita']['result'] = $List;
		}

		$p = new MyPager($_SESSION['ListaAssegnazionePostVendita']['result'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION['ListaAssegnazionePostVendita']['key_search']);
		
		$this->tEngine->assign('tpl_action', 'ListaAssegnazionePostVendita');
		$this->tEngine->display('Index');
	}
	
	function exportExcel()
	{	
		foreach($_SESSION['ListaContenuti']['result'][0] as $key => $val)
			$fieldToDisplay[strtoupper($key)] = $key;
				$this->exportExcelData($_SESSION['ListaAssegnazionePostVendita']['result'], $fieldToDisplay, 'lista_visioni_'.date('d_m_Y'));
	}
}
?>