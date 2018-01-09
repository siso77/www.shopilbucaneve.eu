<?php

class AjaxResoRappresentante extends DBSmartyAction
{
	function AjaxResoRappresentante()
	{
		parent::DBSmartyAction();

		switch ($_REQUEST['action'])
		{
			case 'add_book_to_basket':

				if(!empty($_REQUEST['remove']))
				{
					foreach ($_SESSION['book_in_basket'] as $key => $id)
					{
						if($_REQUEST['id'] == $id)
						{
							unset($_SESSION['book_in_basket'][$key]);
							
							$tbl_name = $_REQUEST['tbl_name'];
							include_once(APP_ROOT."/beans/$tbl_name.php");
							$Bean = new $tbl_name();
							$result = $Bean->dbSearch($this->conn, ' AND magazzino.id = '.$id);
							echo('Hai rimosso il libro: "'.$result[0]['titolo'].'"');
							exit();
						}
					}
				}
				else
				{
					$_SESSION['book_in_basket'][] = $_REQUEST['id'];
					$tbl_name = $_REQUEST['tbl_name'];
					include_once(APP_ROOT."/beans/$tbl_name.php");
					$Bean = new $tbl_name();
					$result = $Bean->dbSearch($this->conn, ' AND magazzino.id = '.$_REQUEST['id']);
					echo('Hai aggiunto il libro: "'.$result[0]['titolo'].'"');
					exit();
				}
				
			break;
			case 'cerca_libro':
				if(empty($_REQUEST['pageID']))
				{
					$tbl_name = $_REQUEST['tbl_name'];
					include_once(APP_ROOT."/beans/$tbl_name.php");
					$Bean = new $tbl_name();
					
					$where = "";
					if(!empty($_REQUEST['isbn']))
						$where = " contenuti.isbn LIKE '%".$_REQUEST['isbn']."%' AND ";
					if(!empty($_REQUEST['titolo']))
						$where .= " contenuti.titolo LIKE '%".$_REQUEST['titolo']."%' AND ";
					if(!empty($_REQUEST['cellulare']))
						$where .= " contenuti.descrizione LIKE '%".$_REQUEST['key_search']."%' AND ";
					if(!empty($_REQUEST['prezzo']))
						$where .= " contenuti.prezzo LIKE '%".$_REQUEST['prezzo']."%' AND ";
					if(!empty($_REQUEST['documento_carico']))
						$where .= " $tbl_name.documento_carico LIKE '%".$_REQUEST['documento_carico']."%' AND ";
					if(!empty($_REQUEST['id']))
						$where .= " $tbl_name.id_rappresentante = '".$_REQUEST['id']."' AND ";
						
					if(!empty($where))
						$where = " AND ".substr($where, 0, -4);
						
					$result = $Bean->dbSearch($this->conn, $where.' ORDER BY quantita DESC');
					
					$_SESSION['AjaxPortaVisione']['result'] = $result;
				}

				$headerField[] = 'ISBN';
				$headerField[] = 'TITOLO';
				$headerField[] = 'DOCUMENTO CARICO';
				$headerField[] = 'QUANTITA';
//				$headerField[] = '';
				$headerField[] = 'DATA CARICO';
				$headerField[] = 'SELEZIONA';
				
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
//				$excludedField[] = 'data_inserimento_riga';
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
				$excludedField[] = 'documento_carico_rapp';
				$excludedField[] = 'documento_scarico_rapp';
				
				$this->tEngine->assign('pagingAction', 'cerca_libro');				
			break;
			case 'cerca_cliente':

				include_once(APP_ROOT."/beans/referers.php");
				include_once(APP_ROOT."/beans/referers_clienti.php");
				
				if(empty($_REQUEST['pageID']))
				{
					$tbl_name = $_REQUEST['tbl_name'];
					include_once(APP_ROOT."/beans/$tbl_name.php");
					$Bean = new $tbl_name();
					
					$where = "";
					if(!empty($_REQUEST['nome']))
						$where .= "clienti.nome LIKE '%".$_REQUEST['nome']."%' AND ";
					if(!empty($_REQUEST['cognome']))
						$where .= " clienti.cognome LIKE '%".$_REQUEST['cognome']."%' AND ";
					if(!empty($_REQUEST['indirizzo']))
						$where .= " clienti.indirizzo LIKE '%".$_REQUEST['indirizzo']."%' AND ";
					if(!empty($_REQUEST['cellulare']))
						$where .= " clienti.cellulare LIKE '%".$_REQUEST['cellulare']."%' AND ";
	
					if(!empty($where))
						$where = " AND ".substr($where, 0, -4);
					$result = $Bean->dbSearch($this->conn, $where, new referers(), new referers_clienti());

					$_SESSION['AjaxPortaVisione']['result'] = $result;
				}
				$headerField[] = 'NOME';
				$headerField[] = 'COGNOME';
				$headerField[] = 'PARTITA IVA';
				$headerField[] = 'CODICE FISCALE';
				$headerField[] = 'OPZIONI';
				
				$excludedField[] = 'id';				
				$excludedField[] = 'indirizzo';
				$excludedField[] = 'indirizzo_spedizione';
				$excludedField[] = 'citta';
				$excludedField[] = 'cap';
				$excludedField[] = 'cellulare';
				$excludedField[] = 'fisso';
				$excludedField[] = 'email';
				$excludedField[] = 'ragione_sociale';
				$excludedField[] = 'percentuale_sconto';
				$excludedField[] = 'data_inserimento_riga';
				$excludedField[] = 'data_modifica_riga';
				$excludedField[] = 'is_active';
				$excludedField[] = 'operatore';
				
				$this->tEngine->assign('pagingAction', 'cerca_cliente');
			break;
		}

		$this->tEngine->assign('excludedField', $excludedField);
		$this->tEngine->assign('headerField', $headerField);
		$this->tEngine->assign('actionType', 'visione');

		if(!empty($_REQUEST['id_rappresentante']))
			$this->tEngine->assign('id_rappresentante', $_REQUEST['id_rappresentante']);
			
		$this->tEngine->assign('tbl_name', $tbl_name);

		$p = new MyPager($_SESSION['AjaxPortaVisione']['result'], $this->rowForPage);
		$links = $p->getLinks();
		
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);

		echo $this->tEngine->fetch('shared/DivCercaResult');
	}
}
?>