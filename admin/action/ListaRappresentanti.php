<?php
include_once(APP_ROOT."/beans/in_visione.php");
include_once(APP_ROOT."/beans/vendite.php");
include_once(APP_ROOT."/beans/rappresentante.php");
include_once(APP_ROOT."/beans/magazzino_rappresemtanti.php");

class ListaRappresentanti extends DBSmartyAction
{
	function ListaRappresentanti()
	{
		parent::DBSmartyAction();
	
		if(!empty($_REQUEST['export']))
		{
			$rappresentante = $_SESSION['ListaRappresentanti']['result'][0]['cognome'];
			if($_REQUEST['action'] == 'in_visione')
				$this->exportExcel($_SESSION['ListaRappresentanti']['result'][0]['in_visione'], $rappresentante);
			else
				$this->exportExcel($_SESSION['ListaRappresentanti']['result'][0]['vendite'], $rappresentante);
		}
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanRappresentante = new rappresentante();
			$BeanRappresentante->dbDelete($this->conn,$_REQUEST['id'], true);
		}
				
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reload_search']))
		{
			if(!empty($_REQUEST['nome']))
				$nome = $_REQUEST['nome']; 
			if(!empty($_REQUEST['cognome']))
				$cognome = $_REQUEST['cognome']; 

			if(!empty($cognome) || !empty($nome))
			{
				$_SESSION['ListaRappresentanti']['result'] = null;
				$_SESSION['ListaRappresentanti']['key_search']['nome'] = $nome;
				$_SESSION['ListaRappresentanti']['key_search']['cognome'] = $cognome;
				
				$where = "";
				if(!empty($nome))
					$where .= " AND rappresentante.nome LIKE '%".$_REQUEST['nome']."%'";
				if(!empty($cognome))
					$where .= " AND rappresentante.cognome LIKE '%".$_REQUEST['cognome']."%'";
			}
			else 
			{
				$_SESSION['ListaRappresentanti']['key_search']['nome'] = null;
				$_SESSION['ListaRappresentanti']['key_search']['cognome'] = null;
				$_SESSION['ListaRappresentanti']['result'] = null;
				$_SESSION['ListaRappresentanti']['order_by'] = null;
				$_SESSION['ListaRappresentanti']['order_type'] = null;
			}			
		}
		else
			$where = '';
			
		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION['ListaRappresentanti']['order_by'] = $_REQUEST['order_by'];
			$_SESSION['ListaRappresentanti']['order_type'] = $_REQUEST['order_type'];
			$_SESSION['ListaRappresentanti']['result'] = null;
		}			
		
		if(!empty($_SESSION['ListaRappresentanti']['order_by']))
			$where .= ' ORDER BY '.$_SESSION['ListaRappresentanti']['order_by'].' '.$_SESSION['ListaRappresentanti']['order_type'];
		if(!empty($where))
		{
			$BeanRappresentante = new rappresentante();
			$List = $BeanRappresentante->dbSearch($this->conn, $where);
			
			$BeanInVisione = new in_visione();
			$BeanVendite = new vendite();
			$BeanMagazzinoRappresemtanti = new magazzino_rappresemtanti();
			
			foreach ($List as $k => $value)
			{
				$List[$k]['in_visione'] = $BeanInVisione->dbSearch($this->conn, ' AND in_visione.id_rappresentante = '.$value['id']);
				$List[$k]['vendite'] = $BeanVendite->dbGetVendite($this->conn, ' AND vendite.id_rappresentante = '.$value['id']);
				$List[$k]['assegnati'] = $BeanMagazzinoRappresemtanti->dbSearch($this->conn, ' AND magazzino_rappresemtanti.id_rappresentante = '.$value['id']);
			}				
			$_SESSION['ListaRappresentanti']['result'] = $List;
		}
		elseif(empty($_REQUEST['pageIdVisioni']))
			$_SESSION['ListaRappresentanti']['result'] = null;
		/*
		 * PAGINAZIONE PER LA LISTA IN VISIONE 
		 * */
		if(!empty($_SESSION['ListaRappresentanti']['result'][0]['in_visione']))
		{
			$this->getTemplateSettingsByCustomKey('ViewRappresentanteListaVisione');
			$p = new MyPager($_SESSION['ListaRappresentanti']['result'][0]['in_visione'], $this->rowForPage, 'pageIdVisioni');
	
			$links = $p->getLinks();
			$this->tEngine->assign("list_in_visione", $p->getData());
			$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
			$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
			$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
			$this->tEngine->assign('numViewPage', $this->numViewPage);
		}
		/*
		 * PAGINAZIONE PER LA LISTA IN VISIONE 
		 * */
		
		/*
		 * PAGINAZIONE PER LA LISTA VENDITE
		 * */
		if(!empty($_SESSION['ListaRappresentanti']['result'][0]['vendite']))
		{
			$this->getTemplateSettingsByCustomKey('ViewRappresentanteListaVendite');
			$p = new MyPager($_SESSION['ListaRappresentanti']['result'][0]['vendite'], $this->rowForPage, 'pageIdVendite');
	
			$links = $p->getLinks();
			$this->tEngine->assign("list_vendite", $p->getData());
			$this->tEngine->assign('tot_items_vendite'  , $p->pager->_totalItems);
			$this->tEngine->assign('curr_page_vendite'  , $p->pager->_currentPage);
			$this->tEngine->assign('last_page_vendite'  , $p->pager->_totalPages);
			$this->tEngine->assign('numViewPage_vendite', $this->numViewPage);
		}
		/*
		 * PAGINAZIONE PER LA LISTA VENDITE
		 * */
		
		
		/*
		 * PAGINAZIONE PER LA LISTA ASSEGNATI
		 * */
		if(!empty($_SESSION['ListaRappresentanti']['result'][0]['assegnati']))
		{			
			$this->getTemplateSettingsByCustomKey('ViewRappresentanteListaAssegnati');
			$p = new MyPager($_SESSION['ListaRappresentanti']['result'][0]['assegnati'], $this->rowForPage, 'pageIdAssegnati');
	
			$links = $p->getLinks();
			$this->tEngine->assign("list_assegnati", $p->getData());
			$this->tEngine->assign('tot_items_assegnati'  , $p->pager->_totalItems);
			$this->tEngine->assign('curr_page_assegnati'  , $p->pager->_currentPage);
			$this->tEngine->assign('last_page_assegnati'  , $p->pager->_totalPages);
			$this->tEngine->assign('numViewPage_assegnati', $this->numViewPage);
		}
		/*
		 * PAGINAZIONE PER LA LISTA ASSEGNATI
		 * */
		
		
		
		$this->tEngine->assign('list', $_SESSION['ListaRappresentanti']['result']);
		$this->tEngine->assign('key_search', $_SESSION['ListaRappresentanti']['key_search']);
		
		$this->tEngine->assign('tpl_action', 'ListaRappresentanti');
		$this->tEngine->display('Index');
	}
	
	function exportExcel($data, $rappresentante)
	{	
		foreach($_SESSION[$this->className]['result'][0] as $key => $val)
			$fieldToDisplay[strtoupper($key)] = $key;
		$this->exportExcelData($_SESSION[$this->className]['result'], $fieldToDisplay, $this->className.'_'.date('d_m_Y'));
	}
}
?>
