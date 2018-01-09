<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/casa_editrice.php");
include_once(APP_ROOT."/beans/autori.php");
include_once(APP_ROOT."/beans/contenuti.php");
include_once(APP_ROOT."/beans/distributore.php");
include_once(APP_ROOT."/beans/tipo_presa_carico.php");
include_once(APP_ROOT."/beans/vendite.php");
include_once(APP_ROOT."/beans/clienti.php");
include_once(APP_ROOT."/beans/index_fattura.php");

class NuovaVendita extends DBSmartyAction
{
	function NuovaVendita()
	{
		parent::DBSmartyAction();
		
		$BeanIndexFattura = new index_fattura();
		$index_fattura = $BeanIndexFattura->dbGetAll($this->conn);
		$this->tEngine->assign('index_fattura', $index_fattura['id']);

		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$data = $this->validatePostData();
			if(!$data)
				exit('Errore');
				
			$idsMagazzino = $data['id_magazino'];
			unset($data['id_magazino']);
			unset($data['id']);
			
			$data['id_rif_new_age'] = 1;
			
			foreach($idsMagazzino as $id)
			{
				$data['id_magazino'] = $id;
				$data['quantita'] = $_REQUEST['quantita_'.$id];

				if(!empty($_REQUEST['fattura']) && $_REQUEST['fattura'] == $index_fattura['id'] && !empty($_REQUEST['btn_vendi_stampa']))
					$BeanIndexFattura->fast_edit($this->conn);
				else
					unset($data['fattura']);

				$BeanVendite = new vendite($this->conn, $data);
				$BeanVendite->setData_vendita(date('Y-m-d'));
				$BeanVendite->setOperatore($_SESSION['LoggedUser']['username']);
				$BeanVendite->setId_rif_new_age($id_rif_new_age);
				$idVendita[] = $BeanVendite->dbStore($this->conn);
				
				if($_REQUEST['vendita_from'] == 'cliente')
				{
					$BeanMagazzino = new magazzino($this->conn, $data['id_magazino']);
					$quantita = $BeanMagazzino->getQuantita();
					$BeanMagazzino->setQuantita(($quantita - $data['quantita']));
					$BeanMagazzino->dbStore($this->conn);
				}
				elseif($_REQUEST['vendita_from'] == 'visione' || $_REQUEST['vendita_from'] == 'visione_cliente')
				{
					$BeanVisione = new in_visione($this->conn, $_REQUEST['id_visione']);
					$quantita = $BeanVisione->getQuantita();
					$BeanVisione->setQuantita(($quantita - $data['quantita']));
					if($BeanVisione->getQuantita() <= 0)
						$BeanVisione->setIs_active(0);
						$BeanVisione->dbStore($this->conn);
				}
			}
			unset($_SESSION['book_in_basket']);

			if(!empty($_REQUEST['btn_vendi_stampa']))
			{
				foreach($idsMagazzino as $id)
					$idsMag .= '&id_magazzino[]='.$id;
			
				foreach($idVendita as $id)
					$ids .= '&id_vendita[]='.$id;
					
				$this->_redirect('?act=Fatturazione&id_cliente='.$data['id_cliente'].$ids.$idsMag);
			
			}

			$this->_redirect('?act=ViewCliente&id='.$data['id_cliente']);		
		}
		if(!empty($_REQUEST['qty']))
			$this->tEngine->assign('qty', $_REQUEST['qty']);
		
		if(!empty($_REQUEST['id_cliente']))
		{
			$BeanMagazzino = new magazzino();
			if(empty($_REQUEST['id_magazzino']) && !empty($_SESSION['book_in_basket']))
				$BookInBasket = $BeanMagazzino->dbSearch($this->conn, ' AND magazzino.id IN('.implode(',', $_SESSION['book_in_basket']).') ');
			elseif(!empty($_REQUEST['id_magazzino']))
				$BookInBasket = $BeanMagazzino->dbSearch($this->conn, " AND magazzino.id = '".$_REQUEST['id_magazzino']."' ");
			$this->tEngine->assign('book_in_basket', $BookInBasket);
			
			$BeanClienti = new clienti($this->conn, $_REQUEST['id_cliente']);
			$cliente = $BeanClienti->vars();
			
			include_once(APP_ROOT."/beans/emails.php");
			include_once(APP_ROOT."/beans/emails_clienti.php");

			$BeanClientiEmail = new emails_clienti();
			$idsEmail = $BeanClientiEmail->dbGetAllIdEmailByIdCliente($this->conn, $cliente['id']);
			$BeanEmails = new emails();
			$emails = $BeanEmails->dbGetAllByIdsEmail($this->conn, $idsEmail);
			
			$this->tEngine->assign('emails', $emails);
			$this->tEngine->assign('cliente', $cliente);
			
			if(!empty($_REQUEST['vendita_from']))
				$this->tEngine->assign('vendita_from', $_REQUEST['vendita_from']);
			if(!empty($_REQUEST['id_visione']))
				$this->tEngine->assign('id_visione', $_REQUEST['id_visione']);
		}
		
