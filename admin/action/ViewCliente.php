<?php
include_once(APP_ROOT."/beans/clienti.php");
include_once(APP_ROOT."/beans/resi.php");
include_once(APP_ROOT."/beans/in_visione.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/contenuti.php");
include_once(APP_ROOT."/beans/emails.php");
include_once(APP_ROOT."/beans/emails_clienti.php");
include_once(APP_ROOT."/beans/vendite.php");
include_once(APP_ROOT."/beans/reso_post_vendita.php");

class ViewCliente extends DBSmartyAction
{
	function ViewCliente()
	{
		parent::DBSmartyAction();

		$BeanClienti = new clienti($this->conn, $_REQUEST['id']);
		$this->getListaInVisione($_REQUEST['id']);
		$this->getListaVendite($_REQUEST['id']);
		$this->getListaResi($_REQUEST['id']);
		$this->getListaResiPostVendite($_REQUEST['id']);
		
		$BeanClientiEmail = new emails_clienti();
		$idsEmail = $BeanClientiEmail->dbGetAllIdEmailByIdCliente($this->conn, $_REQUEST['id']);
		$BeanEmails = new emails();
		$emails = $BeanEmails->dbGetAllByIdsEmail($this->conn, $idsEmail);
		
		$this->tEngine->assign('cliente', $BeanClienti->vars());
		$this->tEngine->assign('emails', $emails);

		$this->tEngine->assign('tpl_action', 'ViewCliente');
		$this->tEngine->display('Index');
		
	}
	
	function getListaResi($id)
	{
		$BeanResi = new resi();
		$ListaResi = $BeanResi->dbGetAllByIdCliente($this->conn, $id, new magazzino(), new contenuti());
		
		$this->getTemplateSettingsByCustomKey('ViewClienteListaResi');

		$p = new MyPager($ListaResi, $this->rowForPage, 'pageIdResi');
		
		$links = $p->getLinks();
		$this->tEngine->assign('ListaResi'	, $p->getData());
		$this->tEngine->assign('tot_items_resi'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page_resi'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page_resi'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage_resi', $this->numViewPage);
	}
	
	function getListaInVisione($id)
	{
		$BeanInVisione = new in_visione();
		$ListaInVisione = $BeanInVisione->dbGetAllByIdCliente($this->conn, $id, new magazzino(), new contenuti());
		
		$this->getTemplateSettingsByCustomKey('ViewClienteListaVisione');

		$p = new MyPager($ListaInVisione, $this->rowForPage, 'pageIdVisioni');
		
		$links = $p->getLinks();
		$this->tEngine->assign('ListaInVisione'	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
	}
	
	function getListaVendite($id)
	{
		$BeanVendite = new vendite();
		$ListaVendite = $BeanVendite->dbGetAllByIdCliente($this->conn, $id, new magazzino(), new contenuti());

		$this->getTemplateSettingsByCustomKey('ViewClienteListaVendite');

		$p = new MyPager($ListaVendite, $this->rowForPage, 'pageIdVenduti');
		
		$links = $p->getLinks();
		$this->tEngine->assign('ListaVendite'		, $p->getData());
		$this->tEngine->assign('tot_items_venduti'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page_venduti'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page_venduti'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage_venduti', $this->numViewPage);
	}	
	
	function getListaResiPostVendite($id)
	{
		$BeanVendite = new reso_post_vendita();
		$ListaVendite = $BeanVendite->dbGetAllByIdCliente($this->conn, $id, new magazzino(), new contenuti());

		$this->getTemplateSettingsByCustomKey('ViewClienteListaResiPostVendite');

		$p = new MyPager($ListaVendite, $this->rowForPage, 'pageIdResiPostVendite');
		
		$links = $p->getLinks();
		$this->tEngine->assign('ListaResiPostVendite'		, $p->getData());
		$this->tEngine->assign('tot_items_resi_post_vendite'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page_resi_post_vendite'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page_resi_post_vendite'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage_resi_post_vendite', $this->numViewPage);
	}	
}
?>