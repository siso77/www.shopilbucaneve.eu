<?php

class AjaxStampaFattura extends DBSmartyAction
{
	function AjaxStampaFattura()
	{
		parent::DBSmartyAction();

		switch ($_REQUEST['action_fatturazione'])
		{
			case 'add_book_to_basket':

				if(!empty($_REQUEST['remove']))
				{
					foreach ($_SESSION['book_in_basket_fattura']['id_contenuto'] as $key => $id)
					{
						if($_REQUEST['id'] == $id)
						{
							unset($_SESSION['book_in_basket_fattura']['id_contenuto'][$key]);
							unset($_SESSION['book_in_basket_fattura']['id_vendita'][$key]);
							
							$tbl_name = $_REQUEST['tbl_name_fatturazione'];
							include_once(APP_ROOT."/beans/$tbl_name.php");
							$Bean = new $tbl_name();
							$result = $Bean->dbSearchForViewCliente($this->conn, ' AND vendite.id = '.$_REQUEST['id_vendita'].' AND contenuti.id = '.$id);
							echo('Hai rimosso il libro: "'.$result[0]['titolo'].'"');
						}
					}
					exit();
				}
				else
				{
					if(!in_array($_REQUEST['id'], $_SESSION['book_in_basket_fattura']['id_contenuto']))
					{
						$_SESSION['book_in_basket_fattura']['id_contenuto'][] = $_REQUEST['id'];
						$_SESSION['book_in_basket_fattura']['id_vendita'][] = $_REQUEST['id_vendita'];
						$tbl_name = $_REQUEST['tbl_name_fatturazione'];
						include_once(APP_ROOT."/beans/$tbl_name.php");
						$Bean = new $tbl_name();
						$result = $Bean->dbSearchForViewCliente($this->conn, ' AND vendite.id = '.$_REQUEST['id_vendita'].' AND contenuti.id = '.$_REQUEST['id']);
						echo('Hai aggiunto il libro: "'.$result[0]['titolo'].'"');
					}
					else
						echo('Il libro era già presente');
					exit();
				}
			break;
			case 'cerca_libro':				
				if(empty($_REQUEST['pageID']))
				{
					$tbl_name = $_REQUEST['tbl_name_fatturazione'];
					include_once(APP_ROOT."/beans/$tbl_name.php");
					$Bean = new $tbl_name();
    
					$where = " vendite.is_invoiced IS NULL AND ";
					if(!empty($_REQUEST['fatt_isbn']))
						$where = " contenuti.isbn LIKE '%".$_REQUEST['fatt_isbn']."%' AND ";
					if(!empty($_REQUEST['fatt_titolo']))
						$where .= " contenuti.titolo LIKE '%".$_REQUEST['fatt_titolo']."%' AND ";
					if(!empty($_REQUEST['fatt_cellulare']))
						$where .= " contenuti.descrizione LIKE '%".$_REQUEST['key_search']."%' AND ";
					if(!empty($_REQUEST['fatt_prezzo']))
						$where .= " contenuti.prezzo LIKE '%".$_REQUEST['fatt_prezzo']."%' AND ";
					if(!empty($_REQUEST['fatt_documento_carico']))
						$where .= " magazzino.documento_carico LIKE '%".$_REQUEST['fatt_documento_carico']."%' AND ";

					if(!empty($where))
						$where = " AND ".substr($where, 0, -4);
					$result = $Bean->dbSearch($this->conn, $where.' ORDER BY quantita DESC');
					
					$_SESSION['AjaxStampaFattura']['result'] = $result;
				}
				$headerField[] = 'ISBN';
				$headerField[] = 'TITOLO';
				$headerField[] = 'QUANTITA';
				$headerField[] = '';
				$headerField[] = 'DATA VENDITA';
				$headerField[] = 'SELEZIONA';
				
				$excludedField[] = 'id_vendita';
				$excludedField[] = 'documento_carico';
				$excludedField[] = 'presa_carico';
				$excludedField[] = 'id_contenuto';
				$excludedField[] = 'descrizione';
				$excludedField[] = 'id';
				$excludedField[] = 'id_magazzino';
				//$excludedField[] = 'quantita';
				$excludedField[] = 'percentuale_sconto';
				$excludedField[] = 'copie_omaggio';
				$excludedField[] = 'data_carico';
				$excludedField[] = 'quantita_caricata';
				$excludedField[] = 'data_inserimento_riga';
				$excludedField[] = 'data_modifica_riga';
				$excludedField[] = 'id_tipo_presa_carico';
				$excludedField[] = 'nome';
				$excludedField[] = 'prezzo';
				$excludedField[] = 'id_distributore';
				$excludedField[] = 'distributore';
				$excludedField[] = 'autore';
				$excludedField[] = 'casa_editrice';
				$excludedField[] = 'id_contenuto_tipo';
				$excludedField[] = 'contenuto_tipo';
				
				$this->tEngine->assign('pagingAction', 'cerca_libro');				
			break;
		}

		$this->tEngine->assign('excludedField', $excludedField);
		$this->tEngine->assign('headerField', $headerField);
		$this->tEngine->assign('actionType', 'visione');
		
		$this->tEngine->assign('tbl_name', $tbl_name);

		$p = new MyPager($_SESSION['AjaxStampaFattura']['result'], $this->rowForPage);
		$links = $p->getLinks();

		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);

		echo $this->tEngine->fetch('shared/DivCercaResultFatturazione');
	}
}
?>