		$this->tEngine->assign('vendi', true);
		$this->tEngine->assign('header_message', 'Stai vendendo un libro');
		$this->tEngine->assign('sub_header_message', 'Inserisci i campi necessari per fare la vendita');

		$this->tEngine->assign('tpl_action', 'VenditaContenuto');
		$this->tEngine->display('Index');		
	}
	
	function validatePostData()
	{
		if(
			!empty($_REQUEST['rappresentante_nome']) && 
			!empty($_REQUEST['rappresentante_cognome']) &&
			!empty($_REQUEST['rappresentante_indirizzo']) &&
			!empty($_REQUEST['rappresentante_citta']) &&
			!empty($_REQUEST['rappresentante_cap']) &&
			!empty($_REQUEST['rappresentante_cellulare']) &&
			!empty($_REQUEST['rappresentante_fisso']) &&
			!empty($_REQUEST['rappresentante_email']) &&
			!empty($_REQUEST['rappresentante_percentuale_provvigioni']) 
			)
		{
				$data = array('nome' => $_REQUEST['rappresentante_nome'], 
							'cognome' => $_REQUEST['rappresentante_cognome'],
							'indirizzo' => $_REQUEST['rappresentante_indirizzo'],
							'citta' => $_REQUEST['rappresentante_citta'],
							'cap' => $_REQUEST['rappresentante_cap'],
							'cellulare' => $_REQUEST['rappresentante_cellulare'],
							'fisso' => $_REQUEST['rappresentante_fisso'],
							'email' => $_REQUEST['rappresentante_email'],
							'percentuale_provvigioni' => $_REQUEST['rappresentante_percentuale_provvigioni'],
							'operatore'=> $_SESSION['LoggedUser']['username']);
				
				include_once(APP_ROOT."/beans/rappresentante.php");
				$BeanRappresentante = new rappresentante($this->conn, $data);
				
				$search = " AND nome = '".$_REQUEST['rappresentante_nome']."' AND cognome = '".$_REQUEST['rappresentante_cognome']."' AND email = '".$_REQUEST['rappresentante_email']."'";
				$RappresentanteFound = $BeanRappresentante->dbSearch($this->conn, $search);

				if(empty($RappresentanteFound))
					$return['id_rappresentante'] = $BeanRappresentante->dbStore($this->conn);
				else
					$return['id_rappresentante'] = $RappresentanteFound[0]['id'];
		}
		else 
			$return['id_rappresentante'] = $_REQUEST['id_rappresentante'];

		if(!empty($_REQUEST['fattura']))
			$return['fattura'] = $_REQUEST['fattura'];
		//else
			//return false;

		if(!empty($_REQUEST['id_magazzino']))
			$return['id_magazino'] = $_REQUEST['id_magazzino'];
		else
			return false;
		
		if(!empty($_REQUEST['ddv']))
			$return['ddv'] = $_REQUEST['ddv'];
			
		if(!empty($_REQUEST['vendita_iva']))
			$return['is_iva'] = 1;
		else
			$return['is_iva'] = 0;
						
		if(!empty($_REQUEST['id_cliente']))
			$return['id_cliente'] = $_REQUEST['id_cliente'];
		else
			return false;

		if(!empty($_REQUEST['free_text']))
			$return['free_text'] = $_REQUEST['free_text'];
			
		if(!empty($_REQUEST['percentuale_sconto']))
			$return['percentuale_sconto'] = $_REQUEST['percentuale_sconto'];
			
		if(!empty($_REQUEST['tipo_pagamento']))
			$return['tipo_pagamento'] = $_REQUEST['tipo_pagamento'];
		else
			return false;

		return $return;	
	}
}
?>