<?php

class AjaxCercaRappresentante extends DBSmartyAction
{
	function AjaxCercaRappresentante()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['reset']))
			unset($_SESSION['AjaxCercaRappresentante']['result']);
	
		if(
			empty($_SESSION['AjaxCercaRappresentante']['result']) ||
			!empty($_REQUEST['cerca_rappresentante_email']) || 
			!empty($_REQUEST['cerca_rappresentante_cognome']) || 
			!empty($_REQUEST['cerca_rappresentante_nome'])
		)
		{
			$tbl_name = $_REQUEST['tbl_name'];
			include_once(APP_ROOT."/beans/$tbl_name.php");
			$Bean = new $tbl_name();
	
			$search = '';
			if(!empty($_REQUEST['cerca_rappresentante_nome']))
				$search .= " nome LIKE '%".$_REQUEST['cerca_rappresentante_nome']."%' AND ";
			if(!empty($_REQUEST['cerca_rappresentante_cognome']))
				$search .= " cognome LIKE '%".$_REQUEST['cerca_rappresentante_cognome']."%' AND ";
			if(!empty($_REQUEST['cerca_rappresentante_email']))
				$search .= " email LIKE '%".$_REQUEST['cerca_rappresentante_email']."%' AND ";
				
			if(!empty($search))
				$search = ' AND '.substr($search, 0, -4);
			$result = $Bean->dbSearch($this->conn, $search);
			$_SESSION['AjaxCercaRappresentante']['result'] = $result;
		}
		$headerField[] = 'NOME';
		$headerField[] = 'COGNOME';
		$headerField[] = 'EMAIL';

		$this->tEngine->assign('headerField', $headerField);
		$this->tEngine->assign('actionType', 'visione');
		
		$this->tEngine->assign('tbl_name', $tbl_name);
		
		$p = new MyPager($_SESSION['AjaxCercaRappresentante']['result'], $this->rowForPage);
		$links = $p->getLinks();

		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		
		echo $this->tEngine->fetch('shared/DivCercaRappresentanteResult');
	}
}
?>