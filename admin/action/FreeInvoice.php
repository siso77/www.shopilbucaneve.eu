<?php
class FreeInvoice extends DBSmartyAction
{
	var $className;
	
	function FreeInvoice()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);

		if(!empty($_REQUEST['reset']))
		{
			unset($_SESSION[$this->className]);
			unset($_SESSION['invoice_'.$_SESSION['curr_invoice_num']]);
			$this->_redirect('?act=FreeInvoice');
		}
		
		if(!empty($_SESSION[$this->className]['elements']))
			$this->tEngine->assign('invoices_index', count($_SESSION[$this->className]['elements']));
		else
			$this->tEngine->assign('invoices_index', 0);
			
		include_once(APP_ROOT."/beans/index_fattura.php");
		$BeanIndexFattura = new index_fattura();
		$index_fattura = $BeanIndexFattura->dbGetAll($this->conn);
		$numero_fattura = $index_fattura[0]['id'];
		
		if(!empty($_REQUEST['invoice_num']) && $_REQUEST['invoice_num'] != $numero_fattura)
			$this->tEngine->assign('invoice_num', $_REQUEST['invoice_num']);
		else
			$this->tEngine->assign('invoice_num', $numero_fattura);
		
		if(!empty($_REQUEST['invoice_date']) && $_REQUEST['invoice_date'] != $numero_fattura)
			$this->tEngine->assign('invoice_date', $_REQUEST['invoice_date']);
			
		if(!empty($_REQUEST['tipo_pagamento']))
			$this->tEngine->assign('tipo_pagamento', $_REQUEST['tipo_pagamento']);
			
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(empty($_REQUEST['save']))
			{
				$_SESSION[$this->className]['rif_scontrino'] 				= $_REQUEST['rif_scontrino'];
				$_SESSION[$this->className]['data_rif_scontrino'] 			= $_REQUEST['data_rif_scontrino'];
				$_SESSION[$this->className]['mezzo'] 						= $_REQUEST['mezzo'];
				$_SESSION[$this->className]['customer_data']['nome'] 		= $_REQUEST['nome'];
				$_SESSION[$this->className]['customer_data']['cognome'] 	= $_REQUEST['cognome'];
				$_SESSION[$this->className]['customer_data']['citta'] 		= $_REQUEST['citta'];
				$_SESSION[$this->className]['customer_data']['indirizzo']	= $_REQUEST['indirizzo'];
				$_SESSION[$this->className]['customer_data']['cap'] 		= $_REQUEST['cap'];
				$_SESSION[$this->className]['customer_data']['provincia'] 	= $_REQUEST['provincia'];
				$_SESSION[$this->className]['customer_data']['fisso']	 	= $_REQUEST['fisso'];
				$_SESSION[$this->className]['customer_data']['fisso']	 	= $_REQUEST['cellulare'];
				$_SESSION[$this->className]['customer_data']['cellulare']	 	= $_REQUEST['email'];
				$_SESSION[$this->className]['customer_data']['codice_fiscale']	= $_REQUEST['codice_fiscale'];
				$_SESSION[$this->className]['customer_data']['p_iva']			= $_REQUEST['p_iva'];
				
				$_SESSION[$this->className]['customer_data']['spedizione']['nome'] 		= $_REQUEST['nome_spedizione'];
				$_SESSION[$this->className]['customer_data']['spedizione']['cognome'] 	= $_REQUEST['cognome_spedizione'];
				$_SESSION[$this->className]['customer_data']['spedizione']['citta'] 	= $_REQUEST['citta_spedizione'];
				$_SESSION[$this->className]['customer_data']['spedizione']['indirizzo']	= $_REQUEST['indirizzo_spedizione'];
				$_SESSION[$this->className]['customer_data']['spedizione']['cap'] 		= $_REQUEST['cap_spedizione'];
				$_SESSION[$this->className]['customer_data']['spedizione']['provincia'] = $_REQUEST['provincia_spedizione'];
				$_SESSION[$this->className]['customer_data']['spedizione']['fisso']	 	= $_REQUEST['fisso_spedizione'];
				$_SESSION[$this->className]['customer_data']['spedizione']['cf_p_iva']	= $_REQUEST['cf_p_iva_spedizione'];
				
				for($i = 0; $i <= $this->tEngine->assignment['invoices_index']; $i++)
				{
					$_SESSION[$this->className]['elements'][$i]['bar_code'] 		= $_REQUEST['bar_code_'.$i];
					$_SESSION[$this->className]['elements'][$i]['name_it'] 			= $_REQUEST['name_it_'.$i];
					$_SESSION[$this->className]['elements'][$i]['description_it'] 	= $_REQUEST['description_it_'.$i];
					$_SESSION[$this->className]['elements'][$i]['price_it'] 		= $_REQUEST['price_it_'.$i];
					$_SESSION[$this->className]['elements'][$i]['quantita'] 		= $_REQUEST['quantita_'.$i];
					$_SESSION[$this->className]['elements'][$i]['iva'] 				= $_REQUEST['iva_'.$i];
	
					$totale_prodotto = str_replace(',','.', $_REQUEST['price_it_'.$i])*$_REQUEST['quantita_'.$i];
					$_SESSION[$this->className]['elements'][$i]['totale'] = $totale_prodotto;
					
					$totale_fattura = $totale_fattura + $totale_prodotto;
				}
				$imponibile = round($imponibile + ($totale_fattura / FATTURA_TAX_IVA), 2);
				//$iva = $total-$imponibile;
				$_SESSION[$this->className]['imponibile'] = $imponibile;
				$_SESSION[$this->className]['totale_fattura'] = $totale_fattura;
				$this->tEngine->assign('invoices_index', count($_SESSION[$this->className]['elements']));
			}				
			else
			{
				include_once(APP_ROOT."/beans/customer.php");
				$BeanCustomer = new customer();
				$search = " AND nome = '".$_REQUEST['nome']."'";
				$search .= " AND cognome = '".$_REQUEST['cognome']."'";
				$CustomerFound = $BeanCustomer->dbSearch($this->conn, $search.' ORDER BY nome DESC');
				if(empty($CustomerFound))
				{
					$BeanCustomer = new customer($this->conn, $_REQUEST);
					$BeanCustomer->setOperatore($_SESSION['LoggedUser']['username']);
					$idCustomer = $BeanCustomer->dbStore($this->conn);
				}
				
				if(!empty($_REQUEST['invoice_num']) && $_REQUEST['invoice_num'] == $index_fattura[0]['id'])
				{
					$BeanIndexFattura->fast_edit($this->conn, $index_fattura[0]['id']);
					$this->createInvoice($numero_fattura, $_REQUEST['invoice_date'], $_SESSION[$this->className]['rif_scontrino'], $_SESSION[$this->className]['data_rif_scontrino']);
				}
				else 
				{
					$this->createInvoice($_REQUEST['invoice_num'], $_REQUEST['invoice_date'], $_SESSION[$this->className]['rif_scontrino'], $_SESSION[$this->className]['data_rif_scontrino']);
				}
			}
		}
		
		$this->tEngine->assign('invoices_data', $_SESSION[$this->className]);
		$this->tEngine->assign('tpl_action', 'FreeInvoice');
		$this->tEngine->display('Index');
	}

	function createInvoice($invoiceNum, $invoiceDate, $rif_scontrino, $data_rif_scontrino)
	{
		$pathDoc = APP_ROOT.'/fatture/free_invoice/';
		$wwwPathDoc = WWW_ROOT.'fatture/free_invoice/';
		if(!is_dir(APP_ROOT.'/fatture/free_invoice/'))
			mkdir(APP_ROOT.'/fatture/free_invoice/', 0755, true);

		$param['invoiceNum'] 	  = $invoiceNum;
		$param['invoiceDate'] 	  = $invoiceDate;
		$param['customer'] 		  = $_SESSION[$this->className]['customer_data'];
//$param['data'] 			  = $_SESSION[$this->className]['elements'];
//$param['sale'] 			  = $_SESSION[$this->className]['elements'];
		$param['mezzo'] 		  = $_SESSION[$this->className]['mezzo'];
		$param['rif_scontrino']   = $rif_scontrino;
//$param['bean_vendite'] 	  = $_SESSION[$this->className]['elements'];
		$param['imponibile'] 	  = $_SESSION[$this->className]['imponibile'];
		$param['totale_fattura']  = $_SESSION[$this->className]['totale_fattura'];
		$param['FATTURA_TAX_IVA'] = FATTURA_TAX_IVA;
		$param['IVA_INTEGRATORI'] = IVA_INTEGRATORI;
		$param['IVA'] 			  = IVA;
		$param['WWW_ROOT'] 		  = WWW_ROOT;
		$param['tipo_pagamento'] 		  = $_REQUEST['tipo_pagamento'];
		$param['data_rif_scontrino'] 	  = $data_rif_scontrino;
		
//$_SESSION['invoice_'.$invoiceNum] = $param;
		$_SESSION['curr_invoice_num'] 	  = $invoiceNum;
		
		if(is_file($pathDoc.$fName.'.doc'))
			unlink($pathDoc.$fName.'.doc');
			
		$data = $_SESSION[$this->className]['elements'];
		$index = round((count($data) / 10));
		if($index == 0)
			$index = 1;

		for($a = 0; $a < $index; $a++)
		{
			$param['data'] = null;
			$param['sale'] = null;
			$param['bean_vendite'] = null;
			$_SESSION['invoice_'.$invoiceNum] = null;
			for($j=0; $j <= 10;$j++)
			{
				$tmp = $data[$j];
				if(!empty($data[$j]))
				{
					$param['data'][] = $tmp;
					$param['sale'][] = $tmp;
					$param['bean_vendite'][] = $tmp;
					unset($data[$j]);
				}
			}
			$data = array_values($data);
			$_SESSION['invoice_'.$invoiceNum] = $param;

			ob_start();
				include(APP_ROOT.'/libs/TemplateClass/Template_Free_Invoice_DOC.php');
				$msWord .= ob_get_contents();
			ob_end_clean();
		}
		
		$fName = $invoiceNum.'_';
		$fName .= str_replace('', '', $_SESSION[$this->className]['customer_data']['nome']).'_';
		$fName .= str_replace('', '', $_SESSION[$this->className]['customer_data']['cognome'].'_');

		$fName .= str_replace('/', '-', $invoiceDate);
		$fp = fopen($pathDoc.$fName.'.doc', 'w+');
		fwrite($fp, $msWord);
		fclose($fp);

//exit();
		unset($_SESSION[$this->className]);
		unset($_SESSION['invoice_'.$invoice_num]);

		echo '<script>
				window.open("'.$wwwPathDoc.$fName.'.doc");
				window.location.href = "'.WWW_ROOT.'?act=FreeInvoice";
			</script>';
		exit();
	}
}
?>