<?php

class AjaxGetCaricoMagazzinoByIsbn extends DBSmartyAction
{
	function AjaxGetCaricoMagazzinoByIsbn()
	{
		parent::DBSmartyAction();

		$where = " AND contenuti.isbn LIKE '%".$_REQUEST['isbn']."%'";
		$where .= ' ORDER BY data_carico DESC';
		
		include_once(APP_ROOT."/beans/carico_magazzino.php");
		$BeanCaricoMagazzino = new carico_magazzino();
		$List = $BeanCaricoMagazzino->dbSearch($this->conn, $where);

		$this->tEngine->assign("pagingAction", "AjaxGetCaricoMagazzinoByIsbn");
		$this->tEngine->assign("div_result", $_REQUEST['div_id']);

		$p = new MyPager($List, $this->rowForPage);
		$links = $p->getLinks();

		if(!empty($_REQUEST['edit']))
		{
			$headerField['isbn'] = 'ISBN';
			$headerField['titolo'] = 'TITOLO';
			$headerField['casa_editrice'] = 'EDITORE';
			$headerField['autore'] = 'AUTORE';
			$headerField['documento_carico'] = 'DOC. CARICO';
			$headerField['data_carico'] = 'DATA CARICO';
			$headerField['quantita'] = 'QUANTITA\'';
			$headerField['quantita_caricata'] = 'QUANTITA\' CARICATA';
			//$headerField['prezzo'] = 'PREZZO';
			$headerField['distributore'] = 'FORNITORE';
			$headerField['copie_omaggio'] = 'COPIE OMAGGIO';
			$headerField['percentuale_sconto'] = 'PERCENTUALE SCONTO';
			$headerField['action'] = 'AZIONI';
		}
		else 
		{
			$headerField['isbn'] = 'ISBN';
			$headerField['titolo'] = 'TITOLO';
			$headerField['casa_editrice'] = 'EDITORE';
			$headerField['autore'] = 'AUTORE';
			$headerField['documento_carico'] = 'DOC. CARICO';
			$headerField['data_carico'] = 'DATA CARICO';
			$headerField['quantita'] = 'QUANTITA\'';
			$headerField['quantita_caricata'] = 'QUANTITA\' CARICATA';
			$headerField['prezzo'] = 'PREZZO';
			$headerField['distributore'] = 'FORNITORE';
			$headerField['copie_omaggio'] = 'COPIE OMAGGIO';
			$headerField['percentuale_sconto'] = 'PERCENTUALE SCONTO';
		}
		$this->tEngine->assign("headerField"	    , $headerField);
		
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
//_dump($headerField);
//exit();

		echo $this->tEngine->fetch('shared/DivDetailCaricoMagazzino');
	}
}
?>