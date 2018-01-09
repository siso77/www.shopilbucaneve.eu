<?php
include_once(APP_ROOT."/beans/vendite.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/contenuti.php");

class Fatturazione extends DBSmartyAction
{
	var $invoice_date;
	
	function Fatturazione()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['search']) || isset($_REQUEST['order_by']) || isset($_REQUEST['rowForPage']) || isset($_REQUEST['pageID']))
		{
			$this->CercaCliente();
		}

		if(!empty($_REQUEST['id_magazzino']))
		{
			foreach ($_REQUEST['id_magazzino'] as $id)
			{
				$BeanMagazzino = new magazzino($this->conn, $id);
				$data = $BeanMagazzino->vars();
				$idsContenuti[] = $data['id_contenuto'];
			}
			$_SESSION['book_in_basket_fattura']['id_contenuto'] = $idsContenuti;
		}
		if(!empty($_REQUEST['id_contenuto']) && !is_array($_REQUEST['id_contenuto']))
			$_SESSION['book_in_basket_fattura']['id_contenuto'] = array($_REQUEST['id_contenuto']);
		if(!empty($_REQUEST['id_vendita']))
		{
			if(!is_array($_REQUEST['id_vendita']))
				$_SESSION['book_in_basket_fattura']['id_vendita'] = array($_REQUEST['id_vendita']);
			else
				$_SESSION['book_in_basket_fattura']['id_vendita'] = $_REQUEST['id_vendita'];
		}
		if(!empty($_REQUEST['id_cliente']))
			$_SESSION['book_in_basket_fattura']['id_cliente'] = $_REQUEST['id_cliente'];

		if(!empty($_REQUEST['id_cliente']))
		{
			include_once(APP_ROOT."/beans/clienti.php");
			$BeanClienti = new clienti($this->conn, $_SESSION['book_in_basket_fattura']['id_cliente']);
			$cliente = $BeanClienti->vars();

			foreach ($result as $k => $val)
			{
				if($cliente['id'] != $val['id_cliente'])
				{
					echo '
					<script>
					alert("Attenzione!!!\nStai generando la fattura di un libro che non venduto al cliente: '.$val['nome'].' '.$val['cognome'].'");
					document.location.href="'.WWW_ROOT.'?act=Fatturazione";
					</script>';
					exit();
				}
			}
			
			$Bean = new vendite();
			$isInvoiced = '';
			if(empty($_REQUEST['print']) && empty($_REQUEST['view_fattura']))
				$isInvoiced = 'AND is_invoiced IS NULL ';
			$result = $Bean->dbGetAllByIdCliente($this->conn, $_SESSION['book_in_basket_fattura']['id_cliente'].' AND id IN('.implode(', ', $_SESSION['book_in_basket_fattura']['id_vendita']).') '.$isInvoiced, new magazzino(), new contenuti());		
			$i = 0;
			$notEqualsDdv = false;
			foreach($result as $value)
			{
				if(in_array($value['contenuto']['id'], $_SESSION['book_in_basket_fattura']['id_contenuto']))
				{
					$BeanAutori = new autori($this->conn, $value['contenuto']['id_autore']);
					$dataAutore = $BeanAutori->vars();
					$BeanCasaEditrice = new casa_editrice($this->conn, $value['contenuto']['id_casa_editrice']);
					$dataEditrice = $BeanCasaEditrice->vars();
					
					$BeanApplicationSetup = new ApplicationSetup();
					$paymentType = $BeanApplicationSetup->dbGetAllByFieldAndName($this->conn, 'payment_type', $value['tipo_pagamento']);

					$struct[] = $value;
					if(empty($ddv))
						$ddv = $value['ddv'];
					if($ddv != $value['ddv'])
						$notEqualsDdv = true;
						
					if(!empty($value['free_text']))
						$free_text = $value['free_text'];
					
					$is_iva = $value['is_iva'];
						
					$assignData[$i]['casa_editrice'] 		= $dataEditrice['nome'];
					$assignData[$i]['autore'] 				= $dataAutore['nome'];
					$assignData[$i]['titolo'] 				= $value['contenuto']['titolo'];
					$assignData[$i]['prezzo'] 				= $value['contenuto']['prezzo'];
					$assignData[$i]['quantita'] 			= $value['quantita'];
					$assignData[$i]['tipo_pagamento'] 		= $paymentType[0];
					$assignData[$i]['percentuale_sconto'] 	= $value['percentuale_sconto'];
					$i++;
				}
			}
			$assignData['cliente'] = $cliente;

			if($notEqualsDdv)
			{
				echo '
					<script>
					alert("Attenzione!!!\nStai generando una o più la fatture con DDV differenti!");
					document.location.href="'.WWW_ROOT.'?act=ViewCliente&id='.$_SESSION['book_in_basket_fattura']['id_cliente'].'";
					</script>';
					unset($_SESSION['book_in_basket_fattura']);
					exit();
			}
			if(count($invoiced) > 0)
			{
				echo '
					<script>
					alert("Attenzione!!!\nStai generando una o più la fatture già emesse!");
					document.location.href="'.WWW_ROOT.'?act=ViewCliente&id='.$_SESSION['book_in_basket_fattura']['id_cliente'].'";
					</script>';
					unset($_SESSION['book_in_basket_fattura']);
					exit();
			}
			
			include_once(APP_ROOT."/beans/index_fattura.php");
			$BeanIndexFattura = new index_fattura();
			$index_fattura = null;
			if(!empty($struct[0]['fattura']))
				$this->tEngine->assign('index_fattura', $struct[0]['fattura']);
			else
			{
				$index_fattura = $BeanIndexFattura->dbGetAll($this->conn);
				$this->tEngine->assign('index_fattura', $index_fattura['id']);
			}
			
			$idsVendite 		= $_SESSION['book_in_basket_fattura']['id_vendita'];
			$result['cliente'] 	= $cliente;

			if(!empty($struct[0]['fattura']))
				$invoiceNum = $struct[0]['fattura'];
			else
				$invoiceNum = $index_fattura['id'];
				
			/** Calcolo prezzi in fattura **/
			$totale = 0;
			$imponibile = 0;
			foreach($assignData as $value)
			{
				$imponibile = $imponibile + ($value['prezzo'] * $value['quantita']);
			}
			
			$percentSale 	= $cliente['percentuale_sconto'];
			$sale 			= ( $imponibile * $percentSale / 100);
			$totale 		= $imponibile - $sale;
			/** Calcolo prezzi in fattura **/
			
			if(!empty($struct[0]['data_fatturazione']))
				$this->invoice_date = Currency::FormatDateFromMysql($struct[0]['data_fatturazione']);
			else
				$this->invoice_date = date('d/m/Y');
				
			$assignData['invoice_data']['total'] 		= $totale;
			$assignData['invoice_data']['percent_sale'] = $percentSale;
			$assignData['invoice_data']['imponibile'] 	= $imponibile;
			$assignData['invoice_data']['sale'] 		= $sale;
			$assignData['data_fatturazione']			= $this->invoice_date;
			$assignData['ddv']							= $ddv;
			$assignData['free_text']					= $free_text;
			$assignData['includeTextIva']				= $is_iva;
			
			$this->tEngine->assign('data', $assignData);
			
//$this->createPdf($invoiceNum, $cliente, $assignData, $imponibile, $percentSale, $sale, $totale, $ddv);		
//exit();
			
			if(!empty($_REQUEST['print']))
			{
				unset($_SESSION['book_in_basket_fattura']);
				$this->tEngine->assign('fattura', array('width'=>900, 'height'=>495));
				$this->tEngine->display('Fattura');
				exit();
			}
			else 
			{
				$this->tEngine->assign('fattura', array('width'=>750, 'height'=>800));
				$data 			= $this->tEngine->fetch('Fattura');
				$pathMsWord 	= APP_ROOT.'/fatture/'.$_SESSION['book_in_basket_fattura']['id_cliente'].'/';
				$fileNameMsWord = $invoiceNum.'.doc';
				
				$this->createMsWord($data,$pathMsWord, $fileNameMsWord,false);
				$this->createPdf($invoiceNum, $cliente, $assignData, $imponibile, $percentSale, $sale, $totale, $ddv);		

				if(!empty($index_fattura))
					$BeanIndexFattura->fast_edit($this->conn);
								
				foreach($struct as $value)
				{
					$Bean = new vendite($this->conn, $value['id']);

					if(!empty($index_fattura))
						$Bean->setFattura($index_fattura['id']);

					$Bean->setIs_invoiced(1);
					$Bean->setData_fatturazione(date('Y-m-d'));
					$Bean->dbStore($this->conn);
				}				
				
				if(empty($_REQUEST['view_fattura']))
					echo '<script>window.open("'.WWW_ROOT.'?act=Fatturazione&print=1&id_cliente='.$_SESSION['book_in_basket_fattura']['id_cliente'].'");</script>';
				else
					echo $data = $this->tEngine->fetch('Fattura');
				exit();
			}
		}
		else 
			$this->tEngine->assign('tpl_action', 'CercaClienteFattura');

		$this->tEngine->display('Index');
	}
	
	function createPdf($invoiceNum, $cliente, $assignData, $imponibile, $percentSale, $sale, $totale)
	{
		$pathPdf = APP_ROOT.'/fatture/'.$_SESSION['book_in_basket_fattura']['id_cliente'].'/'.$invoiceNum.'.pdf';
		
		$customer['nome'] 			 = $cliente['nome'].' '.$cliente['cognome'];
		$customer['address_company'] = $cliente['indirizzo'];
		$customer['address_invoice'] = $cliente['indirizzo_spedizione'];
		$customer['zip_code'] 		 = $cliente['cap'];
		$customer['city'] 			 = $cliente['citta'];
		$customer['data_fattura'] 	 = $this->invoice_date;
		$customer['ddv']		 	 = $assignData['ddv'];

		if(!empty($cliente['p_iva']))
			$customer['cf_piva'] = $cliente['p_iva'];
		elseif(!empty($cliente['codice_fiscale']))
			$customer['cf_piva'] = $cliente['codice_fiscale'];
			
		$data = $assignData;
		$includeTextIva = $data['includeTextIva'];
		unset($data['cliente']);
		unset($data['invoice_data']);
		unset($data['data_fatturazione']);
		unset($data['ddv']);
		unset($data['includeTextIva']);
		
		$this->createPdfInvoice($pathPdf, $includeTextIva, $invoiceNum, $customer, $data, $imponibile, $percentSale, $sale, $totale);		
	}
	
	function CercaCliente()
	{
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION['Fatturazione']['result'] = null;
				$_SESSION['Fatturazione']['key_search'] = $_REQUEST['key_search'];
				$where = " AND (clienti.nome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR clienti.cognome LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR clienti.indirizzo LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR clienti.cellulare LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR clienti.email LIKE '%".$_REQUEST['key_search']."%')";
			}
			else 
			{
				$_SESSION['Fatturazione']['key_search'] = null;
				$_SESSION['Fatturazione']['result'] = null;
				$_SESSION['Fatturazione']['order_by'] = null;
				$_SESSION['Fatturazione']['order_type'] = null;
			}
		}
		else
			$where = '';
			
		if(isset($_REQUEST['order_by']))
		{
			$_SESSION['Fatturazione']['order_by'] = $_REQUEST['order_by'];
			$_SESSION['Fatturazione']['order_type'] = $_REQUEST['order_type'];
			$_SESSION['Fatturazione']['result'] = null;
		}			

		if(!empty($_SESSION['Fatturazione']['order_by']))
			$where .= ' ORDER BY '.$_SESSION['Fatturazione']['order_by'].' '.$_SESSION['Fatturazione']['order_type'];

		if(empty($_SESSION['Fatturazione']['result']))
		{
			include_once(APP_ROOT."/beans/clienti.php");			
			include_once(APP_ROOT."/beans/emails.php");
			include_once(APP_ROOT."/beans/emails_clienti.php");
			$BeanClienti = new clienti();
			$_SESSION['Fatturazione']['result'] = $BeanClienti->dbSearch($this->conn, $where, new emails(), new emails_clienti());
		}

		$p = new MyPager($_SESSION['Fatturazione']['result'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION['Fatturazione']['key_search']);		
	}
}
?>