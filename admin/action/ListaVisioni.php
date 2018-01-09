<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/casa_editrice.php");
include_once(APP_ROOT."/beans/autori.php");
include_once(APP_ROOT."/beans/contenuti.php");
include_once(APP_ROOT."/beans/distributore.php");
include_once(APP_ROOT."/beans/tipo_presa_carico.php");
include_once(APP_ROOT."/beans/in_visione.php");

class ListaVisioni extends DBSmartyAction
{
	function ListaVisioni()
	{
		parent::DBSmartyAction();
	
		if(!empty($_REQUEST['export']))
		{
			$this->exportExcel();
		}
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanInVisione = new in_visione();
			$BeanInVisione->dbDelete($this->conn,array($_REQUEST['id']), true);
			$this->_redirect('?act=ListaVisioni&reset=1');			
		}
				
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION['ListaVisioni']['result'] = null;
				$_SESSION['ListaVisioni']['key_search'] = $_REQUEST['key_search'];
				$where = " AND (contenuti.isbn LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.titolo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.descrizione LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR contenuti.prezzo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR magazzino.documento_carico LIKE '%".$_REQUEST['key_search']."%'";
				
				$where .= " OR in_visione.data_visione LIKE '%".$_REQUEST['key_search']."%'";
				
				$where .= " OR distributore.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR autori.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR casa_editrice.nome LIKE '%".$_REQUEST['key_search']."%')";
			}
			else 
			{
				$_SESSION['ListaVisioni']['key_search'] = null;
				$_SESSION['ListaVisioni']['result'] = null;
				$_SESSION['ListaVisioni']['order_by'] = null;
				$_SESSION['ListaVisioni']['order_type'] = null;
			}			
		}
		else
			$where = '';
		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION['ListaVisioni']['order_by'] = $_REQUEST['order_by'];
			$_SESSION['ListaVisioni']['order_type'] = $_REQUEST['order_type'];
			$_SESSION['ListaVisioni']['result'] = null;
		}			

		if(!empty($_SESSION['ListaVisioni']['order_by']))
			$where .= ' ORDER BY '.$_SESSION['ListaVisioni']['order_by'].' '.$_SESSION['ListaVisioni']['order_type'];
		else
			$where .= ' ORDER BY data_carico DESC';
			
		if(empty($_SESSION['ListaVisioni']['result']))
		{
			$BeanInVisione = new in_visione();
			$List = $BeanInVisione->dbSearch($this->conn, $where);

			$_SESSION['ListaVisioni']['result'] = $List;
		}
		
		$p = new MyPager($_SESSION['ListaVisioni']['result'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION['ListaVisioni']['key_search']);
		
		$this->tEngine->assign('tpl_action', 'ListaVisioni');
		$this->tEngine->display('Index');
	}
	
	function exportExcel()
	{	
		$fieldToDisplay['ISBN'] = 'isbn';
		$fieldToDisplay['TITOLO'] = 'titolo';
		$fieldToDisplay['DESCRIZIONE'] = 'descrizione';
		$fieldToDisplay['PREZZO'] = 'prezzo';
		$fieldToDisplay['QUANTITA'] = 'quantita';
		$fieldToDisplay['DOCUMENTO CARICO'] = 'documento_carico';
		$fieldToDisplay['PERCENTUALE SCONTO'] = 'percentuale_sconto';
		$fieldToDisplay['COPIE OMAGGIO'] = 'copie_omaggio';
		$fieldToDisplay['DATA CARICO'] = 'data_carico';
		$fieldToDisplay['QUANTITA CARICATA'] = 'quantita_caricata';
		$fieldToDisplay['TIPO PRESA CARICO'] = 'presa_carico';
		$fieldToDisplay['DISTRIBUTORE'] = 'distributore';
		$fieldToDisplay['AUTORE'] = 'autore';
		$fieldToDisplay['CASA EDITRICE'] = 'casa_editrice';
		$fieldToDisplay['NOME RAPPRESENTANTE'] = 'nome';
		$fieldToDisplay['COGNOME RAPPRESENTANTE'] = 'cognome';
		$fieldToDisplay['DATA VISIONE'] = 'data_visione';
		$this->exportExcelData($_SESSION['ListaVisioni']['result'], $fieldToDisplay, 'lista_visioni_'.date('d_m_Y'));
	}
}
?>