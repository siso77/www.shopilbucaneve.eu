<?php
include_once(APP_ROOT."/beans/resi.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/contenuti.php");

class ListaResi extends DBSmartyAction
{
	function ListaResi()
	{
		parent::DBSmartyAction();
		
		if(!empty($_REQUEST['export']))
		{
			$this->exportExcel();
		}
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanInVisione = new resi();
			$BeanInVisione->dbDelete($this->conn,array($_REQUEST['id']), true);
			$this->_redirect('?act=ListaResi&reset=1');
		}
				
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION['ListaResi']['result'] = null;
				$_SESSION['ListaResi']['key_search'] = $_REQUEST['key_search'];
				$where = " AND (contenuti.isbn LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.titolo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.descrizione LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.prezzo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR distributore.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR autori.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR casa_editrice.nome LIKE '%".$_REQUEST['key_search']."%')";
			}
			else 
			{
				$_SESSION['ListaResi']['key_search'] = null;
				$_SESSION['ListaResi']['result'] = null;
				$_SESSION['ListaResi']['order_by'] = null;
				$_SESSION['ListaResi']['order_type'] = null;
			}			
		}
		else
			$where = '';

		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION['ListaResi']['order_by'] = $_REQUEST['order_by'];
			$_SESSION['ListaResi']['order_type'] = $_REQUEST['order_type'];
			$_SESSION['ListaResi']['result'] = null;
		}			

		if(!empty($_SESSION['ListaResi']['order_by']))
			$where .= ' ORDER BY '.$_SESSION['ListaResi']['order_by'].' '.$_SESSION['ListaResi']['order_type'];
		else
			$where .= ' ORDER BY data_reso DESC';

		if(empty($_SESSION['ListaResi']['result']))
		{
			$BeanInVisione = new resi();
			$List = $BeanInVisione->dbSearch($this->conn, $where);
			$_SESSION['ListaResi']['result'] = $List;
		}
		
		$p = new MyPager($_SESSION['ListaResi']['result'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION['ListaResi']['key_search']);
		
		$this->tEngine->assign('tpl_action', 'ListaResi');
		$this->tEngine->display('Index');
		
	}
	
	function exportExcel()
	{	
//_dump($_SESSION['ListaResi']['result']);
//exit();		
		$fieldToDisplay['ISBN'] = 'isbn';
		$fieldToDisplay['TITOLO'] = 'titolo';
//		$fieldToDisplay['DESCRIZIONE'] = 'descrizione';
		$fieldToDisplay['TIPO CONTENUTO'] = 'contenuto_tipo';
		$fieldToDisplay['PREZZO'] = 'prezzo';
		$fieldToDisplay['QUANTITA'] = 'quantita';
		$fieldToDisplay['DISTRIBUTORE'] = 'distributore';
		$fieldToDisplay['AUTORE'] = 'autore';
		$fieldToDisplay['CASA EDITRICE'] = 'casa_editrice';
		$fieldToDisplay['NOME CLIENTE'] = 'nome';
		$fieldToDisplay['COGNOME CLIENTE'] = 'cognome';
		$fieldToDisplay['NOME RAPPRESENTANTE'] = 'rappresentante_nome';
		$fieldToDisplay['COGNOME RAPPRESENTANTE'] = 'rappresentante_cognome';
		$fieldToDisplay['DATA RESO'] = 'data_reso';
		$this->exportExcelData($_SESSION['ListaResi']['result'], $fieldToDisplay, 'lista_resi_'.date('d_m_Y'));
	}
}
?>